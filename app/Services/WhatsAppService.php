<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private string $token;
    private string $phoneNumberId;
    private string $templateName;
    private string $templateLang;

    public function __construct()
    {
        $this->token = (string) config('services.whatsapp.token');
        $this->phoneNumberId = (string) config('services.whatsapp.phone_number_id');
        $this->templateName = (string) config('services.whatsapp.template_name', 'carwash_order');
        $this->templateLang = (string) config('services.whatsapp.template_lang', 'en');
    }

    /**
     * إرسال OTP عبر رقم واتساب المستقل (قالب otp_luxuria_wash).
     * يستخدم إعدادات whatsapp_otp فقط — لا يستخدم الرقم القديم.
     */
    public function sendOtp(string $toE164, string $otp): array
    {
        $token = (string) config('services.whatsapp_otp.token');
        $phoneNumberId = (string) config('services.whatsapp_otp.phone_number_id');
        $templateName = (string) config('services.whatsapp_otp.template_name', 'otp_luxuria_wash');
        $templateLang = (string) config('services.whatsapp_otp.template_lang', 'en_US');

        if (empty($token) || empty($phoneNumberId)) {
            Log::warning('WhatsApp OTP: missing credentials. Skipping send.');
            return [];
        }

        $url = "https://graph.facebook.com/v22.0/{$phoneNumberId}/messages";
        $to = preg_replace('/\D/', '', $toE164);

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => ['code' => $templateLang],
                'components' => [
                    [
                        'type' => 'body',
                        'parameters' => [
                            ['type' => 'text', 'text' => $otp],
                        ],
                    ],
                    [
                        'type' => 'button',
                        'sub_type' => 'url',
                        'index' => '0',
                        'parameters' => [
                            ['type' => 'text', 'text' => $otp],
                        ],
                    ],
                ],
            ],
        ];

        $response = Http::withToken($token)
            ->acceptJson()
            ->post($url, $payload);

        if (! $response->successful()) {
            Log::error('WhatsApp OTP send failed', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
        }

        return $response->json() ?? [];
    }

    /**
     * Send a WhatsApp template message to a single E.164 number.
     * $components follows Meta's API format. Leave empty for templates without params.
     */
    public function sendTemplate(string $toE164, array $components = []): array
    {
        if (empty($this->token) || empty($this->phoneNumberId)) {
            Log::warning('WhatsAppService missing credentials. Skipping send.');
            return [];
        }

        $url = "https://graph.facebook.com/v22.0/{$this->phoneNumberId}/messages";

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $toE164,
            'type' => 'template',
            'template' => [
                'name' => $this->templateName,
                'language' => ['code' => $this->templateLang],
            ],
        ];

        if (!empty($components)) {
            $payload['template']['components'] = $components;
        }

        $response = Http::withToken($this->token)
            ->acceptJson()
            ->post($url, $payload);

        if (!$response->successful()) {
            Log::error('WhatsApp template send failed', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
        }

        return $response->json() ?? [];
    }

    /**
     * Send to multiple recipients.
     */
    public function sendTemplateToMany(array $recipientsE164, array $components = []): void
    {
        foreach ($recipientsE164 as $to) {
            $to = trim((string) $to);
            if ($to === '') {
                continue;
            }
            $this->sendTemplate($to, $components);
        }
    }
}


