<?php

use App\Http\Controllers\Vehicle\CreateVehicleController;
use App\Http\Controllers\Vehicle\DestroyVehicleController;
use App\Http\Controllers\Vehicle\EditVehicleController;
use App\Http\Controllers\Vehicle\IndexVehicleController;
use App\Http\Controllers\Vehicle\ShowVehicleController;
use App\Http\Controllers\Vehicle\StoreVehicleController;
use App\Http\Controllers\Vehicle\UpdateVehicleController;
use App\Models\Make;
use App\Models\VehicleModel;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('vehicles', IndexVehicleController::class)->name('vehicles.index');
    Route::get('vehicles/create', CreateVehicleController::class)->name('vehicles.create');
    Route::post('vehicles', StoreVehicleController::class)->name('vehicles.store');

    // Data routes for cascading selects (must come before parameterized routes)
    Route::get('vehicles/data/vehicle-models/{make}', function (Make $make) {
        return $make->vehicleModels()->orderBy('name')->get(['id', 'name']);
    })->name('vehicles.vehicle-models');

    Route::get('vehicles/data/model-versions/{vehicleModel}', function (VehicleModel $vehicleModel) {
        return $vehicleModel->modelVersions()->orderBy('name')->get(['id', 'name']);
    })->name('vehicles.model-versions');

    // Parameterized routes (must come after specific routes)
    Route::get('vehicles/{vehicle}', ShowVehicleController::class)->name('vehicles.show');
    Route::get('vehicles/{vehicle}/edit', EditVehicleController::class)->name('vehicles.edit');
    Route::patch('vehicles/{vehicle}', UpdateVehicleController::class)->name('vehicles.update');
    Route::delete('vehicles/{vehicle}', DestroyVehicleController::class)->name('vehicles.destroy');
});
