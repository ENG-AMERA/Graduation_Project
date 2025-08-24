<?php
namespace App\Http\Services;
use Carbon\Carbon;
use App\Http\Repositories\OrderRepository;
use App\Http\Requests\ApplyPointDiscountRequest;
use App\Models\Cart;
use App\Models\Pharmacist;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    $cartId     = $request->input('cart_id');      // بدّلنا من order_id إلى cart_id
    $pointsUsed = (int) $request->input('points_used');

    if ($pointsUsed <= 0) {
        return response()->json(['error' => 'points_used must be > 0'], 422);
    }

    return DB::transaction(function () use ($user, $cartId, $pointsUsed) {

       $cart = Cart::lockForUpdate()
    ->where('id', $cartId)
    ->where('user_id', $user->id)
    ->first();

if (!$cart) {
    return response()->json(['error' => 'Cart not found for this user.'], 404);
}

$originalPrice = $cart->totalprice;

        if (is_null($originalPrice)) {
            // احسب من العناصر إن وُجدت
            $originalPrice = optional($cart->items)->sum('totalprice') ?? 0;
        }

        // تأكّد أن في صيدلية مربوطة بالسلة
        if (!$cart->pharma_id) {
            return response()->json(['error' => 'Cart has no associated pharmacy.'], 422);
        }

        // 3) جلب الصيدلاني المرتبط بهذه الصيدلية والتأكد من قبول النقاط
        $pharmacist = Pharmacist::where('pharma_id', $cart->pharma_id)->first();

        if (!$pharmacist || (int)$pharmacist->accept_point !== 1) {
            return response()->json(['error' => 'This pharmacist does not accept points.'], 403);
        }

        // 4) التحقق من رصيد نقاط المستخدم
        if ($user->points < $pointsUsed) {
            return response()->json(['error' => 'Not enough points.'], 400);
        }

        // 5) حساب الخصم وتطبيقه
        // قيمة النقطة يحددها الصيدلاني: point_value
        $pointValue = (float) $pharmacist->point_value;
        if ($pointValue <= 0) {
            return response()->json(['error' => 'Invalid point value configured for this pharmacist.'], 422);
        }

        $discount  = $pointValue * $pointsUsed;
        $newPrice  = max($originalPrice - $discount, 0);

        // 6) حفظ التغييرات: تحديث سعر السلة وتخفيض نقاط المستخدم
        $cart->totalprice = $newPrice;
        $cart->save();

        $user->points = $user->points - $pointsUsed;
        $user->save();

        return response()->json([
            'message'          => 'Discount applied successfully',
            'cart_id'          => $cart->id,
            'pharma_id'        => $cart->pharma_id,
            'original_price'   => (float) $originalPrice,
            'point_value'      => $pointValue,
            'points_used'      => $pointsUsed,
            'discount'         => (float) $discount,
            'new_price'        => (float) $newPrice,
            'remaining_points' => (int) $user->points,
        ]);
    });
}
}
