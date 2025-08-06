<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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
                'answer' => 'Simply open the app, select your desired service package, choose your location, and schedule a convenient time. Our team will arrive at your specified location.',
                'category' => 'booking'
            ],
            [
                'id' => 2,
                'question' => 'What payment methods do you accept?',
                'answer' => 'We accept all major credit cards, debit cards, and digital payment methods through our secure payment gateway. You can also purchase service packages in advance.',
                'category' => 'payment'
            ],
            [
                'id' => 3,
                'question' => 'Can I reschedule or cancel my appointment?',
                'answer' => 'Yes, you can reschedule or cancel your appointment up to 2 hours before the scheduled time through the app or by contacting our support team.',
                'category' => 'booking'
            ],
            [
                'id' => 4,
                'question' => 'What if I\'m not satisfied with the service?',
                'answer' => 'Your satisfaction is our priority. If you\'re not completely satisfied with our service, please contact us within 24 hours and we\'ll make it right or provide a full refund.',
                'category' => 'service'
            ],
            [
                'id' => 5,
                'question' => 'Do you provide services in all weather conditions?',
                'answer' => 'We provide services in most weather conditions. However, for safety reasons, services may be postponed during severe weather. We\'ll notify you in advance and help reschedule.',
                'category' => 'service'
            ],
            [
                'id' => 6,
                'question' => 'How do I track my service request?',
                'answer' => 'You can track your service request in real-time through the app. You\'ll receive notifications when our team is on the way and when the service is complete.',
                'category' => 'tracking'
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
        $contactInfo = [
            'phone' => '+971502711549',
            'email' => 'info@washluxuria.com',
            'support_email' => 'support@washluxuria.com',
            'billing_email' => 'billing@washluxuria.com',
            'emergency_phone' => '+971502711549',
            'whatsapp' => '+971502711549',
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