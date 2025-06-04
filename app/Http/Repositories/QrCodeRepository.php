<?php
namespace App\http\Repositories;

use App\Models\Delivery;
use App\Models\PharmaUser;
use App\Models\DeliveryRequest;

class QrCodeRepository
{
    public function findDeliveryByUserId($userId)
    {
        return Delivery::where('user_id', $userId)->first();
    }

    public function findPharmaUserByOrderId($orderId)
    {
        return PharmaUser::where('order_id', $orderId)->first();
    }

    public function findPharmaUserByUserIdAndOrderId($userId, $orderId)
    {
        return PharmaUser::where('user_id', $userId)
                         ->where('order_id', $orderId)
                         ->first();
    }

    public function findDeliveryRequestByPharmaUserId($pharmaUserId)
    {
        return DeliveryRequest::where('pharma_user_id', $pharmaUserId)->first();
    }

    public function findDeliveryRequestByQr($qr)
    {
        return DeliveryRequest::where('qr', $qr)->first();
    }
}
