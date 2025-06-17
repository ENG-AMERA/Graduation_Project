<?php
namespace App\Http\Repositories;

use App\Models\DeliveryRequest;
use App\Models\PharmaUser;
use App\Models\Order;
use App\Models\Pharmacist;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderRepository
{
    public function createPharma(array $data)
    {
        return PharmaUser::create($data);
    }

    public function createOrder(array $data)
    {
        return Order::create($data);
    }
    
public function acceptOrder(array $data)
{


    // Find the PharmaUser record
     $pharmaUser = PharmaUser::where('order_id', $data['order_id'])->first();


    if (!$pharmaUser) {
        return response()->json(['message' => 'PharmaUser not found'], 404);
    }
 
    // Update accept_pharma = 1
    $pharmaUser->update([
        'accept_user' => 1,
        

]);

   

    return response()->json(['message' => 'Order accepted and delivery request created'], 200);
}

public function refuseOrder(array $data)
{
    // Find the PharmaUser record by order_id
    $pharmaUser = PharmaUser::where('order_id', $data['order_id'])->first();

    if (!$pharmaUser) {
        return response()->json(['message' => 'PharmaUser not found'], 404);
    }

    // Delete the PharmaUser record
    $pharmaUser->delete();

    // Optionally delete from orders table too
    $order = Order::find($data['order_id']);
    if ($order) {
        $order->delete();
    }

    return response()->json(['message' => 'PharmaUser and order deleted successfully.']);
}


/*

 public function getAcceptedOrdersWithPrice()
    {
        return DB::table('pharma_users')
            ->join('orders', 'pharma_users.order_id', '=', 'orders.id')
            ->leftJoin('delivery_requests', 'pharma_users.id', '=', 'delivery_requests.pharma_user_id')
           
            ->where('pharma_users.accept_pharma', 1)
            ->select(
                'orders.id as order_id',
                'orders.name_medicine',
                'orders.photo',
                'orders.length',
                'orders.width',
                'orders.type',
                'orders.time',
                'delivery_requests.price'
            )
            ->get();
    }*/
public function getAcceptedOrdersWithPrice()
{
    $orders = DB::table('pharma_users')
        ->join('orders', 'pharma_users.order_id', '=', 'orders.id')
        ->leftJoin('delivery_requests', 'pharma_users.id', '=', 'delivery_requests.pharma_user_id')
        ->join('pharmas', 'pharma_users.pharma_id', '=', 'pharmas.id')
        ->where('pharma_users.accept_pharma', 1)
        ->whereNull('pharma_users.accept_user')
        ->select(
            'pharmas.name as pharma_name',
            'orders.id as order_id',
            'orders.name_medicine',
            'orders.photo',
            'orders.length',
            'orders.width',
            'orders.type',
            'orders.time',
            'delivery_requests.price'
        )
        ->get();

    // Append photo_path to each item
    $orders->transform(function ($order) {
        if ($order->photo) {
            if (filter_var($order->photo, FILTER_VALIDATE_URL)) {
                $parsed = parse_url($order->photo);
                $order->photo_path = ltrim($parsed['path'], '/');
            } else {
                $order->photo_path = $order->photo;
            }
        } else {
            $order->photo_path = null;
        }
        return $order;
    });

    return $orders;
}



////
    public function getPharmaUser($orderId, $userId)
    {
        return PharmaUser::where('order_id', $orderId)
            ->where('user_id', $userId)
            ->where('accept_pharma', 1)
            ->where('accept_user', 1)
            ->first();
    }

    public function getDeliveryRequest($pharmaUserId)
    {
        return DeliveryRequest::where('pharma_user_id', $pharmaUserId)->first();
    }

    public function getPharmacist($pharmaId)
    {
        return Pharmacist::where('pharma_id', $pharmaId)
            ->where('accept', 1)
            ->first();
    }




}
