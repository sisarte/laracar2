<?php

namespace App\Http\Controllers\Vehicle;

use App\Enums\FuelType;
use App\Enums\Transmission;
use App\Enums\VehicleStatus;
use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IndexVehicleController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $vehicles = Vehicle::query()
            ->with(['modelVersion.vehicleModel.make'])
            ->when($request->search, function ($query, $search) {
                $query->whereHas('modelVersion.vehicleModel.make', function ($q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%");
                })->orWhereHas('modelVersion.vehicleModel', function ($q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%");
                })->orWhereHas('modelVersion', function ($q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%");
                })->orWhere('color', 'ilike', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->fuel_type, function ($query, $fuelType) {
                $query->where('fuel_type', $fuelType);
            })
            ->when($request->transmission, function ($query, $transmission) {
                $query->where('transmission', $transmission);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('vehicles/Index', [
            'vehicles' => $vehicles,
            'filters' => $request->only(['search', 'status', 'fuel_type', 'transmission']),
            'fuelTypes' => FuelType::cases(),
            'transmissions' => Transmission::cases(),
            'statuses' => VehicleStatus::cases(),
        ]);
    }
}
