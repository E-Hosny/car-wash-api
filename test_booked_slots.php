<?php
// Test script for booked time slots API
require_once 'vendor/autoload.php';

// Test the API endpoint directly
$url = 'http://localhost/car-wash-api/public/api/orders/booked-time-slots';

// You'll need to replace this with a valid token
$token = 'your-test-token-here';

$headers = [
    'Authorization: Bearer ' . $token,
    'Content-Type: application/json',
    'Accept: application/json'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_VERBOSE, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: $response\n";

// Also test if the route exists
echo "\n--- Testing Route Registration ---\n";
$routes = file_get_contents('routes/api.php');
if (strpos($routes, 'booked-time-slots') !== false) {
    echo "✅ Route 'booked-time-slots' found in routes/api.php\n";
} else {
    echo "❌ Route 'booked-time-slots' NOT found in routes/api.php\n";
}
?>
