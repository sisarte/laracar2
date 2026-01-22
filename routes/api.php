<?php

use App\Http\Controllers\Api\ModelVersionController;
use App\Http\Controllers\Api\VehicleModelController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('makes/{make}/vehicle-models', [VehicleModelController::class, 'index']);
    Route::get('vehicle-models/{vehicleModel}/model-versions', [ModelVersionController::class, 'index']);
});
