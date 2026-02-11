<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    private function normalizePhone($phone) {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strpos($phone, '00') === 0) $phone = substr($phone, 2);
        if (strpos($phone, '966') === 0) return $phone;
        if (strpos($phone, '5') === 0) return '966' . $phone;
        if (strpos($phone, '05') === 0) return '966' . substr($phone, 1);
        if (strpos($phone, '971') === 0) return $phone;
        if (strpos($phone, '0') === 0) return '971' . substr($phone, 1);
        return $phone;
    }

    /**
     * رسائل التحقق للتسجيل حسب اللغة (واضحة للمستخدم)
     */
    private function getRegisterValidationMessages(Request $request): array
    {
        $lang = $request->header('Accept-Language', 'en');
        $isAr = (str_starts_with($lang, 'ar') || $lang === 'ar');

        if ($isAr) {
            return [
                'name.required' => 'الاسم مطلوب.',
                'phone.required' => 'رقم الهاتف مطلوب.',
                'phone.unique' => 'رقم الهاتف مسجّل مسبقاً. استخدم رقماً آخر أو سجّل دخولك.',
                'email.required' => 'البريد الإلكتروني مطلوب.',
                'email.email' => 'أدخل بريداً إلكترونياً صحيحاً.',
                'email.unique' => 'البريد الإلكتروني مسجّل مسبقاً. استخدم بريداً آخر أو سجّل دخولك.',
                'password.required' => 'كلمة المرور مطلوبة.',
                'password.confirmed' => 'تأكيد كلمة المرور غير مطابق. تأكد من كتابة نفس كلمة المرور مرتين.',
                'password.min' => 'كلمة المرور يجب أن تكون 6 أحرف على الأقل.',
                'role.required' => 'نوع الحساب مطلوب (عميل أو مقدم خدمة).',
                'role.in' => 'نوع الحساب غير صحيح. اختر: عميل (customer) أو مقدم خدمة (provider).',
            ];
        }

        return [
            'name.required' => 'Name is required.',
            'phone.required' => 'Phone number is required.',
            'phone.unique' => 'This phone number is already registered. Use another number or log in.',
            'email.required' => 'Email is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already registered. Use another email or log in.',
            'password.required' => 'Password is required.',
            'password.confirmed' => 'Password confirmation does not match. Enter the same password in both fields.',
            'password.min' => 'Password must be at least 6 characters.',
            'role.required' => 'Account type is required (customer or provider).',
            'role.in' => 'Invalid account type. Choose: customer or provider.',
        ];
    }

    // ✅ تسجيل مستخدم جديد
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'phone' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:6',
            'role' => 'required|in:customer,provider',
        ], $this->getRegisterValidationMessages($request));

        $normalizedPhone = $this->normalizePhone($request->phone);

        $user = User::create([
            'name' => $request->name,
            'phone' => $normalizedPhone,
            'email' => $request->email,
            'role' => $request->role,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    // ✅ تسجيل دخول
    public function login(Request $request)
    {
        $request->validate([
            'password' => 'required',
            'phone' => 'required_without:email',
            'email' => 'required_without:phone|email',
        ]);

        if ($request->filled('email')) {
            $user = User::where('email', $request->email)->first();
        } else {
            $normalizedPhone = $this->normalizePhone($request->phone);
            $user = User::where('phone', $normalizedPhone)->first();
        }

        if (! $user) {
            throw ValidationException::withMessages([
                $request->filled('email') ? 'email' : 'phone' => ['User not found.'],
            ]);
        }
        
        if (! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['Incorrect password.'],
            ]);
        }
        
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    // ✅ تسجيل الخروج
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'تم تسجيل الخروج بنجاح.']);
    }

    // ✅ التحقق من وجود رقم الهاتف
    public function checkPhone(Request $request)
    {
        $request->validate([
            'phone' => 'required',
        ]);

        $normalizedPhone = $this->normalizePhone($request->phone);
        $user = User::where('phone', $normalizedPhone)->first();

        return response()->json([
            'exists' => $user ? true : false,
            'message' => $user ? 'User found' : 'Phone number not registered',
        ]);
    }

    // ✅ تسجيل الدخول باستخدام OTP
    public function loginWithOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required',
        ]);

        $normalizedPhone = $this->normalizePhone($request->phone);
        $user = User::where('phone', $normalizedPhone)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'phone' => ['Phone number not found.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }
}
