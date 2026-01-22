<?php

namespace App\Http\Controllers\Vehicle;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;

class DestroyVehicleController extends Controller
{
    public function __invoke(Vehicle $vehicle): RedirectResponse
    {
        $vehicle->delete();

        return to_route('vehicles.index');
    }
}
