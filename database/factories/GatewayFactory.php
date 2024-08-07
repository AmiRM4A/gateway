<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Gateway>
 */
class GatewayFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'service_path' => 'App\\Services\\' . fake()->word() . '\\TransactionService',
            'api_key' => fake()->uuid(),
            'description' => fake()->realText(),
        ];
    }
}
