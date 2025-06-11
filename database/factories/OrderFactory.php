<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        $baseAmount = fake()->randomFloat(2, 50, 1000);
        $taxRate = fake()->randomFloat(2, 5, 25);
        $shippingCost = fake()->randomFloat(2, 5, 50);

        return [
            'user_id' => \App\Models\User::factory(),
            'coupon_id' => null,

            'status' => fake()->randomElement([
                'pending',
                'processing',
                'confirmed',
                'shipped',
                'delivered',
                'canceled'
            ]),

            'subtotal' => $baseAmount,
            'tax' => $baseAmount * ($taxRate / 100),
            'shipping_cost' => $shippingCost,
            'total' => $baseAmount + ($baseAmount * ($taxRate / 100)) + $shippingCost,

    
            'cancelled_at' => null,
            'created_at' => fake()->dateTimeBetween('-6 months'),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function ($order) {
            // Create order items
            $items = \App\Models\Product::with('variants')
                ->inRandomOrder()
                ->take(fake()->numberBetween(1, 5))
                ->get()
                ->map(function ($product) {
                    $variant = $product->variants->random();

                    return [
                        'product_id' => $product->id,
                        'product_variant_id' => $variant->id,
                        'unit_price' => $variant->price,
                        'discount_amount' => fake()->optional(30)->randomFloat(2, 5, 20),
                        'quantity' => fake()->numberBetween(1, 5),
                    ];
                });

            $order->items()->createMany($items);

            // Recalculate totals
            $order->update([
                'subtotal' => $order->items->sum(
                    fn($item) => ($item->unit_price - $item->discount_amount) * $item->quantity
                ),
                'total' => $order->subtotal + $order->tax + $order->shipping_cost
            ]);
        });
    }

    private function fakeAddress(): string
    {
        return implode("\n", [
            fake()->streetAddress(),
            fake()->city(),
            fake()->state(),
            fake()->postcode(),
            fake()->country()
        ]);
    }

    private function formatVariantDetails($variant): ?string
    {
        $details = [];
        if ($variant->size) $details[] = "Size: {$variant->size}";
        if ($variant->color) $details[] = "Color: {$variant->color}";
        return $details ? implode(', ', $details) : null;
    }

    // State Methods
    public function paid(): static
    {
        return $this->state([
        ]);
    }

 
    public function withoutCoupon(): static
    {
        return $this->state([
            'coupon_id' => null,
        ]);
    }

    public function delivered(): static
    {
        return $this->state([
            'status' => 'delivered',
            'delivery_status' => 'delivered',
        ]);
    }

    public function cancelled(): static
    {
        return $this->state([
            'status' => 'canceled',
            'cancelled_at' => now()->subDays(1),
        ]);
    }
}
