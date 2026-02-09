<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\Setting;

class SupportController extends Controller
{
    /**
     * Display the support page
     */
    public function index()
    {
        return view('support');
    }

    /**
     * Handle support form submission
     */
    public function submitTicket(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'priority' => 'required|in:low,medium,high,urgent'
        ]);

        // Here you can implement ticket creation logic
        // For now, we'll just send an email notification

        try {
            // Send email to support team
            $supportData = [
                'name' => $request->name,
                'email' => $request->email,
                'subject' => $request->subject,
                'message' => $request->message,
                'priority' => $request->priority,
                'submitted_at' => now(),
                'user_agent' => $request->header('User-Agent'),
                'ip_address' => $request->ip()
            ];

            // You can uncomment this when you have mail configured
            // Mail::send('emails.support-ticket', $supportData, function ($mail) use ($supportData) {
            //     $mail->to('support@washluxuria.com')
            //          ->subject('New Support Ticket: ' . $supportData['subject']);
            // });

            // Log the support request for now
            \Log::info('Support ticket submitted', $supportData);

            return redirect()->route('support')
                ->with('success', 'Your support request has been submitted successfully. We will get back to you within 24 hours.');

        } catch (\Exception $e) {
            \Log::error('Support ticket submission failed', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return redirect()->route('support')
                ->with('error', 'There was an error submitting your request. Please try again or contact us directly.');
        }
    }

    /**
     * API endpoint to get FAQ data
     */
    public function getFAQ()
    {
        $faqs = [
            [
                'id' => 1,
                'question' => 'How do I book a car wash service?',
                'question_ar' => 'كيف أحجز خدمة غسيل السيارة؟',
                'answer' => 'Simply open the app, select your desired service package, choose your location, and schedule a convenient time. Our team will arrive at your specified location.',
                'answer_ar' => 'افتح التطبيق، اختر الباقة المناسبة، حدد موقعك، وحدد الوقت المناسب. سيصل فريقنا إلى الموقع المحدد.',
                'category' => 'booking'
            ],
            [
                'id' => 2,
                'question' => 'What payment methods do you accept?',
                'question_ar' => 'ما هي طرق الدفع المقبولة؟',
                'answer' => 'We accept all major credit cards, debit cards, and digital payment methods through our secure payment gateway.',
                'answer_ar' => 'نقبل جميع بطاقات الائتمان والخصم الرئيسية وطرق الدفع الرقمية عبر بوابة الدفع الآمنة.',
                'category' => 'payment'
            ],
            [
                'id' => 3,
                'question' => 'Can I reschedule or cancel my appointment?',
                'question_ar' => 'هل يمكنني إعادة جدولة أو إلغاء الموعد؟',
                'answer' => 'Yes, you can reschedule or cancel your appointment up to 2 hours before the scheduled time through the app or by contacting our support team.',
                'answer_ar' => 'نعم، يمكنك إعادة الجدولة أو الإلغاء حتى ساعتين قبل الموعد عبر التطبيق أو بالاتصال بفريق الدعم.',
                'category' => 'booking'
            ],
            [
                'id' => 4,
                'question' => 'What if I\'m not satisfied with the service?',
                'question_ar' => 'ماذا لو لم أكن راضياً عن الخدمة؟',
                'answer' => 'Your satisfaction is our priority. If you\'re not completely satisfied with our service, please contact us within 24 hours and we\'ll make it right or provide a full refund.',
                'answer_ar' => 'رضاك أولويتنا. إن لم تكن راضياً عن الخدمة، تواصل معنا خلال 24 ساعة وسنصلح الأمر أو نعيد المبلغ بالكامل.',
                'category' => 'service'
            ],
            [
                'id' => 5,
                'question' => 'Do you provide services in all weather conditions?',
                'question_ar' => 'هل تقدمون الخدمة في جميع الأحوال الجوية؟',
                'answer' => 'We provide services in most weather conditions. However, for safety reasons, services may be postponed during severe weather. We\'ll notify you in advance and help reschedule.',
                'answer_ar' => 'نقدم الخدمة في معظم الأحوال الجوية. لأسباب أمنية قد يتم تأجيل الخدمة في الطقس الشديد، وسنخبرك مسبقاً ونساعدك في إعادة الجدولة.',
                'category' => 'service'
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $faqs
        ]);
    }

    /**
     * Get support contact information
     */
    public function getContactInfo()
    {
        $whatsappNumber = Setting::getValue('support_whatsapp', '966542327025');
        // Remove any trailing + and ensure it starts with +
        $whatsappNumber = preg_replace('/\+$/', '', trim($whatsappNumber));
        $whatsappNumber = preg_replace('/^\+/', '', $whatsappNumber); // remove leading + for normalization
        $whatsappNumber = '+' . preg_replace('/\D/', '', $whatsappNumber); // keep digits only, add + at start
        
        $contactInfo = [
            'phone' => $whatsappNumber,
            'email' => 'info@washluxuria.com',
            'support_email' => 'info@washluxuria.com',
            'billing_email' => 'billing@washluxuria.com',
            'emergency_phone' => $whatsappNumber,
            'whatsapp' => $whatsappNumber,
            'address' => 'Dubai, United Arab Emirates',
            'support_hours' => [
                'customer_support' => '8:00 AM - 10:00 PM (Daily)',
                'emergency_support' => 'Available 24/7',
                'technical_support' => '9:00 AM - 6:00 PM (Sunday - Thursday)',
                'billing_support' => '9:00 AM - 5:00 PM (Sunday - Thursday)'
            ],
            'service_areas' => [
                'Dubai Marina',
                'Downtown Dubai',
                'Business Bay',
                'Jumeirah Lakes Towers (JLT)',
                'Dubai Internet City',
                'Dubai Media City'
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $contactInfo
        ]);
    }
} 