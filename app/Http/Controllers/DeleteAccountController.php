<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Car;
use App\Models\Order;
use App\Models\Address;

class DeleteAccountController extends Controller
{
    /**
     * عرض صفحة طلب حذف الحساب
     */
    public function show()
    {
        return view('delete-account');
    }

    /**
     * معالجة طلب حذف الحساب
     */
    public function requestDeletion(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'reason' => 'nullable|string|max:500',
            'confirmation' => 'required|accepted',
        ], [
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'يرجى إدخال بريد إلكتروني صحيح',
            'confirmation.required' => 'يجب الموافقة على حذف البيانات',
            'confirmation.accepted' => 'يجب الموافقة على حذف البيانات',
        ]);

        $email = $request->email;
        $reason = $request->reason;

        // البحث عن المستخدم
        $user = User::where('email', $email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'لم يتم العثور على حساب بهذا البريد الإلكتروني']);
        }

        // إرسال بريد إلكتروني لتأكيد الحذف
        try {
            $this->sendDeletionConfirmationEmail($user, $reason);
            
            Log::info('Delete account request received', [
                'email' => $email,
                'user_id' => $user->id,
                'reason' => $reason
            ]);

            return back()->with('success', 'تم إرسال رابط تأكيد الحذف إلى بريدك الإلكتروني. يرجى التحقق من صندوق الوارد الخاص بك.');
        } catch (\Exception $e) {
            Log::error('Failed to send deletion confirmation email', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);

            return back()->withErrors(['error' => 'حدث خطأ أثناء إرسال البريد الإلكتروني. يرجى المحاولة مرة أخرى.']);
        }
    }

    /**
     * تأكيد حذف الحساب
     */
    public function confirmDeletion($token)
    {
        // في التطبيق الحقيقي، يجب إنشاء نظام tokens آمن
        // هذا مثال مبسط للتوضيح
        $user = User::where('email', base64_decode($token))->first();

        if (!$user) {
            return redirect('/delete-account')->withErrors(['error' => 'رابط غير صحيح أو منتهي الصلاحية']);
        }

        try {
            // حذف البيانات المرتبطة
            $this->deleteUserData($user);

            // حذف المستخدم
            $user->delete();

            Log::info('User account deleted successfully', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return redirect('/delete-account')->with('success', 'تم حذف حسابك وبياناتك بنجاح');
        } catch (\Exception $e) {
            Log::error('Failed to delete user account', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return redirect('/delete-account')->withErrors(['error' => 'حدث خطأ أثناء حذف الحساب. يرجى المحاولة مرة أخرى.']);
        }
    }

    /**
     * حذف بيانات المستخدم
     */
    private function deleteUserData($user)
    {
        // حذف السيارات
        Car::where('user_id', $user->id)->delete();

        // حذف الطلبات
        Order::where('user_id', $user->id)->delete();

        // حذف العناوين
        Address::where('user_id', $user->id)->delete();

        // حذف أي بيانات أخرى مرتبطة بالمستخدم
        // يمكن إضافة المزيد حسب احتياجات التطبيق
    }

    /**
     * إرسال بريد إلكتروني لتأكيد الحذف
     */
    private function sendDeletionConfirmationEmail($user, $reason)
    {
        $token = base64_encode($user->email);
        $confirmationUrl = url("/delete-account/confirm/{$token}");

        $data = [
            'user' => $user,
            'confirmationUrl' => $confirmationUrl,
            'reason' => $reason
        ];

        // في التطبيق الحقيقي، يجب إنشاء template للبريد الإلكتروني
        // هذا مثال مبسط
        Mail::send('emails.delete-account-confirmation', $data, function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('تأكيد حذف الحساب - Car Wash App');
        });
    }
}
