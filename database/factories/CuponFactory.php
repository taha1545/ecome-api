<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;


class CuponFactory extends Factory
{
    
    public function definition(): array
    {
        return [
          'code'=>fake()->password(),
          'value'=>random_int(1,80),
          'max_usage'=>200,
          'used_count'=>random_int(1,200),
          'expires_at'=>fake()->dateTimeBetween('now','+2 years'),
          'is_active'=>1
        ];
    }
}
