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
            'brand_id' => 'required|exists:brands,id',
            'model_id' => 'required|exists:models,id',
            'car_year_id' => 'required|exists:car_years,id',
            'color' => 'required|string|max:50',
        ]);

        $car = Car::create([
            'user_id' => $request->user()->id,
            ...$data
        ]);

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
