<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Make;
use Illuminate\Http\JsonResponse;

class VehicleModelController extends Controller
{
    public function index(Make $make): JsonResponse
    {
        $vehicleModels = $make->vehicleModels()
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($vehicleModels);
    }
}
