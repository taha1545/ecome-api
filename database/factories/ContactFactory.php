<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactFactory extends Factory
{
    protected $model = Contact::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->name(),
            'phone' => $this->faker->numerify('##########'), 
            'email' => $this->faker->safeEmail(),
            'notes' => $this->faker->optional()->sentence(),
            'type' => $this->faker->randomElement(array_keys(Contact::TYPES)),
            'is_primary' => $this->faker->boolean(20), 
        ];
    }
}
