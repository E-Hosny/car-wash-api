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

    // ✅ تسجيل مستخدم جديد
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'phone' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:6',
            'role' => 'required|in:customer,provider',
        ]);

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
            'phone' => 'required',
            'password' => 'required',
        ]);

        $normalizedPhone = $this->normalizePhone($request->phone);
        $user = User::where('phone', $normalizedPhone)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'phone' => ['Phone number not found.'],
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
