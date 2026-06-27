<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    protected static ?string $password = '12345';

    public function definition(): array
    {
        return [
            'name' => fake()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'firstname' => fake()->firstName(),
            'lastname' => fake()->lastName(),
            'phone' => fake()->phoneNumber(),
        ];
    }

    public function password(string $pass): static
    {
        return $this->state(fn(array $attributes) => [
            'password' => Hash::make($pass),
        ]);
    }

    public function email(string $email): static
    {
        return $this->state(fn(array $attributes) => [
            'email' => $email,
        ]);
    }
}
