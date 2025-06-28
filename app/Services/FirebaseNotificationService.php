<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FirebaseNotificationService
{
    protected $projectId;

    public function __construct()
    {
        $this->projectId = env('FCM_PROJECT_ID', 'carwash-24656');
    }

    public function sendToToken($token, $title, $body)
    {
        $message = [
            "message" => [
                "token" => $token,
                "notification" => [
                    "title" => $title,
                    "body" => $body,
                ],
                "android" => ["notification" => ["sound" => "default"]],
                "apns" => ["payload" => ["aps" => ["sound" => "default"]]],
            ]
        ];

        $accessToken = $this->getAccessToken();

        return Http::withToken($accessToken)
            ->post("https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send", $message)
            ->json();
    }

    private function getAccessToken()
    {
        $client = new \Google_Client();
        
        // Use service account credentials from environment variables
        $credentials = [
            'type' => 'service_account',
            'project_id' => env('FCM_PROJECT_ID'),
            'private_key_id' => env('FIREBASE_PRIVATE_KEY_ID'),
            'private_key' => str_replace('\n', "\n", env('FIREBASE_PRIVATE_KEY')),
            'client_email' => env('FIREBASE_CLIENT_EMAIL'),
            'client_id' => env('FIREBASE_CLIENT_ID'),
            'auth_uri' => 'https://accounts.google.com/o/oauth2/auth',
            'token_uri' => 'https://oauth2.googleapis.com/token',
            'auth_provider_x509_cert_url' => 'https://www.googleapis.com/oauth2/v1/certs',
            'client_x509_cert_url' => env('FIREBASE_CLIENT_X509_CERT_URL'),
        ];
        
        $client->setAuthConfig($credentials);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        
        return $client->fetchAccessTokenWithAssertion()['access_token'];
    }
}
