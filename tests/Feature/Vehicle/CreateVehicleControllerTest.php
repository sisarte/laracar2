<?php

use App\Models\Make;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('vehicles.create'));

    $response->assertRedirect(route('login'));
});

test('authenticated users can view the create vehicle form', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('vehicles.create'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('vehicles/Create'));
});

test('makes are passed to the view', function () {
    $user = User::factory()->create();
    Make::factory()->count(3)->create();

    $response = $this->actingAs($user)->get(route('vehicles.create'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('vehicles/Create')
        ->has('makes', 3)
    );
});

test('enums are passed to the view', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('vehicles.create'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('vehicles/Create')
        ->has('fuelTypes')
        ->has('transmissions')
        ->has('statuses')
    );
});
