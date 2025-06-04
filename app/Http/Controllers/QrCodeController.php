<?php
namespace App\Http\Controllers;

use App\Http\Requests\GenerateQrRequest;
use App\Http\Requests\ShowQrRequest;
use App\Http\Requests\VerifyQrRequest;
use App\Http\Services\QrCodeService;
use Illuminate\Http\Request;

class QrCodeController extends Controller
{
    protected $qrCodeService;

    public function __construct(QrCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    public function generate(GenerateQrRequest $request)
    {
        return $this->qrCodeService->generateQr($request->order_id);
    }

    public function showQrFromDatabase(ShowQrRequest $request)
    {
        return $this->qrCodeService->showQr($request->order_id);
    }

    public function verifyQr(VerifyQrRequest $request)
    {
        return $this->qrCodeService->verifyQr($request->qr);
    }
}
