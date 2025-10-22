<?php
/**
 * اختبار Payment Sheet API
 * هذا الملف لاختبار أن API يرجع البيانات الصحيحة لـ PaymentSheet
 */

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Http;

// قراءة التوكن من ملف test_token.txt
$token = trim(file_get_contents(__DIR__ . '/test_token.txt'));

if (empty($token)) {
    die("❌ Error: No token found in test_token.txt\n");
}

echo "🔐 Using token: " . substr($token, 0, 20) . "...\n\n";

// البيانات للاختبار
$testData = [
    'amount' => 50.00,
    'currency' => 'AED',
    'order_id' => 'TEST_' . time(),
];

echo "📤 Sending request to create payment intent...\n";
echo "Amount: {$testData['amount']} {$testData['currency']}\n";
echo "Order ID: {$testData['order_id']}\n\n";

// إرسال الطلب
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000/api/payments/create-intent');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'Authorization: Bearer ' . $token,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "📥 Response received (HTTP $httpCode)\n";
echo str_repeat('-', 80) . "\n";

if ($httpCode === 200) {
    $data = json_decode($response, true);
    
    echo "✅ SUCCESS! Payment Intent created\n\n";
    
    // التحقق من البيانات المطلوبة
    $requiredFields = ['client_secret', 'ephemeral_key', 'customer', 'payment_intent_id'];
    $allFieldsPresent = true;
    
    foreach ($requiredFields as $field) {
        if (isset($data[$field]) && !empty($data[$field])) {
            $value = $data[$field];
            $displayValue = strlen($value) > 50 ? substr($value, 0, 50) . '...' : $value;
            echo "✅ $field: $displayValue\n";
        } else {
            echo "❌ $field: MISSING\n";
            $allFieldsPresent = false;
        }
    }
    
    echo "\n";
    
    if ($allFieldsPresent) {
        echo "🎉 All required fields are present!\n";
        echo "✅ PaymentSheet integration is ready to use\n\n";
        
        echo "📋 Summary:\n";
        echo "- Client Secret: Ready\n";
        echo "- Ephemeral Key: Ready\n";
        echo "- Customer ID: Ready\n";
        echo "- Payment Intent ID: Ready\n\n";
        
        echo "🚀 You can now test the payment in the Flutter app!\n";
    } else {
        echo "⚠️  Some required fields are missing!\n";
        echo "Please check the PaymentController implementation.\n";
    }
    
} else {
    echo "❌ ERROR! Request failed\n\n";
    echo "Response:\n";
    echo $response . "\n";
}

echo "\n" . str_repeat('-', 80) . "\n";
echo "Test completed at: " . date('Y-m-d H:i:s') . "\n";

