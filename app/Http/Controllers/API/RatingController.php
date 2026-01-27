<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AppRating;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    /**
     * Store a new app rating.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:1000',
            'order_id' => 'required|exists:orders,id',
        ]);

        // Verify that the user owns the order
        $order = Order::findOrFail($request->order_id);
        if ($order->customer_id !== Auth::id()) {
            return response()->json([
                'message' => 'Unauthorized access to this order'
            ], 403);
        }

        // Check if rating already exists for this order
        $existingRating = AppRating::where('user_id', Auth::id())
            ->where('order_id', $request->order_id)
            ->first();

        if ($existingRating) {
            return response()->json([
                'message' => 'You have already rated this order'
            ], 422);
        }

        $rating = AppRating::create([
            'user_id' => Auth::id(),
            'order_id' => $request->order_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'message' => 'Rating submitted successfully',
            'data' => $rating,
        ], 201);
    }

    /**
     * Check if order has been rated by the current user
     *
     * @param int $orderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkOrderRating($orderId)
    {
        // Verify that the user owns the order
        $order = Order::findOrFail($orderId);
        if ($order->customer_id !== Auth::id()) {
            return response()->json([
                'message' => 'Unauthorized access to this order'
            ], 403);
        }

        $rating = AppRating::where('user_id', Auth::id())
            ->where('order_id', $orderId)
            ->first();

        return response()->json([
            'has_rating' => $rating !== null,
            'rating' => $rating ? [
                'id' => $rating->id,
                'rating' => $rating->rating,
                'comment' => $rating->comment,
                'created_at' => $rating->created_at->toDateTimeString(),
            ] : null,
        ]);
    }
}
