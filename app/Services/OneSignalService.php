<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use App\Models\Setting;
use App\Models\User;

class OneSignalService
{
    protected $appId;
    protected $restApiKey;

    public function __construct()
    {
        $this->appId = Config::get('services.onesignal.app_id');
        $this->restApiKey = Config::get('services.onesignal.rest_api_key');
    }

    /**
     * Add Android channel settings for heads-up notifications with sound
     * The channel must be created in OneSignal Dashboard with:
     * - Importance: Urgent (for heads-up notification)
     * - Sound: Default (or custom)
     *
     * @param array $payload
     * @return array
     */
    protected function addAndroidChannelSettings(array $payload): array
    {
        $androidChannelId = Config::get('services.onesignal.android_channel_id');
        
        // Add Android channel ID if configured
        // OneSignal Category will handle Importance (Urgent) automatically
        if (!empty($androidChannelId)) {
            $payload['android_channel_id'] = $androidChannelId;
        }
        
        // Set sound for all platforms (iOS, Android, Web)
        $payload['sound'] = 'default';
        
        // Explicitly set sound for Android to ensure it plays
        // This works in combination with the channel settings
        $payload['android_sound'] = 'default';
        
        // Add priority to ensure sound plays (High priority = 10)
        // This helps ensure notifications play sound even if device settings are restrictive
        $payload['priority'] = 10;
        $payload['android_priority'] = 10;
        
        return $payload;
    }

    /**
     * Send push notification to all subscribed users
     *
     * @param string $title
     * @param string $message
     * @param array $data
     * @return array|null
     */
    public function sendToAll(string $title, string $message, array $data = [])
    {
        if (empty($this->appId) || empty($this->restApiKey)) {
            return [
                'errors' => ['OneSignal credentials are not configured']
            ];
        }

        $payload = [
            'app_id' => $this->appId,
            'included_segments' => ['All'], // 'All' sends to all subscribed users
            'headings' => [
                'en' => $title
            ],
            'contents' => [
                'en' => $message
            ],
        ];

        if (!empty($data)) {
            $payload['data'] = $data;
        }

        // Add Android channel settings for heads-up + sound
        $payload = $this->addAndroidChannelSettings($payload);

        // OneSignal API requires: Authorization: Key {REST_API_KEY}
        // According to OneSignal documentation, use "Key" prefix, not "Basic"
        $response = Http::withHeaders([
            'Authorization' => 'Key ' . $this->restApiKey,
            'Content-Type' => 'application/json',
        ])->post('https://onesignal.com/api/v1/notifications', $payload);

        return $response->json();
    }

    /**
     * Send push notification to specific player(s)
     *
     * @param array|string $playerIds Single player ID or array of player IDs
     * @param string $title
     * @param string $message
     * @param array $data
     * @return array|null
     */
    public function sendToPlayers($playerIds, string $title, string $message, array $data = [])
    {
        if (empty($this->appId) || empty($this->restApiKey)) {
            return [
                'errors' => ['OneSignal credentials are not configured']
            ];
        }

        // Convert single player ID to array
        if (is_string($playerIds)) {
            $playerIds = [$playerIds];
        }

        $payload = [
            'app_id' => $this->appId,
            'include_player_ids' => $playerIds,
            'headings' => [
                'en' => $title
            ],
            'contents' => [
                'en' => $message
            ],
        ];

        if (!empty($data)) {
            $payload['data'] = $data;
        }

        // Add Android channel settings for heads-up + sound
        $payload = $this->addAndroidChannelSettings($payload);

        $response = Http::withHeaders([
            'Authorization' => 'Key ' . $this->restApiKey,
            'Content-Type' => 'application/json',
        ])->post('https://onesignal.com/api/v1/notifications', $payload);

        return $response->json();
    }

    /**
     * Get all players (subscribed users) from OneSignal
     *
     * @param int $limit
     * @param int $offset
     * @return array|null
     */
    public function getPlayers(int $limit = 50, int $offset = 0)
    {
        if (empty($this->appId) || empty($this->restApiKey)) {
            return [
                'errors' => ['OneSignal credentials are not configured']
            ];
        }

        $response = Http::withHeaders([
            'Authorization' => 'Key ' . $this->restApiKey,
            'Content-Type' => 'application/json',
        ])->get('https://onesignal.com/api/v1/players', [
            'app_id' => $this->appId,
            'limit' => $limit,
            'offset' => $offset,
        ]);

        return $response->json();
    }

    /**
     * Send push notification to user(s) by external_id (user_id from database)
     *
     * @param array|string|int $userIds Single user ID or array of user IDs
     * @param string $title
     * @param string $message
     * @param array $data
     * @return array|null
     */
    public function sendToUsers($userIds, string $title, string $message, array $data = [])
    {
        if (empty($this->appId) || empty($this->restApiKey)) {
            return [
                'errors' => ['OneSignal credentials are not configured']
            ];
        }

        // Convert single user ID to array
        if (!is_array($userIds)) {
            $userIds = [(string)$userIds];
        } else {
            // Convert all to strings
            $userIds = array_map('strval', $userIds);
        }

        $payload = [
            'app_id' => $this->appId,
            'include_aliases' => [
                'external_id' => $userIds
            ],
            'target_channel' => 'push',
            'headings' => [
                'en' => $title
            ],
            'contents' => [
                'en' => $message
            ],
        ];

        if (!empty($data)) {
            $payload['data'] = $data;
        }

        // Add Android channel settings for heads-up + sound
        $payload = $this->addAndroidChannelSettings($payload);

        $response = Http::withHeaders([
            'Authorization' => 'Key ' . $this->restApiKey,
            'Content-Type' => 'application/json',
        ])->post('https://onesignal.com/api/v1/notifications', $payload);

        return $response->json();
    }

    /**
     * Send push notification when order payment is completed
     *
     * @param int $userId
     * @param int $orderId
     * @param float $orderTotal
     * @param string|null $customerName
     * @return array|null
     */
    public function sendOrderPaymentNotification(int $userId, int $orderId, float $orderTotal, ?string $customerName = null)
    {
        // Get notification settings from database
        $defaultTitle = 'تم إتمام الطلب بنجاح';
        $defaultMessage = 'تم إتمام طلبك رقم {order_id} بنجاح. المبلغ: {total}';
        
        $title = Setting::getValue('onesignal_order_payment_title', $defaultTitle);
        $message = Setting::getValue('onesignal_order_payment_message', $defaultMessage);
        
        // If customer name not provided, fetch it
        if (empty($customerName)) {
            $customer = User::find($userId);
            $customerName = $customer ? $customer->name : 'عميلنا العزيز';
        }
        
        // Replace placeholders
        $title = str_replace(
            ['{order_id}', '{total}', '{customer_name}'],
            [$orderId, number_format($orderTotal, 2), $customerName],
            $title
        );
        
        $message = str_replace(
            ['{order_id}', '{total}', '{customer_name}'],
            [$orderId, number_format($orderTotal, 2), $customerName],
            $message
        );
        
        // Send notification to user
        return $this->sendToUsers(
            $userId,
            $title,
            $message,
            [
                'type' => 'ORDER_PAYMENT',
                'order_id' => $orderId,
                'screen' => 'orders'
            ]
        );
    }
}
