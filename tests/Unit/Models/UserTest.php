<?php

namespace Tests\Unit\Models;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_attributes(): void
    {
        $user = new User();

        $this->assertEquals([
            'name',
            'password',
            'firstname',
            'lastname',
            'email',
            'phone',
        ], $user->getFillable());
    }

    public function test_hidden_attributes(): void
    {
        $user = new User();

        $this->assertEquals(['password'], $user->getHidden());
    }

    public function test_password_is_hashed(): void
    {
        $user = User::factory()->create([
            'password' => '12345',
        ]);

        $this->assertNotEquals('12345', $user->password);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('12345', $user->password));
    }

    public function test_user_has_tasks_relation(): void
    {
        $user = User::factory()->create();
        $tasks = Task::factory()->count(3)->create([
            'user_id' => $user->id,
        ]);

        $this->assertCount(3, $user->tasks);
        $this->assertInstanceOf(Task::class, $user->tasks->first());
    }

    public function test_user_has_jwt_identifier(): void
    {
        $user = User::factory()->create();

        $this->assertEquals($user->id, $user->getJWTIdentifier());
    }

    public function test_user_has_empty_jwt_custom_claims(): void
    {
        $user = new User();

        $this->assertEquals([], $user->getJWTCustomClaims());
    }

    public function test_user_can_be_created_with_optional_fields(): void
    {
        $user = User::factory()->create([
            'email' => 'minimal@example.com',
            'password' => '12345',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'minimal@example.com',
        ]);
    }

    public function test_user_uses_correct_table(): void
    {
        $user = new User();

        $this->assertEquals('users', $user->getTable());
    }
}
