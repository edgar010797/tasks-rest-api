<?php

namespace App\Services\Interfaces;

interface AuthServiceInterface
{
	public function register(array $data): array;
	public function login(array $credentials): array;
	public function logout(): void;
	public function getUser(): mixed;
	public function updateUser(array $data): mixed;
}
