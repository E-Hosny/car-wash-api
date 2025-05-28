<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CarYear;

class CarYearController extends Controller
{
    public function index()
    {
        return CarYear::all();
    }
}
