<?php

namespace App\Http\Controllers\Vehicle;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vehicle\StoreVehicleRequest;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;

class StoreVehicleController extends Controller
{
    public function __invoke(StoreVehicleRequest $request): RedirectResponse
    {
        $vehicle = Vehicle::create($request->validated());

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $vehicle->addMedia($image)->toMediaCollection('images');
            }
        }

        return to_route('vehicles.show', $vehicle);
    }
}
