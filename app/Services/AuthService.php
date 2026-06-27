<?php

namespace App\Services;

use \Illuminate\Http\Response;
use App\Exceptions\ApiException;
use App\Services\Interfaces\AuthServiceInterface;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;

class AuthService implements AuthServiceInterface
{
	public function register(array $data): array
	{
		$user = User::create($data);
		$token = JWTAuth::fromUser($user);

		return [
			'access_token'	=> $token,
			'token_type'	=> 'Bearer',
			'expires_in'	=> auth('api')->factory()->getTTL() * 60,
			'data'			=> $user,
		];
	}

	public function login(array $credentials): array
	{
		if (!$token = JWTAuth::attempt($credentials)) {
			throw new ApiException(trans('auth.login.invalid_credentials'), Response::HTTP_UNAUTHORIZED);
		}

		return [
			'access_token'	=> $token,
			'token_type'	=> 'Bearer',
			'expires_in'	=> auth('api')->factory()->getTTL() * 60,
			'data'			=> Auth::user(),
		];
	}

	public function logout(): void
	{
		JWTAuth::invalidate(JWTAuth::getToken());
	}

	public function getUser(): mixed
	{
		if (!$user = Auth::user()) {
			throw new ApiException(trans('auth.user.not_found'), Response::HTTP_NOT_FOUND);
		}
		return $user;
	}

	public function updateUser(array $data): mixed
	{
		$user = Auth::user();
		$user->update($data);
		return $user;
	}
}
