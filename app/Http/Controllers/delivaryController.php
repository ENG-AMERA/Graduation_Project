<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\deliveryRequest;
use App\Http\Services\deliveryService;
use App\Http\Requests\AcceptDeliveryRequest;
use App\Http\Repositories\CartOrdersRepository;
use App\Http\Repositories\deliveryRepository;
use App\Http\Services\FcmService;

class delivaryController extends Controller
{
    protected $deliveryService;
    protected $cartorderrepo;
    protected $deliveryRepository;
    public function __construct(deliveryService $deliveryService , CartOrdersRepository $cartorderrepo,deliveryRepository   $deliveryRepository)
    {
        $this->deliveryService = $deliveryService;
        $this->cartorderrepo = $cartorderrepo;
        $this->deliveryRepository = $deliveryRepository;


    }
                                    

    public function delivery_request(deliveryRequest $request)
    {
        $result = $this->deliveryService->createdelivery($request->validated());

        return response()->json([
            'message' => 'delivery created successfully.',
            'data' => $result,
        ], 201);
    }

       public function accept(AcceptDeliveryRequest $request, FcmService $fcmService)
    {
        $delivery = $this->deliveryService->accept($request->id,$fcmService);
        return response()->json(['message' => 'delivery accepted successfully', 'data' => $delivery], 200);
    }
    
    public function deletdelivery($id, FcmService $fcmService)
    {
        try {
            $delivery  = $this->deliveryService->deletdelivery($id,$fcmService);
            return response()->json(['message' => 'delivery and pharma deleted successfully.','data'=>  $delivery ],200);
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

   public function getConsumerPendingRequests()
    {
        $data= $this->deliveryService->getConsumerPendingRequests();
         return response()->json($data);
    }


     public function acceptedRequests()
    {
        $requests = $this->deliveryService->getAcceptedRequestsByDelivery();

        return response()->json($requests);
    }
public function cart_order_archive(){
        return $this->deliveryRepository->cart_order_archive();
    }   
}
