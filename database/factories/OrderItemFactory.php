<?php

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'price' => $this->faker->randomFloat(2, $product->price * 0.8, $product->price * 1.2),
        ]);
        
        $unitPrice = $variant->price;
        $discountAmount = $this->faker->optional(30)->randomFloat(2, 1, $unitPrice * 0.3);
        
        return [
            'order_id' => Order::factory(),
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'unit_price' => $unitPrice,
            'discount_amount' => $discountAmount ?? 0,
            'quantity' => $this->faker->numberBetween(1, 5),
            'created_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'updated_at' => function (array $attributes) {
                return $attributes['created_at'];
            },
        ];
    }

    public function forOrder(Order $order): static
    {
        return $this->state(function (array $attributes) use ($order) {
            return [
                'order_id' => $order->id,
                'created_at' => $order->created_at,
            ];
        });
    }

    public function forProduct(Product $product, ?ProductVariant $variant = null): static
    {
        return $this->state(function (array $attributes) use ($product, $variant) {
            $data = ['product_id' => $product->id];
            
            if ($variant) {
                $data['product_variant_id'] = $variant->id;
                $data['unit_price'] = $variant->price;
            } else if ($product->variants->count() > 0) {
                $randomVariant = $product->variants->random();
                $data['product_variant_id'] = $randomVariant->id;
                $data['unit_price'] = $randomVariant->price;
            } else {
                $data['unit_price'] = $product->price;
                $data['product_variant_id'] = null;
            }
            
            return $data;
        });
    }

    public function withDiscount(float $discountPercentage = null): static
    {
        return $this->state(function (array $attributes) use ($discountPercentage) {
            $percentage = $discountPercentage ?? $this->faker->numberBetween(5, 30);
            return [
                'discount_amount' => $attributes['unit_price'] * ($percentage / 100),
            ];
        });
    }

    public function withQuantity(int $quantity): static
    {
        return $this->state(function (array $attributes) use ($quantity) {
            return [
                'quantity' => $quantity,
            ];
        });
    }
}
