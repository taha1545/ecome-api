<?php

namespace Database\Factories;

use App\Models\ChartItem;
use App\Models\Chart;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChartItemFactory extends Factory
{
    protected $model = ChartItem::class;

    public function definition(): array
    {
        return [
            'chart_id' => Chart::factory(),
            'label' => $this->faker->word(),
            'value' => $this->faker->randomFloat(4, 1, 100),
            'position' => $this->faker->numberBetween(0, 10),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => function (array $attributes) {
                return $attributes['created_at'];
            },
        ];
    }

    public function forChart(Chart $chart): static
    {
        return $this->state(function (array $attributes) use ($chart) {
            return [
                'chart_id' => $chart->id,
            ];
        });
    }

    public function withPosition(int $position): static
    {
        return $this->state(function (array $attributes) use ($position) {
            return [
                'position' => $position,
            ];
        });
    }

    public function withValue(float $value): static
    {
        return $this->state(function (array $attributes) use ($value) {
            return [
                'value' => $value,
            ];
        });
    }

    public function withLabel(string $label): static
    {
        return $this->state(function (array $attributes) use ($label) {
            return [
                'label' => $label,
            ];
        });
    }
}
