<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'slug' => fake()->slug(),
        ];
    }

    public function work()
    {
        return $this->state(['name' => 'Работа', 'slug' => 'work']);
    }

    public function home()
    {
        return $this->state(['name' => 'Дом', 'slug' => 'home']);
    }

    public function personal()
    {
        return $this->state(['name' => 'Личное', 'slug' => 'personal']);
    }
}
