<?php

namespace Database\Factories;

use App\Models\VehicleModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ModelVersion>
 */
class ModelVersionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vehicle_model_id' => VehicleModel::factory(),
            'fipe_code' => fake()->unique()->numerify('####-#'),
            'name' => fake()->words(3, true),
        ];
    }
}
