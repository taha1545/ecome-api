<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'user_id' => User::factory(),
            'desc' => "hello nigga",
            'recu_path' => 'receipts/' . Str::random(10) . '.pdf',
            'transaction_id' => 'txn_' . Str::upper(Str::random(12)),
            'order_number' => Str::upper(Str::random(10)),
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'status' => 'pending',
            'gateway_response' => json_encode([
                'id' => 'ch_' . Str::upper(Str::random(10)),
                'status' => 'succeeded',
            ]),
            'error_message' => $this->faker->optional()->sentence(),
            'processed_at' => $this->faker->optional()->dateTimeBetween('-2 days'),
        ];
    }

    public function succeeded(): static
    {
        return $this->state(fn() => [
            'status' => 'succeeded',
            'processed_at' => now()->subMinutes(10),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn() => [
            'status' => 'failed',
            'error_message' => $this->faker->sentence(),
        ]);
    }

    public function refunded(): static
    {
        return $this->state(fn() => [
            'status' => 'refunded',
            'processed_at' => now()->subHours(2),
        ]);
    }
}
