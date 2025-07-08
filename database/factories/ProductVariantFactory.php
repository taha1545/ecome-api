<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'size' => $this->faker->optional(70)->randomElement(['XS', 'S', 'M', 'L', 'XL', 'XXL']),
            'color' => $this->faker->optional(60)->safeColorName(),
            'quantity' => $this->faker->numberBetween(0, 100),
            'description'=>$this->faker->optional(60)->safeColorName(),
            'price' => $this->faker->randomFloat(2, 10, 1000),
        ];
    }

    public function inStock(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'quantity' => $this->faker->numberBetween(1, 100),
            ];
        });
    }

    public function outOfStock(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'quantity' => 0,
            ];
        });
    }

    public function withSize(string $size): static
    {
        return $this->state(function (array $attributes) use ($size) {
            return [
                'size' => $size,
            ];
        });
    }

    public function withColor(string $color): static
    {
        return $this->state(function (array $attributes) use ($color) {
            return [
                'color' => $color,
            ];
        });
    }
}
