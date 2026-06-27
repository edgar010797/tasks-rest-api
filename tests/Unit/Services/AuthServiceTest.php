<?php

namespace Tests\Unit\Services;

use App\Exceptions\ApiException;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authService = new AuthService();
    }

    public function test_register_returns_token_and_user(): void
    {
        $data = [
            'email' => 'newuser@test.com',
            'password' => '12345',
            'name' => 'TestUser',
        ];

        $result = $this->authService->register($data);

        $this->assertArrayHasKey('access_token', $result);
        $this->assertArrayHasKey('token_type', $result);
        $this->assertArrayHasKey('expires_in', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals('Bearer', $result['token_type']);
        $this->assertInstanceOf(User::class, $result['data']);
        $this->assertEquals('newuser@test.com', $result['data']->email);

        $this->assertDatabaseHas('users', ['email' => 'newuser@test.com']);
    }

    public function test_login_with_valid_credentials_returns_token(): void
    {
        User::factory()->create([
            'email' => 'login@test.com',
            'password' => '12345',
        ]);

        $result = $this->authService->login([
            'email' => 'login@test.com',
            'password' => '12345',
        ]);

        $this->assertArrayHasKey('access_token', $result);
        $this->assertEquals('Bearer', $result['token_type']);
    }

    public function test_login_with_invalid_credentials_throws_exception(): void
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionCode(401);

        $this->authService->login([
            'email' => 'nonexistent@test.com',
            'password' => 'wrong-password',
        ]);
    }

    public function test_logout_invalidates_token(): void
    {
        $user = User::factory()->create(['email' => 'logout@test.com']);
        $token = JWTAuth::fromUser($user);

        JWTAuth::setToken($token);
        $this->authService->logout();

        $this->expectException(\Tymon\JWTAuth\Exceptions\TokenInvalidException::class);
        JWTAuth::setToken($token)->authenticate();
    }

    public function test_logout_without_token_throws_exception(): void
    {
        $this->expectException(\Tymon\JWTAuth\Exceptions\JWTException::class);

        $this->authService->logout();
    }
}
