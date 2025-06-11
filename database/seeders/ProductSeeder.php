<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
      
        $categories = \App\Models\Categorie::factory()->count(10)->create();
        
      
        $tags = \App\Models\Tag::factory()->count(10)->create();
        
        // Create 50 products with relationships
        for ($i = 0; $i < 50; $i++) {
            $factory = \App\Models\Product::factory();

            // discount to 30%
            if (fake()->boolean(30)) {
                $factory->withDiscount();
            }

            // 10% 
            if (fake()->boolean(10)) {
                $factory->inactive();
            }

            // 
            $factory->afterCreating(function ($product) use ($categories, $tags) {
          
                $product->categories()->attach(
                    $categories->random(rand(1, 3))->pluck('id')
                );

            
                $product->tags()->attach(
                    $tags->random(rand(1, 3))->pluck('id')
                );
            });

    
            if (fake()->boolean(70)) {
                $factory->withReviewCount(rand(0, 15));
            }

            $factory->create();
        }
    }
}