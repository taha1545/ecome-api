<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductFile;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFileFactory extends Factory
{
    protected $model = ProductFile::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(['image', 'document', '3d_model']);
        
        return [
            'product_id' => Product::factory(),
            'path' => $this->generateFilePath($type),
            'type' => $type,
        ];
    }

    private function generateFilePath(string $type): string
    {
        return match($type) {
            'image' => "products/images/".Str::uuid().".".fake()->randomElement(['jpg', 'png']),
            'document' => "products/documents/".Str::uuid().".pdf",
            '3d_model' => "products/models/".Str::uuid().".glb",
        };
    }

    public function image(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'image',
                'path' => $this->generateFilePath('image'),
            ];
        });
    }

    public function document(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'document',
                'path' => $this->generateFilePath('document'),
            ];
        });
    }

    public function model3d(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => '3d_model',
                'path' => $this->generateFilePath('3d_model'),
            ];
        });
    }
}
