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


