<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\OneSignalService;
use App\Models\Setting;
use Illuminate\Http\Request;

class OneSignalTestController extends Controller
{
    protected $oneSignalService;

    public function __construct(OneSignalService $oneSignalService)
    {
        $this->oneSignalService = $oneSignalService;
    }

    /**
     * Display notifications management page
     */
    public function index()
    {
        // Get users for OneSignal push selection
        $users = \App\Models\User::select('id', 'name', 'email', 'phone', 'role')
            ->orderBy('name')
            ->get();

        // Get order payment notification settings
        $orderPaymentTitle = Setting::getValue('onesignal_order_payment_title', 'تم إتمام الطلب بنجاح');
        $orderPaymentMessage = Setting::getValue('onesignal_order_payment_message', 'تم إتمام طلبك رقم {order_id} بنجاح. المبلغ: {total}');

        // Get order completion rating notification settings
        $orderCompletionRatingTitle = Setting::getValue('onesignal_order_completion_rating_title', 'تم إكمال طلبك');
        $orderCompletionRatingMessage = Setting::getValue('onesignal_order_completion_rating_message', 'تم إكمال طلبك رقم {order_id}. شاركنا رأيك وقيم تجربتك!');

        return view('admin.notifications.index', [
            'onesignal_players' => session('onesignal_players', []),
            'users' => $users,
            'order_payment_title' => $orderPaymentTitle,
            'order_payment_message' => $orderPaymentMessage,
            'order_completion_rating_title' => $orderCompletionRatingTitle,
            'order_completion_rating_message' => $orderCompletionRatingMessage,
        ]);
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
                return back()->with('success', 'Push notification sent successfully! Notification ID: ' . $response['id']);
            }

            // If no errors but also no success indicator, show response
            return back()->with('success', 'Notification sent. Response: ' . json_encode($response));

        } catch (\Exception $e) {
            session(['onesignal_last_response' => ['error' => $e->getMessage()]]);
            return back()->with('error', 'Error sending notification: ' . $e->getMessage());
        }
    }

    /**
     * Send push notification to specific player
     */
    public function sendToPlayer(Request $request)
    {
        $request->validate([
            'player_id' => 'required|string',
            'title' => 'nullable|string|max:255',
            'message' => 'nullable|string',
        ]);

        try {
            $title = $request->input('title', 'Test Notification');
            $message = $request->input('message', 'Hello from Laravel ✅');
            
            $response = $this->oneSignalService->sendToPlayers(
                $request->player_id,
                $title,
                $message,
                [
                    "type" => "TEST",
                    "screen" => "home"
                ]
            );

            session(['onesignal_last_response' => $response]);

            if (isset($response['errors']) && !empty($response['errors'])) {
                $errorMessage = is_array($response['errors']) 
                    ? implode(', ', $response['errors']) 
                    : 'Failed to send notification';
                
                return back()->with('error', $errorMessage);
            }

            if (isset($response['id'])) {
                return back()->with('success', 'Push notification sent to player successfully! Notification ID: ' . $response['id']);
            }

            return back()->with('success', 'Notification sent. Response: ' . json_encode($response));

        } catch (\Exception $e) {
            session(['onesignal_last_response' => ['error' => $e->getMessage()]]);
            return back()->with('error', 'Error sending notification: ' . $e->getMessage());
        }
    }

    /**
     * Get list of subscribed players
     */
    public function getPlayers()
    {
        try {
            $response = $this->oneSignalService->getPlayers(100, 0);
            
            if (isset($response['errors'])) {
                return back()->with('error', 'Failed to fetch players: ' . implode(', ', $response['errors']));
            }

            // Store players in session to display in notifications page
            session(['onesignal_players' => $response['players'] ?? []]);
            
            return back()->with('success', 'Players loaded successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Error fetching players: ' . $e->getMessage());
        }
    }

    /**
     * Send push notification to user by user_id (from database)
     */
    public function sendToUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'title' => 'nullable|string|max:255',
            'message' => 'nullable|string',
        ]);

        try {
            $title = $request->input('title', 'Test Notification');
            $message = $request->input('message', 'Hello from Laravel ✅');
            
            $response = $this->oneSignalService->sendToUsers(
                $request->user_id,
                $title,
                $message,
                [
                    "type" => "TEST",
                    "screen" => "home"
                ]
            );

            session(['onesignal_last_response' => $response]);

            if (isset($response['errors']) && !empty($response['errors'])) {
                $errorMessage = is_array($response['errors']) 
                    ? implode(', ', $response['errors']) 
                    : 'Failed to send notification';
                
                return back()->with('error', $errorMessage);
            }

            if (isset($response['id'])) {
                return back()->with('success', 'Push notification sent to user successfully! Notification ID: ' . $response['id']);
            }

            return back()->with('success', 'Notification sent. Response: ' . json_encode($response));

        } catch (\Exception $e) {
            session(['onesignal_last_response' => ['error' => $e->getMessage()]]);
            return back()->with('error', 'Error sending notification: ' . $e->getMessage());
        }
    }

    /**
     * Update order payment notification settings
     */
    public function updateOrderPaymentSettings(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:500',
        ]);

        try {
            Setting::setValue('onesignal_order_payment_title', $request->title);
            Setting::setValue('onesignal_order_payment_message', $request->message);

            return back()->with('success', 'Order payment notification settings saved successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Error saving settings: ' . $e->getMessage());
        }
    }

    /**
     * Update order completion rating notification settings
     */
    public function updateOrderCompletionRatingSettings(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:500',
        ]);

        try {
            Setting::setValue('onesignal_order_completion_rating_title', $request->title);
            Setting::setValue('onesignal_order_completion_rating_message', $request->message);

            return back()->with('success', 'Order completion rating notification settings saved successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Error saving settings: ' . $e->getMessage());
        }
    }
}
