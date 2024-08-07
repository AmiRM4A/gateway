<?php

namespace Database\Factories;

use App\Models\Gateway;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'unique_id' => Transaction::generateUniqueId(),
            'order_id' => $this->faker->unique()->randomNumber(),
            'transaction_id' => $this->faker->uuid,
            'gateway_id' => Gateway::select('id')->get()->random()->id,
            'amount' => $this->faker->randomFloat(2, 1, 1000), // Random float between 1 and 1000 with 2 decimal points
            'link' => $this->faker->url,
            'is_verified' => $this->faker->randomElement(['0', '1']),
            'status_code' => null
        ];
    }
}
