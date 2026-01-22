<?php

use App\Enums\FuelType;
use App\Enums\Transmission;
use App\Enums\VehicleStatus;
use App\Models\ModelVersion;
use App\Models\User;
use App\Models\Vehicle;

test('guests are redirected to the login page', function () {
    $response = $this->post(route('vehicles.store'));

    $response->assertRedirect(route('login'));
});

test('authenticated users can create a vehicle', function () {
    $user = User::factory()->create();
    $modelVersion = ModelVersion::factory()->create();

    $response = $this->actingAs($user)->post(route('vehicles.store'), [
        'model_version_id' => $modelVersion->id,
        'year' => 2023,
        'price' => 50000,
        'mileage' => 10000,
        'color' => 'Preto',
        'fuel_type' => FuelType::Flex->value,
        'transmission' => Transmission::Automatic->value,
        'description' => 'Veículo em ótimo estado',
        'status' => VehicleStatus::Active->value,
    ]);

    $vehicle = Vehicle::first();
    $response->assertRedirect(route('vehicles.show', $vehicle));
    expect(Vehicle::count())->toBe(1);
    expect($vehicle->model_version_id)->toBe($modelVersion->id);
    expect($vehicle->year)->toBe(2023);
    expect($vehicle->price)->toBe(50000.0);
    expect($vehicle->mileage)->toBe(10000);
    expect($vehicle->color)->toBe('Preto');
});

test('model_version_id is required', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('vehicles.store'), [
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

    $response = $this->actingAs($user)->post(route('vehicles.store'), [
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
    $modelVersion = ModelVersion::factory()->create();

    $response = $this->actingAs($user)->post(route('vehicles.store'), [
        'model_version_id' => $modelVersion->id,
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
    $modelVersion = ModelVersion::factory()->create();

    $response = $this->actingAs($user)->post(route('vehicles.store'), [
        'model_version_id' => $modelVersion->id,
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
    $modelVersion = ModelVersion::factory()->create();

    $response = $this->actingAs($user)->post(route('vehicles.store'), [
        'model_version_id' => $modelVersion->id,
        'year' => 2023,
        'mileage' => 10000,
        'color' => 'Preto',
        'fuel_type' => FuelType::Flex->value,
        'transmission' => Transmission::Automatic->value,
        'status' => VehicleStatus::Active->value,
    ]);

    $response->assertSessionHasErrors('price');
});

test('price must be positive', function () {
    $user = User::factory()->create();
    $modelVersion = ModelVersion::factory()->create();

    $response = $this->actingAs($user)->post(route('vehicles.store'), [
        'model_version_id' => $modelVersion->id,
        'year' => 2023,
        'price' => -100,
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
    $modelVersion = ModelVersion::factory()->create();

    $response = $this->actingAs($user)->post(route('vehicles.store'), [
        'model_version_id' => $modelVersion->id,
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
    $modelVersion = ModelVersion::factory()->create();

    $response = $this->actingAs($user)->post(route('vehicles.store'), [
        'model_version_id' => $modelVersion->id,
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
    $modelVersion = ModelVersion::factory()->create();

    $response = $this->actingAs($user)->post(route('vehicles.store'), [
        'model_version_id' => $modelVersion->id,
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
