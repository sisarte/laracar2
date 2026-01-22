<?php

use App\Enums\FuelType;
use App\Enums\Transmission;
use App\Enums\VehicleStatus;
use App\Models\ModelVersion;
use App\Models\User;
use App\Models\Vehicle;

test('guests are redirected to the login page', function () {
    $vehicle = Vehicle::factory()->create();

    $response = $this->patch(route('vehicles.update', $vehicle));

    $response->assertRedirect(route('login'));
});

test('authenticated users can update a vehicle', function () {
    $user = User::factory()->create();
    $vehicle = Vehicle::factory()->create();
    $newModelVersion = ModelVersion::factory()->create();

    $response = $this->actingAs($user)->patch(route('vehicles.update', $vehicle), [
        'model_version_id' => $newModelVersion->id,
        'year' => 2024,
        'price' => 60000,
        'mileage' => 5000,
        'color' => 'Branco',
        'fuel_type' => FuelType::Electric->value,
        'transmission' => Transmission::Automatic->value,
        'description' => 'Veículo atualizado',
        'status' => VehicleStatus::Sold->value,
    ]);

    $response->assertRedirect(route('vehicles.show', $vehicle));

    $vehicle->refresh();
    expect($vehicle->model_version_id)->toBe($newModelVersion->id);
    expect($vehicle->year)->toBe(2024);
    expect($vehicle->price)->toBe(60000.0);
    expect($vehicle->mileage)->toBe(5000);
    expect($vehicle->color)->toBe('Branco');
    expect($vehicle->fuel_type)->toBe(FuelType::Electric);
    expect($vehicle->transmission)->toBe(Transmission::Automatic);
    expect($vehicle->description)->toBe('Veículo atualizado');
    expect($vehicle->status)->toBe(VehicleStatus::Sold);
});

test('model_version_id is required', function () {
    $user = User::factory()->create();
    $vehicle = Vehicle::factory()->create();

    $response = $this->actingAs($user)->patch(route('vehicles.update', $vehicle), [
        'year' => 2023,
        'price' => 50000,
        'mileage' => 10000,
        'color' => 'Preto',
        'fuel_type' => FuelType::Flex->value,
        'transmission' => Transmission::Automatic->value,
        'status' => VehicleStatus::Active->value,
    ]);

    $response->assertSessionHasErrors('model_version_id');
});

test('model_version_id must exist', function () {
    $user = User::factory()->create();
    $vehicle = Vehicle::factory()->create();

    $response = $this->actingAs($user)->patch(route('vehicles.update', $vehicle), [
        'model_version_id' => 999999,
        'year' => 2023,
        'price' => 50000,
        'mileage' => 10000,
        'color' => 'Preto',
        'fuel_type' => FuelType::Flex->value,
        'transmission' => Transmission::Automatic->value,
        'status' => VehicleStatus::Active->value,
    ]);

    $response->assertSessionHasErrors('model_version_id');
});

test('year is required', function () {
    $user = User::factory()->create();
    $vehicle = Vehicle::factory()->create();

    $response = $this->actingAs($user)->patch(route('vehicles.update', $vehicle), [
        'model_version_id' => $vehicle->model_version_id,
        'price' => 50000,
        'mileage' => 10000,
        'color' => 'Preto',
        'fuel_type' => FuelType::Flex->value,
        'transmission' => Transmission::Automatic->value,
        'status' => VehicleStatus::Active->value,
    ]);

    $response->assertSessionHasErrors('year');
});

test('year must be at least 1900', function () {
    $user = User::factory()->create();
    $vehicle = Vehicle::factory()->create();

    $response = $this->actingAs($user)->patch(route('vehicles.update', $vehicle), [
        'model_version_id' => $vehicle->model_version_id,
        'year' => 1899,
        'price' => 50000,
        'mileage' => 10000,
        'color' => 'Preto',
        'fuel_type' => FuelType::Flex->value,
        'transmission' => Transmission::Automatic->value,
        'status' => VehicleStatus::Active->value,
    ]);

    $response->assertSessionHasErrors('year');
});

test('price is required', function () {
    $user = User::factory()->create();
    $vehicle = Vehicle::factory()->create();

    $response = $this->actingAs($user)->patch(route('vehicles.update', $vehicle), [
        'model_version_id' => $vehicle->model_version_id,
        'year' => 2023,
        'mileage' => 10000,
        'color' => 'Preto',
        'fuel_type' => FuelType::Flex->value,
        'transmission' => Transmission::Automatic->value,
        'status' => VehicleStatus::Active->value,
    ]);

    $response->assertSessionHasErrors('price');
});

test('fuel_type must be a valid enum value', function () {
    $user = User::factory()->create();
    $vehicle = Vehicle::factory()->create();

    $response = $this->actingAs($user)->patch(route('vehicles.update', $vehicle), [
        'model_version_id' => $vehicle->model_version_id,
        'year' => 2023,
        'price' => 50000,
        'mileage' => 10000,
        'color' => 'Preto',
        'fuel_type' => 'invalid',
        'transmission' => Transmission::Automatic->value,
        'status' => VehicleStatus::Active->value,
    ]);

    $response->assertSessionHasErrors('fuel_type');
});

test('transmission must be a valid enum value', function () {
    $user = User::factory()->create();
    $vehicle = Vehicle::factory()->create();

    $response = $this->actingAs($user)->patch(route('vehicles.update', $vehicle), [
        'model_version_id' => $vehicle->model_version_id,
        'year' => 2023,
        'price' => 50000,
        'mileage' => 10000,
        'color' => 'Preto',
        'fuel_type' => FuelType::Flex->value,
        'transmission' => 'invalid',
        'status' => VehicleStatus::Active->value,
    ]);

    $response->assertSessionHasErrors('transmission');
});

test('status must be a valid enum value', function () {
    $user = User::factory()->create();
    $vehicle = Vehicle::factory()->create();

    $response = $this->actingAs($user)->patch(route('vehicles.update', $vehicle), [
        'model_version_id' => $vehicle->model_version_id,
        'year' => 2023,
        'price' => 50000,
        'mileage' => 10000,
        'color' => 'Preto',
        'fuel_type' => FuelType::Flex->value,
        'transmission' => Transmission::Automatic->value,
        'status' => 'invalid',
    ]);

    $response->assertSessionHasErrors('status');
});

test('returns 404 for non-existent vehicle', function () {
    $user = User::factory()->create();
    $modelVersion = ModelVersion::factory()->create();

    $response = $this->actingAs($user)->patch(route('vehicles.update', 999999), [
        'model_version_id' => $modelVersion->id,
        'year' => 2023,
        'price' => 50000,
        'mileage' => 10000,
        'color' => 'Preto',
        'fuel_type' => FuelType::Flex->value,
        'transmission' => Transmission::Automatic->value,
        'status' => VehicleStatus::Active->value,
    ]);

    $response->assertNotFound();
});
