<?php
namespace App\Http\Repositories;

use App\Http\Services\FcmService as ServicesFcmService;
use App\Models\Complaint;
use App\Models\DeliveryRequest;
use App\Models\Pharma;
use App\Models\Pharmacist;
use App\Models\Role;
use App\Models\PharmaUser;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Services\FcmService;
use App\Models\CartOrder;
use Illuminate\Support\Facades\DB;

class PharmaRepository
{


    public function createPharma(array $data)
    {
        return Pharma::create($data);
    }

    public function createPharmacist(array $data)
    {
        return Pharmacist::create($data);
    }

    
public function accept($id, FcmService $fcmService)
{
    // نستخدم معاملة لضمان التناسق
    $pharmacist = DB::transaction(function () use ($id) {
        $pharmacist = Pharmacist::findOrFail($id);

        // إذا كان مقبول مسبقًا لا مشكلة، سنثبت الدور ونكمل
        $pharmacist->update(['accept' => 1]);

        // أنشئ/ثبّت دور pharmacist بدون تكرار
        Role::firstOrCreate([
            'user_id' => $pharmacist->user_id,
            'name'    => 'pharmacist',
        ]);

        // احذف دور Consumer إن وُجد (حساس لحالة الأحرف حسب بياناتك)
        Role::where('user_id', $pharmacist->user_id)
            ->whereIn('name', ['Consumer', 'consumer'])
            ->delete();

        return $pharmacist;
    });

    // تحميل المستخدم لجلب الـ device_token
    $pharmacist->load('user');
    $user = $pharmacist->user;

    $notified = false;
    $notify_info = null;

    if ($user && !empty($user->device_token)) {
        $title = 'تم قبولك كصيدلاني';
        $body  = 'تهانينا! تم قبول طلبك وأصبحت تعمل كصيدلاني داخل التطبيق. يمكنك الآن تسجيل الدخول واستخدام صلاحياتك.';

      
        $data = [
            'type'        => 'pharmacist_acceptance',
            'user_id'     => $user->id,
            'pharmacist_id' => $pharmacist->id,
            'accepted'    => 1,
        ];

        $resp = $fcmService->sendNotification($user->device_token, $title, $body, $data);

      
        $notified    = ($resp['ok'] ?? false) === true;
        $notify_info = $notified ? 'User notified via FCM.' : ($resp['error'] ?? 'FCM send failed.');
    } else {
        $notify_info = 'No device_token for user.';
    }

    return response()->json([
        'message'      => 'Pharmacist accepted and role updated.',
        'pharmacist_id'=> $pharmacist->id,
        'user_id'      => $pharmacist->user_id,
        'notified'     => $notified,
        'notify_info'  => $notify_info,
    ], 200);
}
    /*
    public function accept($id)
    {
        // Find the pharmacist by ID
        $pharmacist = Pharmacist::findOrFail($id);

        // Update the accept field to 1
        $pharmacist->update(['accept' => 1]);

        // Create the pharmacist role
        $role = Role::create([
            'user_id' => $pharmacist->user_id,
            'name' => 'pharmacist'
        ]);
        $userid= $pharmacist->user_id;
       
          Role::where('user_id', $userid)->where('name','Consumer')->delete();
            // Delete the pharmacist record

        return $pharmacist;
    }*/
/*
    public function deletePharmacist($id)
    {
        try {
            // Find the pharmacist by ID
            $pharmacist = Pharmacist::findOrFail($id);
    
            // Delete the associated pharma record
            Pharma::where('id', $pharmacist->pharma_id)->delete();
            $userid= $pharmacist->user_id;
            // Delete the pharmacist record
            $pharmacist->delete();
    
            Role::where('user_id', $userid)->where('name','Pharmacist')->delete();
            return true;
        } catch (\Exception $e) {
            throw new \Exception("Error deleting pharmacist and pharma: " . $e->getMessage());
        }
    }
*/
  
    public function deletePharmacist(int $id, FcmService $fcmService)
    {
      
        $pharmacist = Pharmacist::with('user')->findOrFail($id);
        $user       = $pharmacist->user ?? User::find($pharmacist->user_id);
        $userId     = $pharmacist->user_id;
        $pharmaId   = $pharmacist->pharma_id;
        $deviceToken = $user->device_token ?? null;

        DB::transaction(function () use ($pharmacist, $pharmaId, $userId) {
            if (!empty($pharmaId)) {
                Pharma::where('id', $pharmaId)->delete();
            }

            Role::where('user_id', $userId)
                ->whereIn('name', ['Pharmacist', 'pharmacist'])
                ->delete();

        
            $pharmacist->delete();
        });

        // أرسل إشعار الرفض بعد نجاح الحذف
        $notified = false;
        $notifyInfo = 'No device_token for user.';
        if (!empty($deviceToken)) {
            $title = 'تم رفضك كصيدلاني';
            $body  = 'نعتذر، تم رفض طلبك للعمل كصيدلاني في التطبيق.';

            $data = [
                'type'           => 'pharmacist_rejection',
                'user_id'        => $userId,
                'pharmacist_id'  => $id,
                'pharma_id'      => $pharmaId,
                'accepted'       => 0,
            ];

            $resp = $fcmService->sendNotification($deviceToken, $title, $body, $data);

            // واجهة FcmService الموحّدة: ['ok' => bool, 'id' => ?string, 'error' => ?string]
            $notified   = ($resp['ok'] ?? false) === true;
            $notifyInfo = $notified ? 'User notified via FCM.' : ($resp['error'] ?? 'FCM send failed.');
        }

        // رجّع بيانات فقط — خليه الكونترولر يبني JSON
        return [
            'deleted'      => true,
            'user_id'      => $userId,
            'pharmacist_id'=> $id,
            'pharma_id'    => $pharmaId,
            'notified'     => $notified,
            'notify_info'  => $notifyInfo,
        ];
    }
    
      public function getPendingPharmacists()
    {
        return Pharmacist::with(['user', 'pharma'])
            ->whereNull('accept')
            ->get();
    }

      
      public function getPharmacists()
    {
        return Pharmacist::with(['user', 'pharma'])
            ->where('accept',1)
            ->get();
    }


public function getAvailablePrivateOrders()
{
    $user = Auth::user();

    $pharmacist = Pharmacist::where('user_id', $user->id)->first();

    if (!$pharmacist) {
        throw new \Exception('Pharmacist not found for this user.');
    }

    $pharmaId = $pharmacist->pharma_id;

    $pharmaUsers = PharmaUser::with(['order' => function ($query) {
        $query->select(
            'id',
            'user_id',
            'name_medicine',
            'photo',
            'length',
            'width',
            'type',
            'time',
            'created_at',
            'updated_at'
        );
    }])
    ->where('type', 1)
    ->whereNull('accept_pharma')
    ->where('pharma_id', $pharmaId)
    ->get();

    // Add photo_path inside order relation
    $pharmaUsers->each(function ($pharmaUser) {
        if ($pharmaUser->order) {
            $photo = $pharmaUser->order->photo;

            if ($photo) {
                if (filter_var($photo, FILTER_VALIDATE_URL)) {
                    $parsedUrl = parse_url($photo);
                    $relativePath = ltrim($parsedUrl['path'], '/');
                } else {
                    $relativePath = $photo;
                }

                $pharmaUser->order->photo_path = $relativePath;
            } else {
                $pharmaUser->order->photo_path = null;
            }
        }
    });

    return $pharmaUsers;
}


public function getAvailablePublicOrders()
{
    $user = Auth::user();

    $pharmacist = Pharmacist::where('user_id', $user->id)->first();

    if (!$pharmacist) {
        throw new \Exception('Pharmacist not found for this user.');
    }

    $pharmaId = $pharmacist->pharma_id;

    $pharmaUsers = PharmaUser::with(['order' => function ($query) {
        $query->select(
            'id',
            'user_id',
            'name_medicine',
            'photo',
            'length',
            'width',
            'type',
            'time',
            'created_at',
            'updated_at'
        );
    }])
    ->where('type', 0)
    ->whereNull('accept_pharma')
    ->where('pharma_id', null)
    ->get();

    // Add photo_path inside order relation
    $pharmaUsers->each(function ($pharmaUser) {
        if ($pharmaUser->order) {
            $photo = $pharmaUser->order->photo;

            if ($photo) {
                if (filter_var($photo, FILTER_VALIDATE_URL)) {
                    $parsedUrl = parse_url($photo);
                    $relativePath = ltrim($parsedUrl['path'], '/');
                } else {
                    $relativePath = $photo;
                }

                $pharmaUser->order->photo_path = $relativePath;
            } else {
                $pharmaUser->order->photo_path = null;
            }
        }
    });

    return $pharmaUsers;
}
public function acceptOrder(array $data)
{


    // Find the PharmaUser record
     $pharmaUser = PharmaUser::where('order_id', $data['order_id'])->first();


    if (!$pharmaUser) {
        return response()->json(['message' => 'PharmaUser not found'], 404);
    }
    
    $user = Auth::user();

    // Get the pharmacist based on user ID
    $pharmacist = Pharmacist::where('user_id', $user->id)->first();

    if (!$pharmacist) {
        throw new \Exception('Pharmacist not found for this user.');
    }

    $pharmaId = $pharmacist->pharma_id;


    // Update accept_pharma = 1
    $pharmaUser->update([
        'accept_pharma' => 1,
        'pharma_id'=>$pharmaId


]);
// Create a DeliveryRequest with null qr, price, delivery_id
    DeliveryRequest::create([
        'qr' => null,
        'price' => $data['price'],
        'delivery_id' => null,
        'pharma_user_id' => $pharmaUser->id,
    ]);
 

    return response()->json(['message' => 'Order accepted and delivery request created'], 200);
}

/*
public function refuseOrder(array $data)
{
    $pharmaUser = PharmaUser::where('order_id', $data['order_id'])->first();

    if (!$pharmaUser) {
        return response()->json(['message' => 'PharmaUser not found'], 404);
    }

       $user = Auth::user();

    // Get the pharmacist based on user ID
    $pharmacist = Pharmacist::where('user_id', $user->id)->first();

    if (!$pharmacist) {
        throw new \Exception('Pharmacist not found for this user.');
    }

    $pharmaId = $pharmacist->pharma_id;



    // Update accept_pharma and reason
    $pharmaUser->update([
        'accept_pharma' => 0,
        'reason' => $data['reason'],
          'pharma_id'=>$pharmaId

    ]);

    // Create delivery request with null fields
    DeliveryRequest::create([
        'qr' => null,
        'price' => null,
        'delivery_id' => null,
        'pharma_user_id' => $pharmaUser->id,
    ]);

    return response()->json(['message' => 'Order refused and delivery request created'], 200);
}


*/

/*
public function refuseOrder(array $data)
{
    $pharmaUser = PharmaUser::where('order_id', $data['order_id'])->first();

    if (!$pharmaUser) {
        return response()->json(['message' => 'PharmaUser not found'], 404);
    }

    $user = Auth::user();

    $pharmacist = Pharmacist::where('user_id', $user->id)->first();

    if (!$pharmacist) {
        throw new \Exception('Pharmacist not found for this user.');
    }

    $pharmaId = $pharmacist->pharma_id;

    // Update accept_pharma and reason
    $pharmaUser->update([
        'accept_pharma' => 0,
        'reason' => $data['reason'],
        'pharma_id' => $pharmaId,
    ]);

    // Create delivery request
    DeliveryRequest::create([
        'qr' => null,
        'price' => null,
        'delivery_id' => null,
        'pharma_user_id' => $pharmaUser->id,
    ]);

$user = $pharmaUser->user;

if ($user->device_token) {
    app(\App\Services\FcmService::class)
        ->sendNotification(
            $user->device_token,
            'Order Refused',
            'Your order has been refused by the pharmacy. Reason: '.$data['reason']
        );
}

    return response()->json(['message' => 'Order refused and delivery request created'], 200);
}
*/

public function refuseOrder(array $data, FcmService $fcmService)
{
    $pharmaUser = PharmaUser::where('order_id', $data['order_id'])->first();

    if (!$pharmaUser) {
        return response()->json(['message' => 'PharmaUser not found'], 404);
    }

    $user = Auth::user();

    $pharmacist = Pharmacist::where('user_id', $user->id)->first();

    if (!$pharmacist) {
        return response()->json(['message' => 'Pharmacist not found for this user.'], 404);
    }

    $pharmaId = $pharmacist->pharma_id;

    // Update pharmaUser
    $pharmaUser->update([
        'accept_pharma' => 0,
        'reason' => $data['reason'],
        'pharma_id' => $pharmaId,
    ]);

    // Create delivery request
    DeliveryRequest::create([
        'qr' => null,
        'price' => null,
        'delivery_id' => null,
        'pharma_user_id' => $pharmaUser->id,
    ]);

    // Send notification to user
    $orderUser = $pharmaUser->user;

if ($orderUser && $orderUser->device_token) {
    $response = $fcmService->sendNotification(
        $orderUser->device_token,
        'Order Refused',
        'Your order has been refused by the pharmacy. Reason: ' . $data['reason']
    );}

  return response()->json([
    'message' => 'Order refused, delivery request created and user notified.',
    'user' => $orderUser,  // optionally include user info
], 200);
}


public function handleAccept($userId)
{
    $user = User::find($userId);

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }


    // Optional: set accept_point in Pharmacist model if needed
    $pharmacist = Pharmacist::where('user_id', $userId)->first();    
    if ($pharmacist) {
        $pharmacist->update(['accept_point' => 1]);
    }

    return response()->json(['message' => 'Accepted done']);
}


public function handleRefuse($userId)
{
   $user = User::find($userId);

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    // Optional: set accept_point in Pharmacist model if needed
    $pharmacist = Pharmacist::where('user_id', $userId)->first();
    if ($pharmacist) {
        $pharmacist->update(['accept_point' => 0]);
           return response()->json(['message' => 'refuse done']);
    }

 
}

public function searchByName($name)
{
    return Pharma::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($name) . '%'])->get();
}

 public function store(array $data)
    {
        return Complaint::create($data);
    }

    public function cartorderarchive2(){
            $user_id=Auth::id();
            $pharmacist=Pharmacist::where('user_id',$user_id)->first();
            $pharma_id=$pharmacist->pharma_id;

         $cart_order=CartOrder::where('pharma_id',$pharma_id)
                            ->where('done',1)
                           ->where('accepted',1)->where('verified',1)->with('applycartorder')
                           ->with('applycartorder.delivery')
                           ->with('cartorderitem.product')
                           ->with('cartorderitem.type')
                           ->get();
         return response()->json([
            'cart order archive' => $cart_order,
        ]);
    }
}
