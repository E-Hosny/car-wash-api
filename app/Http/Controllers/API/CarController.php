<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;

use App\Models\Car;
use Illuminate\Http\Request;

class CarController extends Controller
{
    private const DEFAULT_CAR_TYPE_RULES = [
        ['key' => 'sedan', 'label_en' => 'Sedan', 'label_ar' => 'سيدان', 'percentage' => 0],
        ['key' => '4x4_5', 'label_en' => '4*4 (5 seats)', 'label_ar' => '4*4 (5 مقاعد)', 'percentage' => 15],
        ['key' => '4x4_7', 'label_en' => '4*4 (7 seats)', 'label_ar' => '4*4 (7 مقاعد)', 'percentage' => 20],
        ['key' => 'carnival', 'label_en' => 'Carnival', 'label_ar' => 'كارنفال', 'percentage' => 25],
    ];

    public function index(Request $request)
    {
        $carTypeRules = collect(\App\Models\Setting::getValue('car_type_pricing_rules', self::DEFAULT_CAR_TYPE_RULES))
            ->filter(fn ($item) => is_array($item) && !empty($item['key']))
            ->keyBy(fn ($item) => (string) $item['key']);

        $cars = Car::with(['brand', 'model', 'year'])
            ->where('user_id', $request->user()->id)
            ->get();

        $cars->each(function ($car) use ($carTypeRules) {
            $typeKey = (string) ($car->car_type ?? '');
            $rule = $typeKey !== '' ? $carTypeRules->get($typeKey) : null;
            $car->car_type_percentage = (float) ($rule['percentage'] ?? 0);
            $car->car_type_label_en = $rule['label_en'] ?? $rule['label'] ?? null;
            $car->car_type_label_ar = $rule['label_ar'] ?? $rule['label'] ?? null;
        });

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
            'car_type' => 'required|string|max:50',
        ]);

        $carTypeRules = collect(\App\Models\Setting::getValue('car_type_pricing_rules', self::DEFAULT_CAR_TYPE_RULES))
            ->filter(fn ($item) => is_array($item) && !empty($item['key']))
            ->keyBy(fn ($item) => (string) $item['key']);

        if (!$carTypeRules->has((string) $data['car_type'])) {
            return response()->json([
                'message' => 'Invalid car type selected',
                'errors' => ['car_type' => ['Please select a valid car type']],
            ], 422);
        }

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
            'car_type' => $data['car_type'],
            'color' => $data['color'],
            'license_plate' => $data['license_plate'],
        ]);
        $car->load(['brand', 'model', 'year']);
        $selectedRule = $carTypeRules->get((string) $car->car_type);
        $car->car_type_percentage = (float) ($selectedRule['percentage'] ?? 0);
        $car->car_type_label_en = $selectedRule['label_en'] ?? $selectedRule['label'] ?? null;
        $car->car_type_label_ar = $selectedRule['label_ar'] ?? $selectedRule['label'] ?? null;

        return response()->json($car, 201);
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
