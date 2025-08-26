<?php
namespace App\Http\Repositories;

use App\Models\Role;
use App\Models\User;
use App\Models\Order;
use App\Models\Pharma;
use App\Models\CartOrder;
use App\Models\Complaint;
use App\Models\ApplyCartOrder;
use App\Models\deliveryprice;
use App\Models\DeliveryRequest;

class AdminRepository{

    public function getallusers(){

    $users = User::with('roles')->get();

    return response()->json([
        'users' => $users,
    ]);

    }

    public function getdelivaries(){
        $role=Role::where('name','Delivery')->with('user')->
        with('user.delivery')->get();
        return response()->json([
            'delivaries'=> $role,
        ]);

    }

    public function getallpharmas(){
        $pharma=Pharma::with('pharmacists')->with('pharmacists.user')->get();
             return response()->json([
            'pharmaswithdetails'=> $pharma,
        ]);
    }

    public function bills($pharma_id)
    {
$cartorder=CartOrder::where('verified',1)->where('pharma_id',$pharma_id)
               ->with('user')
                ->with('cartorderitem.product')
               ->with('cartorderitem.type')
               ->with('applycartorder.delivery.user')->get();

$orders=DeliveryRequest::where('done',1)->with('delivery.user')
                         ->whereHas('pharmaUser', function ($q) use ($pharma_id) {
            $q->where('pharma_id', $pharma_id);
        })
                      ->with('pharmaUser.order')
                       ->get();


     return response()->json([
            'cart orders'=> $cartorder,
            'public and private orders'=>$orders,
        ]);
    }

    public function getcomplaint(){
        $cpmplaint=Complaint::with('user')
                       ->with('pharma')
                       ->with('pharma.pharmacists')
                       ->with('pharma.pharmacists.user')
                       ->get();

     return response()->json([
            'complaint'=> $cpmplaint,
        ]);

    }

   public function getpharmaslocation(){
    $locations=Pharma::select('name','length','width')->get();
      return response()->json([
            'locations'=> $locations,
        ]);
   }

   public function deleteuser($user_id){
     $user=User::where('id',$user_id)->delete();
       return response()->json([
            'message'=>'user deleted succesfully',
        ]);
   }

      public function deletepharma($pharma_id){
     $pharma=Pharma::where('id',$pharma_id)->delete();
       return response()->json([
            'message'=>'pharma deleted succesfully',
        ]);
   }

    public static function getMonthlyOrderCounts()
    {

$deliveryData = DeliveryRequest::selectRaw("YEAR(created_at) as y, MONTH(created_at) as m, COUNT(*) as total")
    ->groupByRaw("YEAR(created_at), MONTH(created_at)")
    ->get()
    ->map(function ($item) {
        return [
            'month' => sprintf('%04d-%02d', $item->y, $item->m),
            'total' => $item->total
        ];
    });

$applyCartData = ApplyCartOrder::selectRaw("YEAR(created_at) as y, MONTH(created_at) as m, COUNT(*) as total")
    ->groupByRaw("YEAR(created_at), MONTH(created_at)")
    ->get()
    ->map(function ($item) {
        return [
            'month' => sprintf('%04d-%02d', $item->y, $item->m),
            'total' => $item->total
        ];
    });

$monthlyOrders = $deliveryData
    ->merge($applyCartData)
    ->groupBy('month')
    ->map(function ($items, $month) {
        return [
            'month' => $month,
            'total_orders' => $items->sum('total')
        ];
    })
    ->sortBy('month')
    ->values();

      return $monthlyOrders;
    }


    public function detect_price($requset){
    $delivery = deliveryprice::first();

     if ($delivery) {
    $delivery->update(['delivery_price' => $requset->price]);
        } else {
     deliveryprice::create(['delivery_price' => $requset->price]);
        }
          return response()->json([
            'message'=>'price added succesfully',
        ]);
    }

    public function edit_price($requset){
        $price=deliveryprice::first();
        $price->delivery_price=$requset->price;
        $price->save();
                return response()->json([
            'message'=>'price edited succesfully',
        ]);
    }

public function getdeliveryprice(){
        $price=deliveryprice::first();
                    return response()->json([
            'the value is'=>$price->delivery_price,
        ]);
    }




}
