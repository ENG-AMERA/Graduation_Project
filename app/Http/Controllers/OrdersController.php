<?php

namespace App\Http\Controllers;

use App\Http\Requests\AcceptOrder;
use App\Http\Requests\AcceptOrderRequest;
use Illuminate\Http\Request;
use App\Http\Requests\makeorder;
use App\Http\Services\orderService;
use App\Http\Requests\orderprivate;
use Illuminate\Support\Facades\Auth;

class OrdersController extends Controller
{
       protected $service;

    public function __construct(orderService $service)
    {
        $this->service = $service;
    }

    public function Order(makeorder $request)
    {
        $user = Auth::user();

        $order = $this->service->publicOrder($request->validated(), $user);

        return response()->json([
            'message' => 'Order created successfully.',
            'data' => $order,
        ], 201);
     
    }
      public function OrderPrivate(orderprivate $request)
    {
        $user = Auth::user();

        $order = $this->service->privateOrder($request->validated(), $user);

        return response()->json([
            'message' => 'Order created successfully.',
            'data' => $order,
        ], 201);
     
    }


    
    
public function acceptOrderc(AcceptOrderRequest $request)
{
    return $this->service->acceptOrder($request->validated());
}

    
public function refuseOrderc(AcceptOrderRequest $request)
{
    return $this->service->refuseOrder($request->validated());
}

public function index()
    {
        $data = $this->service->getAcceptedOrders();
        return response()->json($data);
    }




}
