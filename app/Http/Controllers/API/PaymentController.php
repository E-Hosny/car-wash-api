<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Services\LocationValidationService;

class PaymentController extends Controller
{
    private $stripeSecretKey;
    private $stripeWebhookSecret;

    public function __construct()
    {
        $this->stripeSecretKey = env('STRIPE_SECRET_KEY');
        $this->stripeWebhookSecret = env('STRIPE_WEBHOOK_SECRET');
    }

    /**
     * إنشاء Payment Intent مع دعم PaymentSheet
     */
    public function createPaymentIntent(Request $request)
    {
        try {
            // Log all incoming request data
            Log::info('Payment Intent Request received', [
                'order_id' => $request->order_id,
                'amount' => $request->amount,
                'currency' => $request->currency,
                'has_latitude' => $request->has('latitude'),
                'has_longitude' => $request->has('longitude'),
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'all_request_data' => $request->all(),
            ]);
            
            // تحديد إذا كان هذا طلب عادي (يحتاج موقع) أم شراء باقة (لا يحتاج موقع)
            // التحقق بطريقتين: من order_id أو من معامل is_package_purchase
            $isPackagePurchase = str_starts_with($request->order_id, 'package_') 
                || ($request->has('is_package_purchase') && $request->is_package_purchase == true);
            
            Log::info('Order type determination', [
                'order_id' => $request->order_id,
                'has_is_package_purchase' => $request->has('is_package_purchase'),
                'is_package_purchase_value' => $request->is_package_purchase ?? null,
                'is_package_purchase' => $isPackagePurchase,
            ]);
            
            $validationRules = [
                'amount' => 'required|numeric|min:1',
                'currency' => 'required|string|size:3',
                'order_id' => 'required|string',
            ];
            
            // جعل الإحداثيات nullable للجميع (للتوافق مع النسخ القديمة)
            $validationRules['latitude'] = 'nullable|numeric';
            $validationRules['longitude'] = 'nullable|numeric';
            
            if (!$isPackagePurchase) {
                Log::info('Regular order - location optional (for backward compatibility)');
            } else {
                Log::info('Package purchase - location optional');
            }
            
            try {
                $request->validate($validationRules, [
                    'latitude.numeric' => 'Invalid location coordinates. Please select a valid location.',
                    'longitude.numeric' => 'Invalid location coordinates. Please select a valid location.',
                ]);
            } catch (\Illuminate\Validation\ValidationException $e) {
                Log::error('Validation failed', [
                    'errors' => $e->errors(),
                    'request_data' => $request->all(),
                ]);
                throw $e;
            }

            // التحقق من الموقع للطلبات العادية فقط (إذا كانت الإحداثيات موجودة)
            if (!$isPackagePurchase) {
                Log::info('Checking location for order', [
                    'order_id' => $request->order_id,
                    'has_latitude' => $request->has('latitude'),
                    'has_longitude' => $request->has('longitude'),
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                ]);
                
                // إذا كانت الإحداثيات موجودة، تحقق منها
                if ($request->has('latitude') && $request->has('longitude') && 
                    $request->latitude !== null && $request->longitude !== null) {
                    
                    $latitude = (float) $request->latitude;
                    $longitude = (float) $request->longitude;
                    
                    Log::info('Location provided - validating bounds', [
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                    ]);
                    
                    $locationValidation = LocationValidationService::validateLocation(
                        $latitude,
                        $longitude
                    );

                    Log::info('Location validation result', [
                        'valid' => $locationValidation['valid'],
                        'message' => $locationValidation['message'] ?? 'N/A',
                    ]);

                    if (!$locationValidation['valid']) {
                        return response()->json([
                            'success' => false,
                            'error' => 'Location out of service area',
                            'message' => $locationValidation['message']
                        ], 400);
                    }
                } else {
                    // إذا لم تكن الإحداثيات موجودة (نسخة قديمة)، قبول الطلب بدون تحقق
                    Log::info('Location not provided - accepting order for backward compatibility', [
                        'order_id' => $request->order_id,
                        'reason' => 'Old app version or missing location data',
                    ]);
                }
            }
            // للباقات، لا نحتاج للتحقق من الموقع

            $user = auth()->user();

            // إنشاء أو الحصول على Stripe Customer
            $customerId = $this->getOrCreateStripeCustomer($user);

            // إنشاء Ephemeral Key
            $ephemeralKeyResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->stripeSecretKey,
                'Stripe-Version' => '2024-10-28.acacia',
            ])->asForm()->post('https://api.stripe.com/v1/ephemeral_keys', [
                'customer' => $customerId,
            ]);

            if (!$ephemeralKeyResponse->successful()) {
                Log::error('Ephemeral Key Error: ' . $ephemeralKeyResponse->body());
                $errorData = $ephemeralKeyResponse->json();
                
                // إذا كان الخطأ بسبب customer غير موجود، أنشئ customer جديد
                if (isset($errorData['error']['code']) && $errorData['error']['code'] === 'resource_missing') {
                    Log::info('Customer not found, creating new customer');
                    $customerId = $this->getOrCreateStripeCustomer($user);
                    
                    // جرب إنشاء ephemeral key مرة أخرى
                    $ephemeralKeyResponse = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $this->stripeSecretKey,
                        'Stripe-Version' => '2024-10-28.acacia',
                    ])->asForm()->post('https://api.stripe.com/v1/ephemeral_keys', [
                        'customer' => $customerId,
                    ]);
                    
                    if (!$ephemeralKeyResponse->successful()) {
                        throw new \Exception('Failed to create ephemeral key after customer recreation');
                    }
                } else {
                    throw new \Exception('Failed to create ephemeral key');
                }
            }

            // إنشاء Payment Intent
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->stripeSecretKey,
            ])->asForm()->post('https://api.stripe.com/v1/payment_intents', [
                'amount' => (int)($request->amount * 100), // تحويل إلى أصغر وحدة عملة
                'currency' => strtolower($request->currency),
                'customer' => $customerId,
                'metadata[order_id]' => $request->order_id,
                'automatic_payment_methods[enabled]' => 'true',
                'automatic_payment_methods[allow_redirects]' => 'never',
            ]);

            if ($response->successful()) {
                $paymentIntentData = $response->json();
                
                return response()->json([
                    'client_secret' => $paymentIntentData['client_secret'],
                    'ephemeral_key' => $ephemeralKeyResponse->json()['secret'],
                    'customer' => $customerId,
                    'payment_intent_id' => $paymentIntentData['id'],
                ]);
            } else {
                Log::error('Stripe API Error: ' . $response->body());
                return response()->json([
                    'error' => 'Failed to create payment intent',
                    'details' => $response->json()
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Payment Intent Creation Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Internal server error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * الحصول على أو إنشاء Stripe Customer
     */
    private function getOrCreateStripeCustomer($user)
    {
        // التحقق من وجود stripe_customer_id في قاعدة البيانات
        if ($user->stripe_customer_id) {
            // التحقق من أن Customer موجود في Live Mode
            $customerResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->stripeSecretKey,
            ])->get("https://api.stripe.com/v1/customers/{$user->stripe_customer_id}");
            
            if ($customerResponse->successful()) {
                return $user->stripe_customer_id;
            } else {
                // Customer غير موجود في Live Mode، احذفه من قاعدة البيانات
                Log::info("Customer {$user->stripe_customer_id} not found in live mode, creating new one");
                $user->stripe_customer_id = null;
                $user->save();
            }
        }

        // إنشاء عميل جديد في Stripe
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->stripeSecretKey,
        ])->asForm()->post('https://api.stripe.com/v1/customers', [
            'email' => $user->email,
            'name' => $user->name,
            'phone' => $user->phone_number ?? '',
            'metadata[user_id]' => $user->id,
        ]);

        if ($response->successful()) {
            $customer = $response->json();
            
            // حفظ customer_id في قاعدة البيانات
            $user->update(['stripe_customer_id' => $customer['id']]);
            
            return $customer['id'];
        }

        throw new \Exception('Failed to create Stripe customer');
    }

    /**
     * تأكيد الدفع
     */
    public function confirmPayment(Request $request)
    {
        try {
            $request->validate([
                'payment_intent_id' => 'required|string',
                'payment_method_id' => 'required|string',
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->stripeSecretKey,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ])->post("https://api.stripe.com/v1/payment_intents/{$request->payment_intent_id}/confirm", [
                'payment_method' => $request->payment_method_id,
            ]);

            if ($response->successful()) {
                $paymentData = $response->json();
                
                if ($paymentData['status'] === 'succeeded') {
                    return response()->json([
                        'success' => true,
                        'payment_intent' => $paymentData
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'error' => 'Payment not succeeded',
                        'status' => $paymentData['status']
                    ], 400);
                }
            } else {
                Log::error('Stripe Confirmation Error: ' . $response->body());
                return response()->json([
                    'error' => 'Failed to confirm payment',
                    'details' => $response->json()
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Payment Confirmation Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Internal server error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * الحصول على حالة الدفع
     */
    public function getPaymentStatus(Request $request)
    {
        try {
            $request->validate([
                'payment_intent_id' => 'required|string',
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->stripeSecretKey,
            ])->get("https://api.stripe.com/v1/payment_intents/{$request->payment_intent_id}");

            if ($response->successful()) {
                return response()->json($response->json());
            } else {
                Log::error('Stripe Status Error: ' . $response->body());
                return response()->json([
                    'error' => 'Failed to get payment status',
                    'details' => $response->json()
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Payment Status Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Internal server error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * تحديث حالة دفع الطلب
     */
    public function updateOrderPaymentStatus(Request $request, $orderId)
    {
        try {
            $request->validate([
                'payment_status' => 'required|string|in:paid,pending,failed',
                'payment_intent_id' => 'required|string',
            ]);

            $order = Order::findOrFail($orderId);

            // التحقق من أن المستخدم يملك الطلب
            if ($order->customer_id !== auth()->id()) {
                return response()->json([
                    'error' => 'Unauthorized access to this order'
                ], 403);
            }

            $order->update([
                'payment_status' => $request->payment_status,
                'payment_intent_id' => $request->payment_intent_id,
                'paid_at' => $request->payment_status === 'paid' ? now() : null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment status updated successfully',
                'order' => $order
            ]);
        } catch (\Exception $e) {
            Log::error('Update Payment Status Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Internal server error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Webhook للتعامل مع أحداث Stripe
     */
    public function handleWebhook(Request $request)
    {
        try {
            $payload = $request->getContent();
            $sigHeader = $request->header('Stripe-Signature');
            $endpointSecret = $this->stripeWebhookSecret;

            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sigHeader,
                $endpointSecret
            );

            switch ($event->type) {
                case 'payment_intent.succeeded':
                    $this->handlePaymentSucceeded($event->data->object);
                    break;
                case 'payment_intent.payment_failed':
                    $this->handlePaymentFailed($event->data->object);
                    break;
                default:
                    Log::info('Unhandled event type: ' . $event->type);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Webhook Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Webhook error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    private function handlePaymentSucceeded($paymentIntent)
    {
        $orderId = $paymentIntent->metadata->order_id ?? null;
        
        if ($orderId) {
            $order = Order::where('payment_intent_id', $paymentIntent->id)->first();
            
            if ($order) {
                $order->update([
                    'payment_status' => 'paid',
                    'paid_at' => now(),
                ]);
                
                Log::info("Order {$order->id} payment succeeded");
            }
        }
    }

    private function handlePaymentFailed($paymentIntent)
    {
        $order = Order::where('payment_intent_id', $paymentIntent->id)->first();
        
        if ($order) {
            $order->update([
                'payment_status' => 'failed',
            ]);
            
            Log::info("Order {$order->id} payment failed");
        }
    }
} 