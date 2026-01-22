<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\OneSignalService;
use Illuminate\Http\Request;

class OneSignalTestController extends Controller
{
    protected $oneSignalService;

    public function __construct(OneSignalService $oneSignalService)
    {
        $this->oneSignalService = $oneSignalService;
    }

    /**
     * Send test push notification to all subscribed users
     */
    public function sendTest(Request $request)
    {
        try {
            $response = $this->oneSignalService->sendToAll(
                "Test Notification",
                "Hello from Laravel ✅",
                [
                    "type" => "TEST",
                    "screen" => "home"
                ]
            );

            // Store raw response in session for debugging
            session(['onesignal_last_response' => $response]);

            // Check if response has errors
            if (isset($response['errors']) && !empty($response['errors'])) {
                $errorMessage = is_array($response['errors']) 
                    ? implode(', ', $response['errors']) 
                    : 'Failed to send notification';
                
                return back()->with('error', $errorMessage);
            }

            // Check if response has id (success indicator)
            if (isset($response['id'])) {
                return back()->with('success', 'Push notification sent successfully! Recipients: ' . ($response['recipients'] ?? 'N/A'));
            }

            // If no errors but also no success indicator, show response
            return back()->with('success', 'Notification sent. Response: ' . json_encode($response));

        } catch (\Exception $e) {
            session(['onesignal_last_response' => ['error' => $e->getMessage()]]);
            return back()->with('error', 'Error sending notification: ' . $e->getMessage());
        }
    }
}
