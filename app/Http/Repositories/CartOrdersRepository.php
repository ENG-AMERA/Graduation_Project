<?php
namespace App\Http\Repositories;

use App\Models\Cart;
use App\Models\Type;
use App\Models\Pharma;
use App\Models\Product;
use App\Models\CartOrder;
use Illuminate\Support\Str;
use App\Models\CartOrderItem;
use App\Models\ApplyCartOrder;
use App\Models\Delivery;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CartOrdersRepository{
public function confirmorder($request)
{
    $user_id = Auth::id();
    $cart = Cart::where('id', $request->cart_id)->first();

    if (!$cart) {
        return response()->json(['error' => 'Cart not found'], 404);
    }

    $cartItems = $cart->cart_item;
    $pharma = Pharma::find($request->pharma_id);
    $deliveryprice = $this->calculateDeliveryPrice($pharma->width, $pharma->length, $request->width, $request->length);

    $issues = [];

    // ✅ تحقق من توفر الكميات لكل العناصر
    foreach ($cartItems as $item) {
        $product = Product::find($item->product_id);
        if (!$product) {
            $issues[] = [
                'error_type' => 'product_not_found',
                'product_id' => $item->product_id
            ];
            continue;
        }

        if ($product->quantity < $item->quantity) {
            $issues[] = [
                'item_type' => 'product',
                'name' => $product->name,
                'available_quantity' => $product->quantity,
                'requested_quantity' => $item->quantity
            ];
        }

        if ($item->type_id) {
            $type = Type::find($item->type_id);
            if (!$type) {
                $issues[] = [
                    'error_type' => 'type_not_found',
                    'type_id' => $item->type_id
                ];
                continue;
            }

            if ($type->quantity < $item->quantity) {
                $issues[] = [
                    'item_type' => 'type',
                    'name' => $type->name,
                    'available_quantity' => $type->quantity,
                    'requested_quantity' => $item->quantity
                ];
            }
        }
    }

    // ✅ إذا في مشاكل رجعها كلها
    if (!empty($issues)) {
        return response()->json([
            'error' => 'Some items have insufficient quantity',
            'details' => $issues
        ], 400);
    }

    // ✅ تابع تنفيذ الطلب إذا كل شي تمام
    DB::beginTransaction();

    try {
        $cartOrder = CartOrder::create([
            'user_id' => $user_id,
            'totalprice' => $cart->totalprice,
            'pharma_id' => $cart->pharma_id,
            'length' => $request->length,
            'width' => $request->width,
            'deliverydate' => $request->deliverydate,
            'deliveryprice' => $deliveryprice,
            'done' => '0'
        ]);

        foreach ($cartItems as $item) {
            CartOrderItem::create([
                'product_id' => $item->product_id,
                'type_id' => $item->type_id != 0 ? $item->type_id : null,
                'totalprice' => $item->totalprice,
                'quantity' => $item->quantity,
                'cart_order_id' => $cartOrder->id,
            ]);

            $product = Product::find($item->product_id);
            $product->quantity -= $item->quantity;
            $product->save();

            if ($item->type_id) {
                $type = Type::find($item->type_id);
                $type->quantity -= $item->quantity;
                $type->save();
            }
        }

        $cart->delete();
        DB::commit();
    } catch (\Exception $e) {
        DB::rollback();
        throw $e;
    }

    return response()->json([
        'delivery_price' => $deliveryprice,
        'cart_total_price' => $cart->totalprice,
    ]);
}




public function calculateDeliveryPrice($lat1, $lon1, $lat2, $lon2, $pricePerKm = 2)
{
    $earthRadius = 6371; // نصف قطر الأرض بالكيلومتر

    // تحويل الإحداثيات إلى راديان
    $lat1 = deg2rad($lat1);
    $lon1 = deg2rad($lon1);
    $lat2 = deg2rad($lat2);
    $lon2 = deg2rad($lon2);

    // حساب الفروق
    $deltaLat = $lat2 - $lat1;
    $deltaLon = $lon2 - $lon1;

    // معادلة هافرسين
    $a = sin($deltaLat / 2) ** 2 +
         cos($lat1) * cos($lat2) * sin($deltaLon / 2) ** 2;

    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    $distance = $earthRadius * $c;

    // حساب السعر
    $deliveryPrice = $distance * $pricePerKm;

    return round($deliveryPrice, 2);
}

public function getallcartorderforpharmacist($pharma_id){
   $cartOrders = CartOrder::with(['cartorderitem.product', 'cartorderitem.type'])
        ->where('pharma_id', $pharma_id)
        ->where('accepted', 0)
        ->get();

    return response()->json($cartOrders);
}

public function acceptcartorder($cartorder_id){
    $cartorder=CartOrder::where('id',$cartorder_id)->first();
    $cartorder->accepted='1';
    $cartorder->save();
}

public function getcartordertodelivery(){
    $orders = CartOrder::where('done',0)->where('accepted','1')->with(['user', 'pharma'])->get();
    return response()->json($orders);
}


public function generateQr($cartorder_id)
{
    $user_id=Auth::id();
    $delivery=Delivery::where('user_id',$user_id)->first();

        $qr = (string) Str::uuid();

        $apply =ApplyCartOrder::create([
             'qr'=>$qr,
             'delivery_id'=>$delivery->id,
             'cart_order_id'=>$cartorder_id
        ]);

        $cartorder=CartOrder::where('id',$cartorder_id)->first();
        $cartorder->done='1';
        $cartorder->save();


    $qrSvg = QrCode::format('svg')->size(300)->generate($qr);
    return response($qrSvg)->header('Content-Type', 'image/svg+xml');
}

public function show_qr_ofcartorderwithdetail($cartorder_id)
{
    $Apply_cart_order=ApplyCartOrder::where('cart_order_id',$cartorder_id)
                           ->first();
         $cartorder=CartOrder::where('id',$cartorder_id)
          ->where('done',1)->where('accepted','1')
        ->where('verified',0)->first();
         $pharma=$cartorder->pharma;
         $price=$cartorder->totalprice;
         $deliveryPrice=$cartorder->deliveryprice;
         $deliverydate=$cartorder->deliverydate;



 $qrSvg = QrCode::format('svg')->size(300)->generate($Apply_cart_order->qr);
$base64Svg = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);

return response()->json([
    'pharma' => $pharma,
    'price' => $price,
    'delivery_price' => $deliveryPrice,
    'delivery_date' => $deliverydate,
    'qr_svg_base64' => $base64Svg,
    'qr_svg_text' => (string) $qrSvg, ]);
}


    public function verifyQr($request)
    {
        $qr=$request->qr;
        $user_id=Auth::id();
        $delivery=Delivery::where('user_id',$user_id)->first();
        $applycartorder =ApplyCartOrder::where('qr',$qr)
                                       ->where('delivery_id',$delivery->id)->first();

        if (!$applycartorder) {
            return response()->json(['valid' => false, 'message' => 'QR not valid'], 404);
        }

        $cartorder=CartOrder::where('id',$applycartorder->cart_order_id)->first();

        $cartorder->verified = '1';
        $cartorder->save();

        return response()->json([
            'valid' => true,
            'message' => 'QR is valid and marked as done',
        ]);
    }



}
