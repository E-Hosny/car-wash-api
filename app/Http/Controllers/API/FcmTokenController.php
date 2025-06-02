<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FcmToken;

class FcmTokenController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        // نحذف التوكن القديم (لو موجود) عشان مانخزنش تكرارات
        FcmToken::updateOrCreate(
            ['user_id' => auth()->id()],
            ['token' => $request->token]
        );

        return response()->json(['message' => 'تم حفظ التوكن بنجاح']);
    }
}
