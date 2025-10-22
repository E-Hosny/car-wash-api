<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Order;

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
            $request->validate([
                'amount' => 'required|numeric|min:1',
                'currency' => 'required|string|size:3',
                'order_id' => 'required|string',
            ]);

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
                throw new \Exception('Failed to create ephemeral key');
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
            return $user->stripe_customer_id;
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