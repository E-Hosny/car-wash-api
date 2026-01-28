<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppRating;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    /**
     * Display a listing of all ratings
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $ratings = AppRating::with([
            'order.customer',
            'order.assignedUser',
            'user'
        ])
        ->whereNotNull('order_id') // فقط التقييمات المرتبطة بطلبات
        ->latest('created_at')
        ->paginate(20);
        
        return view('admin.ratings.index', compact('ratings'));
    }
}
