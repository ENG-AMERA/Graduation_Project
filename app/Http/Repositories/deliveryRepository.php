<?php
namespace App\Http\Repositories;

use App\Http\Services\FcmService;
use App\Models\Delivery;
use App\Models\deliveryprice;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use SimpleSoftwareIO\QrCode\Facades\QrCode;
class deliveryRepository
{
    public function createdelivery(array $data)
    {
        return Delivery::create($data);
    }
   
   /*
    public function accept($id)
    {
        // Find the pharmacist by ID
        $delivery = Delivery::findOrFail($id);

        // Update the accept field to 1
        $delivery->update(['accept' => 1]);
  
        // Create the pharmacist role
        $role = Role::create([
            'user_id' => $delivery->user_id,
            'name' => 'delivery'
        ]);
          $userid= $delivery->user_id;
       
          Role::where('user_id', $userid)->where('name','Consumer')->delete();
            // Delete the delivery record

        return $delivery;
    }*/
        public function accept(int $id, FcmService $fcmService)
{
 
    $delivery = DB::transaction(function () use ($id) {
        $delivery = Delivery::findOrFail($id);

     
        $delivery->update(['accept' => 1]);

     
        Role::firstOrCreate([
            'user_id' => $delivery->user_id,
            'name'    => 'delivery',
        ]);


        Role::where('user_id', $delivery->user_id)
            ->whereIn('name', ['Consumer', 'consumer'])
            ->delete();

        return $delivery->fresh();
    });

    
    $user = method_exists($delivery, 'user')
        ? $delivery->user()->first()
        : User::find($delivery->user_id);

    $notified = false;
    $notify_info = 'No device_token for user.';

    if ($user && !empty($user->device_token)) {
        $title = 'تم قبولك كعامل توصيل';
        $body  = 'تهانينا! تم قبول طلبك وأصبحت تعمل كعامل توصيل داخل التطبيق.';

        $data = [
            'type'         => 'delivery_acceptance',
            'user_id'      => $user->id,
            'delivery_id'  => $delivery->id,
            'accepted'     => 1,
        ];

        $resp = $fcmService->sendNotification($user->device_token, $title, $body, $data);

    
        $notified    = ($resp['ok'] ?? false) === true;
        $notify_info = $notified ? 'User notified via FCM.' : ($resp['error'] ?? 'FCM send failed.');
    }


    return [
        'delivery'    => $delivery,
        'notified'    => $notified,
        'notify_info' => $notify_info,
    ];
}


public function deletdelivery(int $id, FcmService $fcmService)
{
    try {
       
        $delivery = Delivery::with('user')->findOrFail($id);
        $user       = $delivery->user;
        $userId     = $delivery->user_id;
        $deliveryId = $delivery->id;
        $deviceToken = $user?->device_token;

    
        DB::transaction(function () use ($delivery, $userId) {
        
            Role::where('user_id', $userId)
                ->whereIn('name', ['Delivery', 'delivery'])
                ->delete();

            $delivery->delete();
        });
        $notified = false;
        $notifyInfo = 'No device_token for user.';
        if (!empty($deviceToken)) {
            $title = 'تم رفضك كعامل توصيل';
            $body  = 'نعتذر، تم رفض طلبك للعمل كعامل توصيل في التطبيق.';

            $data = [
                'type'        => 'delivery_rejection',
                'user_id'     => $userId,
                'delivery_id' => $deliveryId,
                'accepted'    => 0,
            ];

            $resp = $fcmService->sendNotification($deviceToken, $title, $body, $data);

       
            $notified   = ($resp['ok'] ?? false) === true;
            $notifyInfo = $notified ? 'User notified via FCM.' : ($resp['error'] ?? 'FCM send failed.');
        }

        return [
            'deleted'     => true,
            'user_id'     => $userId,
            'delivery_id' => $deliveryId,
            'notified'    => $notified,
            'notify_info' => $notifyInfo,
        ];
    } catch (\Throwable $e) {
        throw new \Exception("Error deleting delivery: " . $e->getMessage());
    }
}/*
    public function deletdelivery($id)
    {
        try {
            // Find the pharmacist by ID
            $delivery = Delivery::findOrFail($id);
    
           $userid= $delivery->user_id;
       
                   $delivery->delete();
    
    
            Role::where('user_id', $userid)->where('name','Delivery')->delete();
            // Delete the pharmacist record
    
            return true;
        } catch (\Exception $e) {
            throw new \Exception("Error deleting delivery" . $e->getMessage());
        }
    }
    */

       public function getPendingdelivery()
    {
        return Delivery::with(['user'])
            ->whereNull('accept')
            ->get();
    }

/*
public function calculateDeliveryPrice($lat1, $lon1, $lat2, $lon2, $pricePerKm = 2)
{
    $earthRadius = 6371; // نصف قطر الأرض بالكيلومتر

    // تحويل الإحداثيات إلى راديان
    $lat1 = deg2rad($lat1);
    $lon1 = deg2rad($lon1);
    $lat2 = deg2rad($lat2);
    $lon2 = deg2rad($lon2);

    // حساب الفروق
    $deltaLat = $lat2 - $lat1;
    $deltaLon = $lon2 - $lon1;

    // معادلة هافرسين
    $a = sin($deltaLat / 2) ** 2 +
         cos($lat1) * cos($lat2) * sin($deltaLon / 2) ** 2;

    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    $distance = $earthRadius * $c;

    // حساب السعر
    $deliveryPrice = $distance * $pricePerKm;

    return round($deliveryPrice, 2);
}

*/
public function calculateDeliveryPrice($lat1, $lon1, $lat2, $lon2)
{
    $priceobj=deliveryprice::first();
     $pricePerKm= $priceobj->delivery_price;

    $earthRadius = 6371; // نصف قطر الأرض بالكيلومتر

    // تحويل الإحداثيات إلى راديان
    $lat1 = deg2rad($lat1);
    $lon1 = deg2rad($lon1);
    $lat2 = deg2rad($lat2);
    $lon2 = deg2rad($lon2);

    // حساب الفروق
    $deltaLat = $lat2 - $lat1;
    $deltaLon = $lon2 - $lon1;

    // معادلة هافرسين
    $a = sin($deltaLat / 2)  **2 +
         cos($lat1) * cos($lat2) * sin($deltaLon / 2) ** 2;

    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    $distance = $earthRadius * $c;

    // حساب السعر
    $deliveryPrice = $distance * $pricePerKm;

    return round($deliveryPrice, 2);
}
public function getPendingRequestsWithPharmaAndOrder()
{
    $date = Carbon::now('Asia/Damascus')->toDateString();
    $nextDay = Carbon::parse($date, 'Asia/Damascus')->addDay()->toDateString();

    $requests = DB::table('delivery_requests')
        ->join('pharma_users', 'delivery_requests.pharma_user_id', '=', 'pharma_users.id')
        ->join('orders', 'pharma_users.order_id', '=', 'orders.id')
        ->join('pharmas', 'pharma_users.pharma_id', '=', 'pharmas.id')
        ->join('users', 'pharma_users.user_id', '=', 'users.id')
        ->whereNull('delivery_requests.done')
        ->whereNull('delivery_requests.delivery_id')
        ->where('pharma_users.accept_user', '=', 1)
        ->where('pharma_users.accept_pharma', '=', 1)
        ->where(function($query) use ($date, $nextDay) {
            $query->whereNull('orders.time')
                  ->orWhere(function($q) use ($date, $nextDay) {
                      $q->where('orders.time', '>=', $date . ' 00:00:00')
                        ->where('orders.time', '<', $nextDay . ' 00:00:00');
                  });
        })
        ->select(
            'delivery_requests.id as request_id',
            'orders.id as order_id',
            'orders.length as order_length',
            'orders.width as order_width',
            'orders.type as order_type',
            'orders.time as order_time',
            'delivery_requests.price',
            'pharmas.name as pharma_name',
            'pharmas.length as pharma_length',
            'pharmas.width as pharma_width',
            'users.firstname',
            'users.lastname',
            'users.phone',
            'users.location'
        )
        ->get();

    // احسب وخزن السعر
    $requests->each(function ($request) {
        $calculatedPrice = $this->calculateDeliveryPrice(
            $request->pharma_length,
            $request->pharma_width,
            $request->order_length,
            $request->order_width
        );

        // خزن السعر في DB
        DB::table('delivery_requests')
            ->where('id', $request->request_id)
            ->update(['totalp
            
            rice' => $calculatedPrice]);

        $request->calculated_totalprice = $calculatedPrice;
    });

    return $requests;
}


public function getConsumerPendingRequests()
{
    return DB::table('delivery_requests')
        ->join('pharma_users', 'delivery_requests.pharma_user_id', '=', 'pharma_users.id')
        ->join('orders', 'pharma_users.order_id', '=', 'orders.id')
        ->join('pharmas', 'pharma_users.pharma_id', '=', 'pharmas.id')
        ->whereNull('delivery_requests.done')
        ->where('pharma_users.accept_user', 1)
        ->where('pharma_users.accept_pharma', 1)
        
        ->select(
            'orders.id as order_id',
            'orders.type as order_type',
            'delivery_requests.price',
           
            'pharmas.name as pharma_name'
        )
        ->get();
}


}
