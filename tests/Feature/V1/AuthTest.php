<?php

namespace Tests\Feature\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private array $validRegistrationData;
    private array $validLoginData;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validRegistrationData = [
            'email' => 'test@example.com',
            'password' => '12345',
            'name' => 'TestUser',
            'firstname' => 'Test',
            'lastname' => 'User',
            'phone' => '+7 (123) 456-78-90',
        ];

        $this->validLoginData = [
            'email' => 'existing@example.com',
            'password' => '12345',
        ];

        $this->user = User::factory()->create($this->validLoginData);
    }

    public function test_user_can_register(): void
    {
        $data = [
            'name' => 'NewUser',
            'email' => 'newuser@example.com',
            'password' => '12345',
        ];

        $response = $this->postJson('/api/v1/auth/register', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_in',
                'data' => ['name', 'email'],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
        ]);
    }

    public function test_user_can_register_with_all_fields(): void
    {
        $data = [
            'email' => 'fulluser@example.com',
            'password' => '12345',
            'name' => 'FullUser',
            'firstname' => 'Full',
            'lastname' => 'User',
            'phone' => '+7 (123) 456-78-90',
        ];

        $response = $this->postJson('/api/v1/auth/register', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_in',
                'data' => ['name', 'firstname', 'lastname', 'email', 'phone'],
            ]);
    }

    public function test_registration_requires_email_and_password(): void
    {
        $response = $this->postJson('/api/v1/auth/register', []);

        $response->assertStatus(422)
            ->assertJson([
                'error' => 'Validation failed.',
            ]);
    }

    public function test_registration_email_must_be_unique(): void
    {
        $response = $this->postJson('/api/v1/auth/register', $this->validLoginData);

        $response->assertStatus(422)
            ->assertJson([
                'error' => 'Validation failed.',
            ]);
    }

    public function test_user_can_login(): void
    {
        $response = $this->postJson('/api/v1/auth/login', $this->validLoginData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_in',
                'data' => ['name', 'email'],
            ]);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $this->validLoginData['email'],
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJson(['error' => 'Invalid email or password']);
    }

    public function test_login_fails_with_nonexistent_email(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'nonexistent@example.com',
            'password' => '12345',
        ]);

        $response->assertStatus(401)
            ->assertJson(['error' => 'Invalid email or password']);
    }

    public function test_login_requires_valid_data(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'not-an-email',
            'password' => '',
        ]);

        $response->assertStatus(422);
    }

    public function test_user_can_logout(): void
    {
        $token = JWTAuth::fromUser($this->user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/auth/logout');

        $response->assertStatus(204);
    }

    public function test_logout_without_token_returns_401(): void
    {
        $response = $this->getJson('/api/v1/auth/logout');

        $response->assertStatus(401);
    }

    public function test_user_can_update_profile(): void
    {
        $token = JWTAuth::fromUser($this->user);

        $updateData = [
            'name' => 'UpdatedName',
            'firstname' => 'UpdatedFirst',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/auth/update', $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => 'UpdatedName',
                    'firstname' => 'UpdatedFirst',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'UpdatedName',
            'firstname' => 'UpdatedFirst',
        ]);
    }

    public function test_update_email_must_be_unique(): void
    {
        User::factory()->create([
            'email' => 'another@example.com',
        ]);

        $token = JWTAuth::fromUser($this->user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/auth/update', [
                'email' => 'another@example.com',
            ]);

        $response->assertStatus(422);
    }

    public function test_update_without_token_returns_401(): void
    {
        $response = $this->postJson('/api/v1/auth/update', [
            'name' => 'UpdatedName',
        ]);

        $response->assertStatus(401);
    }
}
