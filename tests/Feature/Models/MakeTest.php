<?php

use App\Models\Make;
use App\Models\VehicleModel;

test('make has vehicle models relationship', function () {
    $make = Make::factory()->create();
    $vehicleModels = VehicleModel::factory()->count(3)->create(['make_id' => $make->id]);

    expect($make->vehicleModels)->toHaveCount(3);
    expect($make->vehicleModels->first())->toBeInstanceOf(VehicleModel::class);
});

test('make can be created with factory', function () {
    $make = Make::factory()->create([
        'fipe_code' => '59',
        'name' => 'Honda',
    ]);

    expect($make->fipe_code)->toBe('59');
    expect($make->name)->toBe('Honda');
});

test('make fipe_code is unique', function () {
    Make::factory()->create(['fipe_code' => '59']);

    expect(fn () => Make::factory()->create(['fipe_code' => '59']))
        ->toThrow(Illuminate\Database\QueryException::class);
});
