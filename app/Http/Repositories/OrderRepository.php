<?php
namespace App\Http\Repositories;

use App\Models\DeliveryRequest;
use App\Models\PharmaUser;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderRepository
{
    public function createPharma(array $data): PharmaUser
    {
        return PharmaUser::create($data);
    }

    public function createOrder(array $data): Order
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
    return DB::table('pharma_users')
        ->join('orders', 'pharma_users.order_id', '=', 'orders.id')
        ->leftJoin('delivery_requests', 'pharma_users.id', '=', 'delivery_requests.pharma_user_id')
        ->join('pharmas', 'pharma_users.pharma_id', '=', 'pharmas.id') // join pharmas
        ->where('pharma_users.accept_pharma', 1)
        ->select(
            'pharmas.name as pharma_name', // get pharma name
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
}


}
