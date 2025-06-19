<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FirebaseNotificationService
{
    protected $projectId = 'carwash-24656'; // 👈 غيّرها

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
        $client->setAuthConfig(storage_path('firebase/firebase-service-account.json'));
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        return $client->fetchAccessTokenWithAssertion()['access_token'];
    }
}
