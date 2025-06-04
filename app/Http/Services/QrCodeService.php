<?php


namespace App\Http\Services;

use App\http\Repositories\QrCodeRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeService
{
    protected $repository;

    public function __construct(QrCodeRepository $repository)
    {
        $this->repository = $repository;
    }

    public function generateQr($orderId)
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

        if (is_null($deliveryRequest->delivery_id)) {
            $deliveryRequest->qr = Str::uuid();
            $deliveryRequest->delivery_id = $delivery->id;
            $deliveryRequest->save();
        }

        $qrSvg = QrCode::format('svg')->size(300)->generate($deliveryRequest->qr);
        return response($qrSvg)->header('Content-Type', 'image/svg+xml');
    }

    public function showQr($orderId)
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

    public function verifyQr($qr)
    {
        $deliveryRequest = $this->repository->findDeliveryRequestByQr($qr);

        if (!$deliveryRequest) {
            return response()->json(['valid' => false, 'message' => 'QR not valid'], 404);
        }

        $deliveryRequest->done = 1;
        $deliveryRequest->save();

        return response()->json([
            'valid' => true,
            'message' => 'QR is valid and marked as done',
            'data' => [
                'delivery_request_id' => $deliveryRequest->id,
                'pharma_user_id' => $deliveryRequest->pharma_user_id,
                'delivery_id' => $deliveryRequest->delivery_id,
                'done' => $deliveryRequest->done,
            ],
        ]);
    }
}
