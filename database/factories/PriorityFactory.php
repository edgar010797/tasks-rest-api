<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PriorityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'slug' => fake()->slug(),
            'color' => fake()->hexColor(),
            'level' => fake()->numberBetween(0, 2),
        ];
    }

    public function low()
    {
        return $this->state(['name' => 'Низкий', 'slug' => 'low', 'color' => '#3b82f6', 'level' => 0]);
    }

    public function medium()
    {
        return $this->state(['name' => 'Средний', 'slug' => 'medium', 'color' => '#f59e0b', 'level' => 1]);
    }

    public function high()
    {
        return $this->state(['name' => 'Высокий', 'slug' => 'high', 'color' => '#ef4444', 'level' => 2]);
    }
}
