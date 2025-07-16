<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class QrCodeController extends Controller
{
    /**
     * توجيه QR code إلى رابط Google Play
     */
    public function qr1()
    {
        // التوجيه التلقائي إلى رابط Google Play
        return redirect()->away('https://play.google.com/store/apps/details?id=com.washluxuria.carwash');
    }
} 