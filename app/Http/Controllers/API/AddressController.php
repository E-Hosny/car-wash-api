<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Address;

class AddressController extends Controller
{
    // جلب كل عناوين المستخدم
    public function index(Request $request)
    {
        $addresses = Address::where('user_id', $request->user()->id)->get();
        return response()->json($addresses);
    }

    // إضافة عنوان جديد
    public function store(Request $request)
    {
        $data = $request->validate([
            'label' => 'nullable|string|max:100',
            'street' => 'nullable|string|max:100',
            'building' => 'nullable|string|max:100',
            'floor' => 'nullable|string|max:20',
            'apartment' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);
        $data['user_id'] = $request->user()->id;
        $address = Address::create($data);
        return response()->json($address, 201);
    }

    // حذف عنوان
    public function destroy(Address $address)
    {
        if ($address->user_id !== auth()->id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        $address->delete();
        return response()->json(['message' => 'Address deleted']);
    }
} 