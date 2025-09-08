<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;

use App\Models\Car;
use Illuminate\Http\Request;

class CarController extends Controller
{
    public function index(Request $request)
    {
        $cars = Car::with(['brand', 'model', 'year'])
            ->where('user_id', $request->user()->id)
            ->get();
        return response()->json($cars);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'brand_id' => 'nullable|exists:brands,id',
            'model_id' => 'nullable|exists:car_models,id',
            'car_year_id' => 'nullable|exists:car_years,id',
            'custom_brand' => 'nullable|string|max:100',
            'custom_model' => 'nullable|string|max:100',
            'custom_year' => 'nullable|string|max:10',
            'color' => 'required|string|max:50',
            'license_plate' => 'nullable|string|max:20',
        ]);

        // If custom values are provided, create them or use existing ones
        $brandId = $data['brand_id'] ?? null;
        $modelId = $data['model_id'] ?? null;
        $yearId = $data['car_year_id'] ?? null;

        // Handle custom brand first
        if (!empty($data['custom_brand'])) {
            $brand = \App\Models\Brand::firstOrCreate(['name' => trim($data['custom_brand'])]);
            $brandId = $brand->id;
        }

        // Handle custom model (needs brandId to be set first)
        if (!empty($data['custom_model'])) {
            // If no brand is selected and no custom brand, we can't create a model
            if (!$brandId) {
                return response()->json([
                    'message' => 'Brand is required when creating a custom model',
                    'errors' => ['brand' => ['Please select a brand or enter a custom brand first']]
                ], 422);
            }
            
            $model = \App\Models\CarModel::firstOrCreate([
                'name' => trim($data['custom_model']),
                'brand_id' => $brandId
            ]);
            $modelId = $model->id;
        }

        // Handle custom year
        if (!empty($data['custom_year'])) {
            $year = \App\Models\CarYear::firstOrCreate(['year' => trim($data['custom_year'])]);
            $yearId = $year->id;
        }

        // Validate that we have all required IDs
        if (!$brandId) {
            return response()->json([
                'message' => 'Brand is required',
                'errors' => ['brand' => ['Please select a brand or enter a custom brand']]
            ], 422);
        }

        if (!$modelId) {
            return response()->json([
                'message' => 'Model is required',
                'errors' => ['model' => ['Please select a model or enter a custom model']]
            ], 422);
        }

        if (!$yearId) {
            return response()->json([
                'message' => 'Year is required',
                'errors' => ['year' => ['Please select a year or enter a custom year']]
            ], 422);
        }

        $car = Car::create([
            'user_id' => $request->user()->id,
            'brand_id' => $brandId,
            'model_id' => $modelId,
            'car_year_id' => $yearId,
            'color' => $data['color'],
            'license_plate' => $data['license_plate'],
        ]);

        return response()->json($car->load(['brand', 'model', 'year']), 201);
    }

    public function destroy(Car $car)
    {
        if ($car->user_id != auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $car->delete();
        return response()->json(['message' => 'Car deleted']);
    }
}
