<?php
namespace App\Http\Services;
use Carbon\Carbon;
use App\Http\Repositories\OrderRepository;
use App\Http\Requests\ApplyPointDiscountRequest;
use Illuminate\Support\Facades\Auth;

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

    public function refuseOrder(array $data)
{
    return $this->orderRepo->refuseOrder($data);
}



    public function getAcceptedOrders()
    {
        return $this->orderRepo->getAcceptedOrdersWithPrice();
    }
  
  
    public function applyPointDiscount(ApplyPointDiscountRequest $request)
{
    /** @var \App\Models\User $user */
    $user = Auth::user();

    if (!$user) {
        return response()->json(['error' => 'Unauthenticated.'], 401);
    }

    $orderId = $request->input('order_id');
    $pointsUsed = $request->input('points_used');

    $pharmaUser = $this->orderRepo->getPharmaUser($orderId, $user->id);

    if (!$pharmaUser) {
        return response()->json(['error' => 'No valid pharma_user found for this order.'], 404);
    }

    $deliveryRequest = $this->orderRepo->getDeliveryRequest($pharmaUser->id);

    if (!$deliveryRequest) {
        return response()->json(['error' => 'No delivery request found for this order.'], 404);
    }

    $pharmacist = $this->orderRepo->getPharmacist($pharmaUser->pharma_id);

    if (!$pharmacist || $pharmacist->accept_point != 1) {
        return response()->json(['error' => 'This pharmacist does not accept points.'], 403);
    }

    if ($user->points < $pointsUsed) {
        return response()->json(['error' => 'Not enough points.'], 400);
    }

    $discount = $pharmacist->point_value * $pointsUsed;
    $originalPrice = $deliveryRequest->price;
    $newPrice = max($originalPrice - $discount, 0);

    $deliveryRequest->price = $newPrice;
    $deliveryRequest->save();

    $user->points -= $pointsUsed;
    $user->save();

    return response()->json([
        'message' => 'Discount applied successfully',
        'order_id' => $orderId,
        'original_price' => $originalPrice,
        'discount' => $discount,
        'new_price' => $newPrice,
        'remaining_points' => $user->points
    ]);
}
}
