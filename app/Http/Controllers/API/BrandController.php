<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Brand;

class BrandController extends Controller
{
    public function index()
    {
        return Brand::all();
    }

    public function models($brandId)
    {
        $brand = Brand::with('models')->findOrFail($brandId);
        return $brand->models;
    }
}
