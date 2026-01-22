<?php

use App\Models\Make;
use App\Models\User;
use App\Models\Vehicle;

test('guests are redirected to the login page', function () {
    $vehicle = Vehicle::factory()->create();

    $response = $this->get(route('vehicles.edit', $vehicle));

    $response->assertRedirect(route('login'));
});

test('authenticated users can view the edit vehicle form', function () {
    $user = User::factory()->create();
    $vehicle = Vehicle::factory()->create();

    $response = $this->actingAs($user)->get(route('vehicles.edit', $vehicle));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('vehicles/Edit')
        ->has('vehicle')
        ->where('vehicle.id', $vehicle->id)
    );
});

test('vehicle relationships are loaded', function () {
    $user = User::factory()->create();
    $vehicle = Vehicle::factory()->create();

    $response = $this->actingAs($user)->get(route('vehicles.edit', $vehicle));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('vehicles/Edit')
        ->has('vehicle.model_version')
        ->has('vehicle.model_version.vehicle_model')
        ->has('vehicle.model_version.vehicle_model.make')
    );
});

test('makes are passed to the view', function () {
    $user = User::factory()->create();
    $vehicle = Vehicle::factory()->create();
    Make::factory()->count(3)->create();

    $response = $this->actingAs($user)->get(route('vehicles.edit', $vehicle));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('vehicles/Edit')
        ->has('makes')
    );
});

test('enums are passed to the view', function () {
    $user = User::factory()->create();
    $vehicle = Vehicle::factory()->create();

    $response = $this->actingAs($user)->get(route('vehicles.edit', $vehicle));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('vehicles/Edit')
        ->has('fuelTypes')
        ->has('transmissions')
        ->has('statuses')
    );
});

test('returns 404 for non-existent vehicle', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('vehicles.edit', 999999));

    $response->assertNotFound();
});
