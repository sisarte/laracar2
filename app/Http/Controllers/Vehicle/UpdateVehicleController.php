<?php

namespace App\Http\Controllers\Vehicle;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vehicle\UpdateVehicleRequest;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;

class UpdateVehicleController extends Controller
{
    public function __invoke(UpdateVehicleRequest $request, Vehicle $vehicle): RedirectResponse
    {
        $vehicle->update($request->validated());

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $vehicle->addMedia($image)->toMediaCollection('images');
            }
        }

        if ($request->has('remove_images')) {
            $vehicle->media()
                ->whereIn('id', $request->input('remove_images'))
                ->each(fn ($media) => $media->delete());
        }

        return to_route('vehicles.show', $vehicle);
    }
}
