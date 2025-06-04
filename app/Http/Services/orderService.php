<?php
namespace App\Http\Services;
use Carbon\Carbon;
use App\Http\Repositories\OrderRepository;

class OrderService
{
    protected $orderRepo;

    public function __construct(OrderRepository $orderRepo)
    {
        $this->orderRepo = $orderRepo;
    }

   public function publicOrder(array $data, $user)
{
if (isset($data['photo']) && $data['photo']) {
    $photo = $data['photo'];
    $photoExtension = $photo->getClientOriginalExtension();
    $photoName = time() . '_photo.' . $photoExtension;
    $photoPath = 'orders'; // folder inside /public
    $photo->move(public_path($photoPath), $photoName);
    $photoRelativePath = $photoPath . '/' . $photoName;
    $photoFullUrl = url($photoRelativePath); 
    $data['photo'] = $photoFullUrl;
}
    $data['user_id'] = $user->id;

    if (!empty($data['time'])) {
        try {
            $data['time'] = Carbon::createFromFormat('m/d/Y', $data['time'])->format('Y-m-d');
        } catch (\Exception $e) {
            throw new \InvalidArgumentException("Invalid time format. Expected MM/DD/YYYY.");
        }
    }

    $order = $this->orderRepo->createorder($data);

    $this->orderRepo->createpharma([
        'user_id' => $user->id,
        'pharma_id' => null,
        'type' => 0,
        'order_id'=>  $order->id,
        'reason' => null,
        'accept_user' => null,
        'accept_pharma' => null,
    ]);

    return $order;
}




public function privateOrder(array $data, $user)
{
    if (isset($data['photo']) && $data['photo']) {
        $photo = $data['photo'];
        $photoExtension = $photo->getClientOriginalExtension();
        $photoName = time() . '_photo.' . $photoExtension;
        $photoPath = 'orders'; // folder inside /public
        $photo->move(public_path($photoPath), $photoName);
        $photoRelativePath = $photoPath . '/' . $photoName;
        $photoFullUrl = url($photoRelativePath); 
        $data['photo'] = $photoFullUrl;
    }

    $data['user_id'] = $user->id;

    if (!empty($data['time'])) {
        try {
            $data['time'] = Carbon::createFromFormat('m/d/Y', $data['time'])->format('Y-m-d');
        } catch (\Exception $e) {
            throw new \InvalidArgumentException("Invalid time format. Expected MM/DD/YYYY.");
        }
    }

    $order = $this->orderRepo->createorder($data);

  
    $this->orderRepo->createpharma([
        'user_id' => $user->id,
        'pharma_id' => $data['pharma_id'] ?? null, 
        'type' => 1,
        'order_id'=>  $order->id,
        'reason' => null,
        'accept_user' => null,
        'accept_pharma' => null,
    ]);

    return $order;
}

    public function acceptOrder(array $data)
{
    return $this->orderRepo->acceptOrder($data);
}


    public function getAcceptedOrders()
    {
        return $this->orderRepo->getAcceptedOrdersWithPrice();
    }

}
