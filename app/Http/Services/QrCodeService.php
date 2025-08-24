<?php


namespace App\Http\Services;

use App\http\Repositories\QrCodeRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
   
use App\Http\Services\FcmService;
use App\Models\User;

class QrCodeService
{
    protected $repository;

    public function __construct(QrCodeRepository $repository)
    {
        $this->repository = $repository;
    }
    
public function generateQr($orderId, FcmService $fcmService)
{
    $delivery = $this->repository->findDeliveryByUserId(Auth::id());
    if (!$delivery) {
        return response()->json(['error' => 'Delivery not found'], 404);
    }

    $pharmaUser = $this->repository->findPharmaUserByOrderId($orderId);
    if (!$pharmaUser) {
        return response()->json(['error' => 'PharmaUser not found'], 404);
    }

    $deliveryRequest = $this->repository->findDeliveryRequestByPharmaUserId($pharmaUser->id);
    if (!$deliveryRequest) {
        return response()->json(['error' => 'DeliveryRequest not found'], 404);
    }

    $justAssigned = false;
    if (is_null($deliveryRequest->delivery_id)) {
        $deliveryRequest->qr = (string) Str::uuid();
        $deliveryRequest->delivery_id = $delivery->id;
        $deliveryRequest->save();
        $justAssigned = true;
    }

   
    $qrSvg = QrCode::format('svg')->size(300)->generate($deliveryRequest->qr);

    $pharmaUser->load('user'); 
    $orderUser = $pharmaUser->user ?? null;

    $notified = false;
    $notifyMessage = null;

 
    if ($justAssigned && $orderUser && !empty($orderUser->device_token)) {
        $title = 'تم تعيين مندوب للتوصيل';
        $body  = 'تم تعيين مندوب لطلبك رقم #' . $orderId . '. الرجاء إظهار رمز الـ QR للمندوب عند الاستلام.';
        $resp = $fcmService->sendNotification($orderUser->device_token, $title, $body, [
            'order_id' => $orderId,
            'delivery_request_id' => $deliveryRequest->id,
            'qr' => $deliveryRequest->qr,
        ]);
        $notified = ($resp['success'] ?? false) === true;
        $notifyMessage = $notified ? 'User notified via FCM.' : ($resp['error'] ?? 'FCM send failed.');
    }

    return response()->json([
        'message' => $justAssigned ? 'Delivery assigned and QR generated.' : 'Delivery was already assigned.',
        'notified' => $notified,
        'notify_info' => $notifyMessage,
        'delivery_request_id' => $deliveryRequest->id,
        'qr' => $deliveryRequest->qr,
        // 'qr_svg' => $qrSvg, // لو حابب ترسله للفرونت (حجمه كبير عادة)
    ], 200);
}   public function showQr($orderId)
    {
        $pharmaUser = $this->repository->findPharmaUserByUserIdAndOrderId(Auth::id(), $orderId);
        if (!$pharmaUser) {
            return response()->json(['error' => 'PharmaUser not found for this order.'], 404);
        }

        $deliveryRequest = $this->repository->findDeliveryRequestByPharmaUserId($pharmaUser->id);
        if (!$deliveryRequest || !$deliveryRequest->qr) {
            return response()->json(['error' => 'DeliveryRequest or QR not found'], 404);
        }

        $qrSvg = QrCode::format('svg')->size(300)->generate($deliveryRequest->qr);
        return response($qrSvg)->header('Content-Type', 'image/svg+xml');
    }
public function verifyQr(string $qr, FcmService $fcmService)
{
    $deliveryRequest = $this->repository->findDeliveryRequestByQr($qr);

    if (!$deliveryRequest) {
        return response()->json(['valid' => false, 'message' => 'QR not valid'], 404);
    }

    $alreadyDone = (int) $deliveryRequest->done === 1;

    $deliveryRequest->done = 1;
    $deliveryRequest->save();

 
    $deliveryRequest->load([
        'pharmaUser.pharma.pharmacists', 
        'delivery',
    
        'pharmaUser.user',
    ]);

    $pharmaUser  = $deliveryRequest->pharmaUser;
    $pharma      = $pharmaUser?->pharma;
    $pharmacist  = $pharma?->pharmacists;

  
    $pharmacistUID = $pharmacist?->user_id;
    $pharmacistUser = $pharmacistUID
        ? User::query()->select('id', 'device_token')->find($pharmacistUID)
        : null;
    $pharmacistToken = $pharmacistUser?->device_token;

   
    $orderId     = $pharmaUser?->order_id;
    $deliveryId  = $deliveryRequest->delivery_id;
    $totalPrice  = $deliveryRequest->totalprice ?? null;
    $price       = $deliveryRequest->price ?? null;
    $pharmaName  = $pharma?->name ?? null; 
    $consumerUID = $pharmaUser?->user_id;

    $notified = false;
    $notifyInfo = 'No device_token for pharmacist user.';

    if (!$alreadyDone && !empty($pharmacistToken)) {
        $title = 'تم تسليم الطلب';
        $body  = 'تم تأكيد تسليم الطلب رقم #' . ($orderId ?? '-') . ' بنجاح.';

        $data = [
            'type'                 => 'order_delivered',
            'delivery_request_id'  => $deliveryRequest->id,
            'order_id'             => $orderId,
            'delivery_id'          => $deliveryId,
            'pharma_id'            => $pharma?->id,
            'pharma_name'          => $pharmaName,
            'pharmacist_id'        => $pharmacist?->id,
            'pharmacist_user_id'   => $pharmacistUID,
            'consumer_user_id'     => $consumerUID,
            'price'                => $price,
            'totalprice'           => $totalPrice,
            'done'                 => 1,
        ];

        $resp = $fcmService->sendNotification($pharmacistToken, $title, $body, $data);
        $notified   = ($resp['ok'] ?? false) === true;
        $notifyInfo = $notified ? 'Pharmacist notified via FCM.' : ($resp['error'] ?? 'FCM send failed.');
    }

    return response()->json([
        'valid'       => true,
        'message'     => $alreadyDone
            ? 'QR already verified earlier; marked as done.'
            : 'QR is valid, marked as done, and pharmacist notified.',
        'notified'    => $notified,
        'notify_info' => $notifyInfo,
        'data' => [
            'delivery_request_id' => $deliveryRequest->id,
            'pharma_user_id'      => $deliveryRequest->pharma_user_id,
            'delivery_id'         => $deliveryId,
            'order_id'            => $orderId,
            'pharmacist_id'       => $pharmacist?->id,
            'pharmacist_user_id'  => $pharmacistUID,
            'done'                => $deliveryRequest->done,
        ],
    ], 200);
}
}
