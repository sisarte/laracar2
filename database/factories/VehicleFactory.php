<?php

namespace Database\Factories;

use App\Enums\FuelType;
use App\Enums\Transmission;
use App\Enums\VehicleStatus;
use App\Models\ModelVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'model_version_id' => ModelVersion::factory(),
            'year' => fake()->numberBetween(2000, 2025),
            'price' => fake()->randomFloat(2, 10000, 500000),
            'mileage' => fake()->numberBetween(0, 300000),
            'color' => fake()->safeColorName(),
            'fuel_type' => fake()->randomElement(FuelType::cases()),
            'transmission' => fake()->randomElement(Transmission::cases()),
            'description' => fake()->paragraph(),
            'status' => VehicleStatus::Active,
        ];
    }

    public function sold(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => VehicleStatus::Sold,
        ]);
    }

    public function paused(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => VehicleStatus::Paused,
        ]);
    }
}
