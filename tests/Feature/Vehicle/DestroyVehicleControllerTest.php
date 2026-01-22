<?php

use App\Models\User;
use App\Models\Vehicle;

test('guests are redirected to the login page', function () {
    $vehicle = Vehicle::factory()->create();

    $response = $this->delete(route('vehicles.destroy', $vehicle));

    $response->assertRedirect(route('login'));
});

test('authenticated users can delete a vehicle', function () {
    $user = User::factory()->create();
    $vehicle = Vehicle::factory()->create();

    $response = $this->actingAs($user)->delete(route('vehicles.destroy', $vehicle));

    $response->assertRedirect(route('vehicles.index'));
    expect(Vehicle::find($vehicle->id))->toBeNull();
});

test('returns 404 for non-existent vehicle', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->delete(route('vehicles.destroy', 999999));

    $response->assertNotFound();
});
