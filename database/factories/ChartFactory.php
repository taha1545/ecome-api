<?php

namespace Database\Factories;

use App\Models\Chart;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChartFactory extends Factory
{
    protected $model = Chart::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'type' => $this->faker->randomElement(array_keys(Chart::TYPES)),
            'description' => $this->faker->sentence(),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Chart $chart) {
            for ($i = 0; $i < 5; $i++) {
                $chart->items()->create([
                    'label' => $this->faker->word(),
                    'value' => $this->faker->randomFloat(4, 1, 100),
                    'position' => $i
                ]);
            }
        });
    }
}
