<?php

namespace App\Http\Controllers\Vehicle;

use App\Enums\FuelType;
use App\Enums\Transmission;
use App\Enums\VehicleStatus;
use App\Http\Controllers\Controller;
use App\Models\Make;
use Inertia\Inertia;
use Inertia\Response;

class CreateVehicleController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('vehicles/Create', [
            'makes' => Make::query()->orderBy('name')->get(['id', 'name']),
            'fuelTypes' => FuelType::cases(),
            'transmissions' => Transmission::cases(),
            'statuses' => VehicleStatus::cases(),
        ]);
    }
}
