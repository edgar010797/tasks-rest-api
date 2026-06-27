<?php

namespace App\Http\Controllers\Api\V1;

use \Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\StoreUserRequest;
use App\Http\Requests\Api\V1\UpdateUserRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Services\Interfaces\AuthServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class AuthController extends Controller
{
	public function __construct(
		private AuthServiceInterface $authService
	) {}

	public function register(StoreUserRequest $request): JsonResponse
	{
		return response()->json(
			$this->authService->register($request->validated()),
			Response::HTTP_CREATED
		);
	}

	public function login(LoginRequest $request): JsonResponse
	{
		return response()->json(
			$this->authService->login($request->validated()),
			Response::HTTP_OK
		);
	}

	public function logout(): Response
	{
		$this->authService->logout();
		return response()->noContent();
	}

	public function getUser(): JsonResponse
	{
		$user = $this->authService->getUser();

		return (new UserResource($user))
			->response()
			->setStatusCode(Response::HTTP_OK);
	}

	public function updateUser(UpdateUserRequest $request): JsonResponse
	{
		$user = $this->authService->updateUser($request->validated());

		return (new UserResource($user))
			->response()
			->setStatusCode(Response::HTTP_OK);
	}
}
