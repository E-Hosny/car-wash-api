<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // WhatsApp (Meta) configuration — الطلبات والإشعارات
    'whatsapp' => [
        'token' => env('WHATSAPP_TOKEN'),
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
        'template_name' => env('WHATSAPP_TEMPLATE_NAME', 'carwash_order'),
        'template_lang' => env('WHATSAPP_TEMPLATE_LANG', 'en'),
        // Comma-separated E.164 numbers to notify, e.g. "9715XXXXXXX,9665XXXXXXX"
        'notify_recipients' => env('WHATSAPP_NOTIFY_RECIPIENTS', ''),
    ],

    // WhatsApp OTP (رقم مستقل لرسائل OTP تسجيل الدخول فقط)
    'whatsapp_otp' => [
        'token' => env('WHATSAPP_OTP_TOKEN'),
        'phone_number_id' => env('WHATSAPP_OTP_PHONE_NUMBER_ID'),
        'template_name' => env('WHATSAPP_OTP_TEMPLATE_NAME', 'otp_luxuria_wash'),
        'template_lang' => env('WHATSAPP_OTP_TEMPLATE_LANG', 'en_US'),
    ],

    'onesignal' => [
        'app_id' => env('ONESIGNAL_APP_ID'),
        'rest_api_key' => env('ONESIGNAL_REST_API_KEY'),
        'android_channel_id' => env('ONESIGNAL_ANDROID_CHANNEL_ID'),
    ],

];
