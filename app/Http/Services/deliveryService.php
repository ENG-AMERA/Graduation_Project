<?php


namespace App\Http\Services;
use App\Http\Repositories\deliveryRepository;
use App\Models\DeliveryRequest;
use Illuminate\Support\Facades\Auth;
//use App\\Http\Repositories\PharmacistRepository;

class deliveryService
{


    protected $deliveryRepository;

    public function __construct(deliveryRepository $deliveryRepository)
    {
        $this-> deliveryRepository = $deliveryRepository;
    }

    public function createdelivery(array $data)
    {

        $userId = Auth::id();
        // Create Pharmacist linked to Pharma
        $delivery = $this->deliveryRepository->createdelivery([

            'user_id' => $userId,
            'delivery_method' => $data['delivery_method'],
            'number_method'=>$data['number_method']
        ]);

        return [
            'delivery' => $delivery,

        ];
    }

    public function accept($id,FcmService $fcmService)
    {
        return $this->deliveryRepository->accept($id,$fcmService);
    }

    public function deletdelivery($id,FcmService $fcmService)
    {
        try {
            // Call the repository to delete the pharmacist and pharma
            return $this->deliveryRepository->deletdelivery($id,$fcmService);
        } catch (\Exception $e) {
            throw new \Exception("Failed to delete delivery and pharma: " . $e->getMessage());
        }
    }



      public function getAllPending()
    {
        return $this->deliveryRepository->getPendingdelivery();
    }



    public function getPendingDeliveryRequests()
    {
        return $this->deliveryRepository->getPendingRequestsWithPharmaAndOrder();
    }

    public function getConsumerPendingRequests()
    {
        return $this->deliveryRepository->getConsumerPendingRequests();
    }
public function getAcceptedRequestsByDelivery()
    {
        $deliveryId = auth()->id(); 

        
        $delivery = \App\Models\Delivery::where('user_id', $deliveryId)->first();

        if (!$delivery) {
            return collect(); 
        }

        return DeliveryRequest::with('pharmaUser.user', 'pharmaUser.pharma', 'pharmaUser.order')
            ->where('delivery_id', $delivery->id)
            ->where('done',null)
            ->get();
    }

}
