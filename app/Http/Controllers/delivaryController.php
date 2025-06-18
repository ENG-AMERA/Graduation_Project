<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\deliveryRequest;
use App\Http\Services\deliveryService;
use App\Http\Requests\AcceptDeliveryRequest;
use App\Http\Repositories\CartOrdersRepository;

class delivaryController extends Controller
{
    protected $deliveryService;
    protected $cartorderrepo;

    public function __construct(deliveryService $deliveryService , CartOrdersRepository $cartorderrepo)
    {
        $this->deliveryService = $deliveryService;
        $this->cartorderrepo = $cartorderrepo;

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

    public function getcartordertodelivery(){
        return $this->cartorderrepo->getcartordertodelivery();

    }

    public function applycartorder($cartorder_id){
        return $this->cartorderrepo->generateQr($cartorder_id);
    }

       public function verifyqrforcartorder(Request $request){
        return $this->cartorderrepo->verifyQr($request);
    }



}
