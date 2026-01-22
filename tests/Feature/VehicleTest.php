<?php

use App\Enums\FuelType;
use App\Enums\Transmission;
use App\Enums\VehicleStatus;
use App\Models\ModelVersion;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('vehicle can be created with factory', function () {
    $vehicle = Vehicle::factory()->create();

    expect($vehicle)->toBeInstanceOf(Vehicle::class)
        ->and($vehicle->id)->toBeInt()
        ->and($vehicle->status)->toBe(VehicleStatus::Active);
});

test('vehicle has correct casts for enums', function () {
    $vehicle = Vehicle::factory()->create([
        'fuel_type' => FuelType::Flex,
        'transmission' => Transmission::Automatic,
        'status' => VehicleStatus::Active,
    ]);

    expect($vehicle->fuel_type)->toBe(FuelType::Flex)
        ->and($vehicle->transmission)->toBe(Transmission::Automatic)
        ->and($vehicle->status)->toBe(VehicleStatus::Active);
});

test('vehicle belongs to user', function () {
    $user = User::factory()->create();
    $vehicle = Vehicle::factory()->create(['user_id' => $user->id]);

    expect($vehicle->user)->toBeInstanceOf(User::class)
        ->and($vehicle->user->id)->toBe($user->id);
});

test('vehicle belongs to model version', function () {
    $modelVersion = ModelVersion::factory()->create();
    $vehicle = Vehicle::factory()->create(['model_version_id' => $modelVersion->id]);

    expect($vehicle->modelVersion)->toBeInstanceOf(ModelVersion::class)
        ->and($vehicle->modelVersion->id)->toBe($modelVersion->id);
});

test('user has many vehicles', function () {
    $user = User::factory()->create();
    Vehicle::factory()->count(3)->create(['user_id' => $user->id]);

    expect($user->vehicles)->toHaveCount(3);
});

test('model version has many vehicles', function () {
    $modelVersion = ModelVersion::factory()->create();
    Vehicle::factory()->count(3)->create(['model_version_id' => $modelVersion->id]);

    expect($modelVersion->vehicles)->toHaveCount(3);
});

test('vehicle factory has sold state', function () {
    $vehicle = Vehicle::factory()->sold()->create();

    expect($vehicle->status)->toBe(VehicleStatus::Sold);
});

test('vehicle factory has paused state', function () {
    $vehicle = Vehicle::factory()->paused()->create();

    expect($vehicle->status)->toBe(VehicleStatus::Paused);
});

test('vehicle price is cast to float', function () {
    $vehicle = Vehicle::factory()->create(['price' => 150000.50]);

    expect($vehicle->price)->toBeFloat()
        ->and($vehicle->price)->toBe(150000.50);
});

test('vehicle implements has media interface', function () {
    $vehicle = Vehicle::factory()->create();

    expect($vehicle)->toBeInstanceOf(\Spatie\MediaLibrary\HasMedia::class);
});
