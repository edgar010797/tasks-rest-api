<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Priority;
use App\Models\Status;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'due_date' => fake()->dateTimeBetween('+1 day', '+1 month')->format('Y-m-d\TH:i:s'),
            'created_at' => now()->format('Y-m-d\TH:i:s'),
            'status_id' => Status::inRandomOrder()->value('id') ?? Status::factory(),
            'priority_id' => Priority::inRandomOrder()->value('id') ?? Priority::factory(),
            'category_id' => Category::inRandomOrder()->value('id') ?? Category::factory(),
            'user_id' => User::inRandomOrder()->value('id') ?? User::factory(),
        ];
    }

    public function title(string $title): static
    {
        return $this->state(fn() => [
            'title' => $title,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn() => [
            'status_id' => Status::where('slug', 'completed')->value('id'),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn() => [
            'status_id' => Status::where('slug', 'pending')->value('id'),
        ]);
    }

    public function highPriority(): static
    {
        return $this->state(fn() => [
            'priority_id' => Priority::where('slug', 'high')->value('id'),
        ]);
    }

    public function lowPriority(): static
    {
        return $this->state(fn() => [
            'priority_id' => Priority::where('slug', 'low')->value('id'),
        ]);
    }

    public function ofCategory(string $slug): static
    {
        return $this->state(fn() => [
            'category_id' => Category::where('slug', $slug)->value('id'),
        ]);
    }

    public function ofUser(string $email): static
    {
        return $this->state(fn() => [
            'user_id' => User::where('email', $email)->value('id'),
        ]);
    }

    public function dueToday(): static
    {
        return $this->state(fn() => [
            'due_date' => now()->addHours(3)->format('Y-m-d\TH:i:s'),
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn() => [
            'due_date' => now()->subDay()->format('Y-m-d\TH:i:s'),
            'status_id' => Status::where('slug', 'pending')->value('id'),
        ]);
    }
}
