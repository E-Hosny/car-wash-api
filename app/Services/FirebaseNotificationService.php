<?php

namespace App\Services;

use Google\Auth\OAuth2;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseNotificationService
{
    protected $projectId = 'luxuria-car-wash-33703';

    public function sendToToken($deviceToken, $title, $body)
    {
        Log::info("🔔 جاري إرسال إشعار إلى التوكن:", ['token' => $deviceToken]);

        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            Log::error("❌ فشل في الحصول على access token من Firebase");
            return ['error' => 'Unable to get access token'];
        }

        $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

       $response = Http::withToken($accessToken)
    ->post($url, [
        'message' => [
            'token' => $deviceToken,
            'notification' => [
                'title' => '🚘 New Wash Request',
                'body' => 'You have received a new car wash request. Check the app for details.',
            ],
            'android' => [
                'priority' => 'high',
                'notification' => [
                    'sound' => 'default', // أو اسم صوت مخصص لو هتستخدمه
                    'channel_id' => 'high_importance_channel',
                    'color' => '#000000',
                    'icon' => 'ic_launcher', // الأيقونة الأساسية
                ],
            ],
        ]
    ]);


        if ($response->successful()) {
            Log::info("✅ تم إرسال الإشعار بنجاح:", $response->json());
        } else {
            Log::error("❌ فشل إرسال الإشعار:", [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        }

        return $response->json();
    }

    protected function getAccessToken()
    {
        $credentialsPath = storage_path('app/firebase/service-account.json');

        if (!file_exists($credentialsPath)) {
            Log::error("❌ ملف service-account.json غير موجود في: " . $credentialsPath);
            return null;
        }

        $json = json_decode(file_get_contents($credentialsPath));
        $oauth = new OAuth2([
            'audience' => 'https://oauth2.googleapis.com/token',
            'issuer' => $json->client_email,
            'signingAlgorithm' => 'RS256',
            'signingKey' => $json->private_key,
            'tokenCredentialUri' => 'https://oauth2.googleapis.com/token',
            'scope' => ['https://www.googleapis.com/auth/firebase.messaging'],
        ]);

        $token = $oauth->fetchAuthToken();

        if (!isset($token['access_token'])) {
            Log::error("❌ فشل في إنشاء التوكن من Google OAuth:", $token);
            return null;
        }

        return $token['access_token'];
    }
}
