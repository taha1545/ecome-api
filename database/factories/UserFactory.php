<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'role' => 'client',
            'password' => static::$password ??= Hash::make('password'),
            'profile_image' => fake()->optional(70)->imageUrl(200, 200, 'people'),
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
            'created_at' => fake()->dateTimeBetween('-2 years', 'now'),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function role(string $role): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => $role,
        ]);
    }

    public function withProfileImage(): static
    {
        return $this->state(fn(array $attributes) => [
            'profile_image' => fake()->imageUrl(200, 200, 'people'),
        ]);
    }
}
