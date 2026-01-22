<?php

namespace App\Http\Controllers\Vehicle;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Inertia\Inertia;
use Inertia\Response;

class ShowVehicleController extends Controller
{
    public function __invoke(Vehicle $vehicle): Response
    {
        $vehicle->load(['modelVersion.vehicleModel.make', 'media']);

        return Inertia::render('vehicles/Show', [
            'vehicle' => $vehicle,
        ]);
    }
}
