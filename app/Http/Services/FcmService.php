<?php
namespace App\Http\Services;

use GuzzleHttp\Exception\RequestException;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Http;

class FcmService
{
    protected $credentialsPath;
    protected $projectId;

    public function __construct()
    {
        $this->credentialsPath = config('services.fcm.credentialsPath');
        $this->projectId = config('services.fcm.project_id');
    }

    public function sendNotification($to, $title, $body)
    {
        try {
            // Load Service Account credentials from JSON file
            $credentials = new ServiceAccountCredentials(
                'https://www.googleapis.com/auth/firebase.messaging',
                $this->credentialsPath
            );

            // Get OAuth 2.0 token
            $authToken = $credentials->fetchAuthToken()['access_token'];

            // If the token is not fetched, throw an error
            if (!$authToken) {
                throw new \Exception('Failed to fetch OAuth token.');
            }

            $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

            // Send notification via HTTP request to FCM API
            $response = Http::withToken($authToken)->post($url, [
                'message' => [
                    'token' => $to,
                    'data' => [
                        'title' => $title,
                        'body' => $body,
                        'click_action' => "FLUTTER_NOTIFICATION_CLICK"
                    ],
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'android' => [
                        'priority' => "high",
                    ],
                    'apns' => [
                        'headers' => [
                            'apns-priority' => '10',
                        ],
                        'payload' => [
                            'aps' => [
                                'content-available' => 1,
                                'badge' => 5,
                                'priority' => "high",
                            ]
                        ]
                    ]
                ]
            ]);

            // Check if response is successful and return response content
            if ($response->successful()) {
                return $response->getBody()->getContents();
            } else {
                return $this->handleErrorResponse($response);
            }
        } catch (RequestException $e) {
            // Handle request exception errors
            return $this->handleException($e);
        } catch (\Exception $e) {
            // General error handling
            return $this->handleGeneralException($e);
        }
    }

    /**
     * Handle error responses from FCM API
     *
     * @param $response
     * @return string
     */
    protected function handleErrorResponse($response)
    {
        $errorData = $response->json();
        return isset($errorData['error']['message']) 
            ? $errorData['error']['message'] 
            : 'Unknown error occurred.';
    }

    /**
     * Handle exceptions thrown by GuzzleRequest or other sources
     *
     * @param RequestException $e
     * @return string
     */
    protected function handleException(RequestException $e)
    {
        return "Request error: " . $e->getMessage();
    }

    /**
     * Handle general exceptions that are not RequestExceptions
     *
     * @param \Exception $e
     * @return string
     */
    protected function handleGeneralException(\Exception $e)
    {
        return "General error: " . $e->getMessage();
    }
}
