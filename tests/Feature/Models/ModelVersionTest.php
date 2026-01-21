<?php

use App\Models\ModelVersion;
use App\Models\VehicleModel;

test('model version belongs to vehicle model', function () {
    $vehicleModel = VehicleModel::factory()->create();
    $version = ModelVersion::factory()->create(['vehicle_model_id' => $vehicleModel->id]);

    expect($version->vehicleModel)->toBeInstanceOf(VehicleModel::class);
    expect($version->vehicleModel->id)->toBe($vehicleModel->id);
});

test('model version can be created with factory', function () {
    $vehicleModel = VehicleModel::factory()->create();
    $version = ModelVersion::factory()->create([
        'vehicle_model_id' => $vehicleModel->id,
        'fipe_code' => '2024-1',
        'name' => 'LX 1.8 16V 2024 Gasolina',
    ]);

    expect($version->fipe_code)->toBe('2024-1');
    expect($version->name)->toBe('LX 1.8 16V 2024 Gasolina');
    expect($version->vehicle_model_id)->toBe($vehicleModel->id);
});

test('model version fipe_code is unique per vehicle model', function () {
    $vehicleModel = VehicleModel::factory()->create();
    ModelVersion::factory()->create(['vehicle_model_id' => $vehicleModel->id, 'fipe_code' => '2024-1']);

    expect(fn () => ModelVersion::factory()->create(['vehicle_model_id' => $vehicleModel->id, 'fipe_code' => '2024-1']))
        ->toThrow(Illuminate\Database\QueryException::class);
});

test('same fipe_code can exist for different vehicle models', function () {
    $civic = VehicleModel::factory()->create(['name' => 'Civic']);
    $fit = VehicleModel::factory()->create(['name' => 'Fit']);

    $civicVersion = ModelVersion::factory()->create(['vehicle_model_id' => $civic->id, 'fipe_code' => '2024-1']);
    $fitVersion = ModelVersion::factory()->create(['vehicle_model_id' => $fit->id, 'fipe_code' => '2024-1']);

    expect($civicVersion->id)->not->toBe($fitVersion->id);
    expect(ModelVersion::where('fipe_code', '2024-1')->count())->toBe(2);
});
