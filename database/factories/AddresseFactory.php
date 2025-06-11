<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;


class AddresseFactory extends Factory
{

    public function definition(): array
    {
        return [
            'user_id'=>User::factory(),
            'address_line1'=>fake()->streetAddress(),
            'address_line2'=>fake()->streetAddress(),
            'city'=>fake()->city(),
            'postal_code'=>fake()->postcode(),
            'phone'=>fake()->phoneNumber(),
            'latitude'=>fake()->latitude(),
            'longitude'=>fake()->longitude(),
        ];
    }
}
