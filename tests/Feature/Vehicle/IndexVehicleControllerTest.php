<?php

use App\Models\User;
use App\Models\Vehicle;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('vehicles.index'));

    $response->assertRedirect(route('login'));
});

test('authenticated users can view the vehicles list', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('vehicles.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('vehicles/Index'));
});

test('vehicles are paginated', function () {
    $user = User::factory()->create();
    Vehicle::factory()->count(20)->create();

    $response = $this->actingAs($user)->get(route('vehicles.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('vehicles/Index')
        ->has('vehicles.data', 15)
    );
});

test('vehicles can be filtered by status', function () {
    $user = User::factory()->create();
    Vehicle::factory()->count(5)->create();
    $soldVehicle = Vehicle::factory()->sold()->create();

    $response = $this->actingAs($user)->get(route('vehicles.index', ['status' => 'sold']));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('vehicles/Index')
        ->has('vehicles.data', 1)
        ->where('vehicles.data.0.id', $soldVehicle->id)
    );
});

test('vehicles can be filtered by fuel type', function () {
    $user = User::factory()->create();
    Vehicle::factory()->create(['fuel_type' => 'gasoline']);
    Vehicle::factory()->create(['fuel_type' => 'diesel']);

    $response = $this->actingAs($user)->get(route('vehicles.index', ['fuel_type' => 'diesel']));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('vehicles/Index')
        ->has('vehicles.data', 1)
        ->where('vehicles.data.0.fuel_type', 'diesel')
    );
});

test('vehicles can be filtered by transmission', function () {
    $user = User::factory()->create();
    Vehicle::factory()->create(['transmission' => 'manual']);
    Vehicle::factory()->create(['transmission' => 'automatic']);

    $response = $this->actingAs($user)->get(route('vehicles.index', ['transmission' => 'automatic']));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('vehicles/Index')
        ->has('vehicles.data', 1)
        ->where('vehicles.data.0.transmission', 'automatic')
    );
});

test('enums are passed to the view', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('vehicles.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('vehicles/Index')
        ->has('fuelTypes')
        ->has('transmissions')
        ->has('statuses')
    );
});
