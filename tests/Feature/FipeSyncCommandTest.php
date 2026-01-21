<?php

use App\Models\Make;
use App\Models\ModelVersion;
use App\Models\VehicleModel;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::preventStrayRequests();
});

test('sync only makes imports makes from fipe api', function () {
    Http::fake([
        '*/carros/marcas' => Http::response([
            ['codigo' => '59', 'nome' => 'Honda'],
            ['codigo' => '21', 'nome' => 'Fiat'],
            ['codigo' => '23', 'nome' => 'Ford'],
        ]),
    ]);

    $this->artisan('fipe:sync', ['--only-makes' => true])
        ->assertSuccessful();

    expect(Make::count())->toBe(3);
    expect(Make::where('fipe_code', '59')->first()->name)->toBe('Honda');
    expect(Make::where('fipe_code', '21')->first()->name)->toBe('Fiat');
    expect(Make::where('fipe_code', '23')->first()->name)->toBe('Ford');
});

test('sync only makes updates existing makes', function () {
    Make::factory()->create(['fipe_code' => '59', 'name' => 'Old Name']);

    Http::fake([
        '*/carros/marcas' => Http::response([
            ['codigo' => '59', 'nome' => 'Honda'],
        ]),
    ]);

    $this->artisan('fipe:sync', ['--only-makes' => true])
        ->assertSuccessful();

    expect(Make::count())->toBe(1);
    expect(Make::where('fipe_code', '59')->first()->name)->toBe('Honda');
});

test('sync make option syncs models and versions for specific make', function () {
    $make = Make::factory()->create(['fipe_code' => '59', 'name' => 'Honda']);

    Http::fake([
        '*/carros/marcas/59/modelos' => Http::response([
            'modelos' => [
                ['codigo' => '5585', 'nome' => 'CIVIC LX 1.6 16V'],
                ['codigo' => '5586', 'nome' => 'CIVIC EX 1.8 16V'],
            ],
        ]),
        '*/carros/marcas/59/modelos/5585/anos' => Http::response([
            ['codigo' => '2024-1', 'nome' => '2024 Gasolina'],
            ['codigo' => '2023-1', 'nome' => '2023 Gasolina'],
        ]),
        '*/carros/marcas/59/modelos/5586/anos' => Http::response([
            ['codigo' => '2024-3', 'nome' => '2024 Flex'],
        ]),
    ]);

    $this->artisan('fipe:sync', ['--make' => $make->id])
        ->assertSuccessful();

    expect(VehicleModel::count())->toBe(1);
    expect(VehicleModel::first()->name)->toBe('CIVIC');
    expect(ModelVersion::count())->toBe(3);
});

test('sync make option fails for non existent make', function () {
    $this->artisan('fipe:sync', ['--make' => 999])
        ->assertFailed();
});

test('sync make handles api errors gracefully', function () {
    $make = Make::factory()->create(['fipe_code' => '59', 'name' => 'Honda']);

    Http::fake([
        '*/carros/marcas/59/modelos' => Http::response([], 500),
    ]);

    $this->artisan('fipe:sync', ['--make' => $make->id])
        ->assertSuccessful();

    expect(VehicleModel::count())->toBe(0);
});

test('sync all syncs makes then models and versions', function () {
    Http::fake([
        '*/carros/marcas' => Http::response([
            ['codigo' => '59', 'nome' => 'Honda'],
        ]),
        '*/carros/marcas/59/modelos' => Http::response([
            'modelos' => [
                ['codigo' => '5585', 'nome' => 'FIT LX 1.4'],
            ],
        ]),
        '*/carros/marcas/59/modelos/5585/anos' => Http::response([
            ['codigo' => '2024-1', 'nome' => '2024 Gasolina'],
        ]),
    ]);

    $this->artisan('fipe:sync')
        ->assertSuccessful();

    expect(Make::count())->toBe(1);
    expect(VehicleModel::count())->toBe(1);
    expect(VehicleModel::first()->name)->toBe('FIT');
    expect(ModelVersion::count())->toBe(1);
});

test('parse model name extracts compound model names correctly', function () {
    Http::fake([
        '*/carros/marcas' => Http::response([
            ['codigo' => '26', 'nome' => 'Hyundai'],
        ]),
        '*/carros/marcas/26/modelos' => Http::response([
            'modelos' => [
                ['codigo' => '1234', 'nome' => 'Santa Fe 3.5 V6'],
            ],
        ]),
        '*/carros/marcas/26/modelos/1234/anos' => Http::response([
            ['codigo' => '2024-1', 'nome' => '2024 Gasolina'],
        ]),
    ]);

    $this->artisan('fipe:sync')
        ->assertSuccessful();

    $vehicleModel = VehicleModel::first();
    expect($vehicleModel->name)->toBe('Santa Fe');

    $version = ModelVersion::first();
    expect($version->name)->toBe('3.5 V6 2024 Gasolina');
});
