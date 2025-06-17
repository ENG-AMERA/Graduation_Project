<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\deliveryRequest;
use App\Http\Requests\AcceptDeliveryRequest;
use App\Http\Services\deliveryService;
class delivaryController extends Controller
{
    protected $deliveryService;

    public function __construct(deliveryService $deliveryService)
    {
        $this->deliveryService = $deliveryService;
        
    }
 



    public function delivery_request(deliveryRequest $request)
    {
        $result = $this->deliveryService->createdelivery($request->validated());

        return response()->json([
            'message' => 'delivery created successfully.',
            'data' => $result,
        ], 201);
    }

       public function accept(AcceptDeliveryRequest $request)
    {
        $delivery = $this->deliveryService->accept($request->id);
        return response()->json(['message' => 'delivery accepted successfully', 'data' => $delivery], 200);
    }
    public function deletdelivery($id)
    {
        try {
            $this->deliveryService->deletdelivery($id);
            return response()->json(['message' => 'delivery and pharma deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

     public function getPendingDelivery()
    {
        $delivery = $this->deliveryService->getAllPending();
        return response()->json($delivery);
    }


     public function getPendingRequests()
    {
        $data = $this->deliveryService->getPendingDeliveryRequests();
        return response()->json($data);
    }

    
     public function getConsumerPendingRequests()
    {
        $data = $this->deliveryService->getConsumerPendingRequests();
        return response()->json($data);
    }

    

}
