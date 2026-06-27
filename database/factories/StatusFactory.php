<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class StatusFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'slug' => fake()->slug(),
        ];
    }

    public function completed()
    {
        return $this->state(['name' => 'Выполнена', 'slug' => 'completed']);
    }

    public function pending()
    {
        return $this->state(['name' => 'Не выполнена', 'slug' => 'pending']);
    }
}
