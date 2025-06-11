<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => \App\Models\Order::factory(),
            'user_id' => \App\Models\User::factory(),
            
            'method' => fake()->randomElement(['credit_card', 'paypal', 'stripe', 'bank_transfer']),
            'amount' => fake()->randomFloat(2, 10, 1000),
            'currency' => 'USD',
            'status' => 'pending',
            
            'gateway_id' => 'txn_'.Str::upper(Str::random(10)),
            'gateway_response' => json_encode([
                'id' => 'ch_'.Str::upper(Str::random(10)),
                'status' => 'succeeded'
            ]),
            
            'error_code' => fake()->optional()->bothify('ERR_####'),
            'error_message' => fake()->optional()->sentence(),
            'processed_at' => fake()->optional()->dateTimeBetween('-1 day'),
        ];
    }

    public function succeeded(): static
    {
        return $this->state([
            'status' => 'succeeded',
            'processed_at' => now()->subMinutes(10),
        ]);
    }

    public function failed(): static
    {
        return $this->state([
            'status' => 'failed',
            'error_code' => 'ERR_'.fake()->bothify('####'),
            'error_message' => fake()->sentence(),
        ]);
    }

    public function refunded(): static
    {
        return $this->state([
            'status' => 'refunded',
            'processed_at' => now()->subHours(2),
        ]);
    }
}