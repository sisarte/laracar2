<?php

use App\Models\Make;
use App\Models\ModelVersion;
use App\Models\VehicleModel;

test('vehicle model belongs to make', function () {
    $make = Make::factory()->create();
    $vehicleModel = VehicleModel::factory()->create(['make_id' => $make->id]);

    expect($vehicleModel->make)->toBeInstanceOf(Make::class);
    expect($vehicleModel->make->id)->toBe($make->id);
});

test('vehicle model has model versions relationship', function () {
    $vehicleModel = VehicleModel::factory()->create();
    $versions = ModelVersion::factory()->count(3)->create(['vehicle_model_id' => $vehicleModel->id]);

    expect($vehicleModel->modelVersions)->toHaveCount(3);
    expect($vehicleModel->modelVersions->first())->toBeInstanceOf(ModelVersion::class);
});

test('vehicle model can be created with factory', function () {
    $make = Make::factory()->create();
    $vehicleModel = VehicleModel::factory()->create([
        'make_id' => $make->id,
        'name' => 'Civic',
    ]);

    expect($vehicleModel->name)->toBe('Civic');
    expect($vehicleModel->make_id)->toBe($make->id);
});

test('vehicle model name is unique per make', function () {
    $make = Make::factory()->create();
    VehicleModel::factory()->create(['make_id' => $make->id, 'name' => 'Civic']);

    expect(fn () => VehicleModel::factory()->create(['make_id' => $make->id, 'name' => 'Civic']))
        ->toThrow(Illuminate\Database\QueryException::class);
});

test('same vehicle model name can exist for different makes', function () {
    $honda = Make::factory()->create(['name' => 'Honda']);
    $toyota = Make::factory()->create(['name' => 'Toyota']);

    $hondaCivic = VehicleModel::factory()->create(['make_id' => $honda->id, 'name' => 'Civic']);
    $toyotaCivic = VehicleModel::factory()->create(['make_id' => $toyota->id, 'name' => 'Civic']);

    expect($hondaCivic->id)->not->toBe($toyotaCivic->id);
    expect(VehicleModel::where('name', 'Civic')->count())->toBe(2);
});
