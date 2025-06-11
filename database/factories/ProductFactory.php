<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'description' => fake()->paragraphs(3, true),
            'brand' => fake()->company(),
            'price' => fake()->randomFloat(2, 10, 1000),
            'discount_price' => fake()->optional(30)->randomFloat(2, 5, 500),
            'is_active' => fake()->boolean(80),
            'views' => fake()->numberBetween(0, 10000),
            'created_at' => fake()->dateTimeBetween('-2 years'),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function ($product) {
            $this->createVariants($product);
            $this->createFiles($product);
        });
    }

    protected function createVariants($product): void
    {
        $variants = [];
        $count = fake()->numberBetween(1, 4);
        
        for ($i = 0; $i < $count; $i++) {
            $variants[] = [
                'size' => fake()->optional(70)->randomElement(['S', 'M', 'L', 'XL']),
                'color' => fake()->optional(60)->safeColorName(),
                'quantity' => fake()->numberBetween(0, 100),
                'price' => $product->price + fake()->randomFloat(2, -5, 20),
            ];
        }
        
        
        \Illuminate\Support\Facades\DB::table('product_variants')->insert(
            array_map(fn($v) => array_merge($v, [
                'product_id' => $product->id,
            ]), $variants)
        );
    }

    protected function createFiles($product): void
    {
        $files = [];
        $count = fake()->numberBetween(2, 5);
        
        for ($i = 0; $i < $count; $i++) {
            $type = fake()->randomElement(['image', 'document', '3d_model']);
            $files[] = [
                'path' => $this->generateFilePath($type),
                'type' => $type,
                'product_id' => $product->id,
            ];
        }
        
        \Illuminate\Support\Facades\DB::table('product_files')->insert($files);
    }

    private function generateFilePath(string $type): string
    {
        return match($type) {
            'image' => "products/images/".Str::uuid().".".fake()->randomElement(['jpg', 'png']),
            'document' => "products/documents/".Str::uuid().".pdf",
            '3d_model' => "products/models/".Str::uuid().".glb",
        };
    }

    public function withDiscount(): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_price' => $attributes['price'] * fake()->randomFloat(2, 0.5, 0.8),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
    public function withReviewCount(int $count): static
    {
        return $this->afterCreating(function ($product) use ($count) {
            $this->createReviews($product, $count);
        });
    }

    protected function createReviews($product, ?int $count = null): void
    {
        $count = $count ?? fake()->numberBetween(0, 15);
        $reviews = [];
        
        for ($i = 0; $i < $count; $i++) {
            $reviews[] = [
                'user_id' => \App\Models\User::factory(),
                'rating' => fake()->numberBetween(1, 5),
                'message' => fake()->optional(80)->paragraph(),
            ];
        }
        
        $product->reviews()->createMany($reviews);
    }
}