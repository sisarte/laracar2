<?php

use App\Models\User;
use App\Models\Vehicle;

test('guests are redirected to the login page', function () {
    $vehicle = Vehicle::factory()->create();

    $response = $this->get(route('vehicles.show', $vehicle));

    $response->assertRedirect(route('login'));
});

test('authenticated users can view a vehicle', function () {
    $user = User::factory()->create();
    $vehicle = Vehicle::factory()->create();

    $response = $this->actingAs($user)->get(route('vehicles.show', $vehicle));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('vehicles/Show')
        ->has('vehicle')
        ->where('vehicle.id', $vehicle->id)
    );
});

test('vehicle relationships are loaded', function () {
    $user = User::factory()->create();
    $vehicle = Vehicle::factory()->create();

    $response = $this->actingAs($user)->get(route('vehicles.show', $vehicle));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('vehicles/Show')
        ->has('vehicle.model_version')
        ->has('vehicle.model_version.vehicle_model')
        ->has('vehicle.model_version.vehicle_model.make')
    );
});

test('returns 404 for non-existent vehicle', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('vehicles.show', 999999));

    $response->assertNotFound();
});
