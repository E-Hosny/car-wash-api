<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

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
            'included_segments' => ['Subscribed Users'],
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

        $response = Http::withBasicAuth($this->restApiKey, '')
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post('https://onesignal.com/api/v1/notifications', $payload);

        return $response->json();
    }
}
