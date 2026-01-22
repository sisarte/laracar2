<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VehicleModel;
use Illuminate\Http\JsonResponse;

class ModelVersionController extends Controller
{
    public function index(VehicleModel $vehicleModel): JsonResponse
    {
        $modelVersions = $vehicleModel->modelVersions()
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($modelVersions);
    }
}
