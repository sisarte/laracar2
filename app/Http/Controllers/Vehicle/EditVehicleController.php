<?php

namespace App\Http\Controllers\Vehicle;

use App\Enums\FuelType;
use App\Enums\Transmission;
use App\Enums\VehicleStatus;
use App\Http\Controllers\Controller;
use App\Models\Make;
use App\Models\Vehicle;
use Inertia\Inertia;
use Inertia\Response;

class EditVehicleController extends Controller
{
    public function __invoke(Vehicle $vehicle): Response
    {
        $vehicle->load(['modelVersion.vehicleModel.make', 'media']);

        return Inertia::render('vehicles/Edit', [
            'vehicle' => $vehicle,
            'makes' => Make::query()->orderBy('name')->get(['id', 'name']),
            'fuelTypes' => FuelType::cases(),
            'transmissions' => Transmission::cases(),
            'statuses' => VehicleStatus::cases(),
        ]);
    }
}
