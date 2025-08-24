<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\http\Services\FcmService;

class NotificationController extends Controller
{
    protected $fcmService;

    public function __construct(FcmService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    public function sendTestNotification(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string',
            'title' => 'required|string',
            'body' => 'required|string',
        ]);

        $response = $this->fcmService->sendNotification(
            $validated['token'],
            $validated['title'],
            $validated['body']
        );

        if ($response['success']) {
            return response()->json([
                'message' => 'Notification sent successfully.',
                'result' => $response['result'],
            ]);
        }

        return response()->json([
            'message' => 'Failed to send notification.',
            'error' => $response['error'],
        ], 500);
    }
}
