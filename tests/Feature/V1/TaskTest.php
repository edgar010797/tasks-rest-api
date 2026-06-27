<?php

namespace Tests\Feature\V1;

use App\Models\Category;
use App\Models\Priority;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;
    private Status $status;
    private Priority $priority;
    private Category $category;
    private array $validTaskData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => '12345',
        ]);

        $this->token = JWTAuth::fromUser($this->user);

        $this->status = Status::factory()->create(['slug' => 'pending']);
        $this->priority = Priority::factory()->create(['slug' => 'medium']);
        $this->category = Category::factory()->create(['slug' => 'work']);

        $this->validTaskData = [
            'title' => 'Test Task Title',
            'description' => 'Test task description',
            'due_date' => now()->addDays(7)->format('Y-m-d\TH:i:s'),
            'status_id' => $this->status->id,
            'priority_id' => $this->priority->id,
            'category_id' => $this->category->id,
        ];
    }

    private function authHeaders(): array
    {
        return ['Authorization' => 'Bearer ' . $this->token];
    }

    public function test_can_list_tasks(): void
    {
        Task::factory()->count(15)->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/v1/tasks');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'description', 'due_date', 'created_at', 'status', 'priority', 'category'],
                ],
                'meta' => ['current_page', 'per_page', 'total'],
            ]);
    }

    public function test_list_tasks_with_custom_per_page(): void
    {
        Task::factory()->count(15)->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/v1/tasks?per_page=5');

        $response->assertStatus(200);
        $this->assertCount(5, $response->json('data'));
    }

    public function test_list_tasks_per_page_capped_at_100(): void
    {
        Task::factory()->count(50)->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/v1/tasks?per_page=200');

        $response->assertStatus(200);
        $this->assertCount(50, $response->json('data'));
    }

    public function test_list_tasks_with_search(): void
    {
        Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Unique Searchable Task Title',
            'description' => 'Some description',
        ]);
        Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Other Task',
            'description' => 'Contains unique in description only',
        ]);

        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/v1/tasks?search=Searchable+Task+Title');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_list_tasks_search_by_description(): void
    {
        Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'First',
            'description' => 'Special search text only here',
        ]);

        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/v1/tasks?search=Special+search+text+only+here');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_list_tasks_with_sort_by_created_at(): void
    {
        Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Old Task',
            'due_date' => now()->addDays(10)->format('Y-m-d\TH:i:s'),
            'created_at' => now()->subDays(5),
        ]);
        Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'New Task',
            'due_date' => now()->addDays(5)->format('Y-m-d\TH:i:s'),
            'created_at' => now(),
        ]);

        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/v1/tasks?sort=created_at');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals('Old Task', $data[0]['title']);
        $this->assertEquals('New Task', $data[1]['title']);
    }

    public function test_list_only_returns_own_tasks(): void
    {
        Task::factory()->count(3)->create(['user_id' => $this->user->id]);

        $otherUser = User::factory()->create();
        Task::factory()->count(5)->create(['user_id' => $otherUser->id]);

        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/v1/tasks');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    public function test_list_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/tasks');
        $response->assertStatus(401);
    }

    public function test_can_show_task(): void
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Visible Task',
        ]);

        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/v1/tasks/' . $task->id);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $task->id,
                    'title' => 'Visible Task',
                ],
            ]);
    }

    public function test_cannot_show_other_users_task(): void
    {
        $otherUser = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/v1/tasks/' . $task->id);

        $response->assertStatus(404);
    }

    public function test_show_nonexistent_task_returns_404(): void
    {
        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/v1/tasks/99999');

        $response->assertStatus(404);
    }

    public function test_can_create_task(): void
    {
        $response = $this->withHeaders($this->authHeaders())
            ->postJson('/api/v1/tasks', $this->validTaskData);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'title' => 'Test Task Title',
                    'description' => 'Test task description',
                ],
            ])
            ->assertJsonStructure([
                'data' => ['id', 'title', 'description', 'due_date', 'created_at',
                    'status' => ['id', 'name', 'slug'],
                    'priority' => ['id', 'name', 'slug', 'color', 'level'],
                    'category' => ['id', 'name', 'slug'],
                ],
            ]);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Test Task Title',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_create_task_assigns_to_authenticated_user(): void
    {
        $response = $this->withHeaders($this->authHeaders())
            ->postJson('/api/v1/tasks', $this->validTaskData);

        $taskId = $response->json('data.id');
        $this->assertDatabaseHas('tasks', [
            'id' => $taskId,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_create_task_requires_title(): void
    {
        $data = $this->validTaskData;
        unset($data['title']);

        $response = $this->withHeaders($this->authHeaders())
            ->postJson('/api/v1/tasks', $data);

        $response->assertStatus(422);
    }

    public function test_create_task_requires_valid_due_date_format(): void
    {
        $data = $this->validTaskData;
        $data['due_date'] = 'invalid-date';

        $response = $this->withHeaders($this->authHeaders())
            ->postJson('/api/v1/tasks', $data);

        $response->assertStatus(422);
    }

    public function test_create_task_with_nonexistent_status_returns_422(): void
    {
        $data = $this->validTaskData;
        $data['status_id'] = 99999;

        $response = $this->withHeaders($this->authHeaders())
            ->postJson('/api/v1/tasks', $data);

        $response->assertStatus(422);
    }

    public function test_can_update_task(): void
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Original Title',
        ]);

        $updateData = [
            'title' => 'Updated Title',
            'due_date' => now()->addDays(14)->format('Y-m-d\TH:i:s'),
            'status_id' => $this->status->id,
            'priority_id' => $this->priority->id,
            'category_id' => $this->category->id,
        ];

        $response = $this->withHeaders($this->authHeaders())
            ->putJson('/api/v1/tasks/' . $task->id, $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'data' => ['title' => 'Updated Title'],
            ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_update_partial_task(): void
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Original Title',
            'description' => 'Original description',
        ]);

        $response = $this->withHeaders($this->authHeaders())
            ->putJson('/api/v1/tasks/' . $task->id, [
                'title' => 'Only Title Changed',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => ['title' => 'Only Title Changed'],
            ]);
    }

    public function test_cannot_update_other_users_task(): void
    {
        $otherUser = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->withHeaders($this->authHeaders())
            ->putJson('/api/v1/tasks/' . $task->id, [
                'title' => 'Hacked Title',
            ]);

        $response->assertStatus(404);
    }

    public function test_can_delete_task(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders($this->authHeaders())
            ->deleteJson('/api/v1/tasks/' . $task->id);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_cannot_delete_other_users_task(): void
    {
        $otherUser = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->withHeaders($this->authHeaders())
            ->deleteJson('/api/v1/tasks/' . $task->id);

        $response->assertStatus(404);

        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }

    public function test_delete_nonexistent_task_returns_404(): void
    {
        $response = $this->withHeaders($this->authHeaders())
            ->deleteJson('/api/v1/tasks/99999');

        $response->assertStatus(404);
    }

    public function test_requires_token_for_all_endpoints(): void
    {
        $this->getJson('/api/v1/tasks')->assertStatus(401);
        $this->postJson('/api/v1/tasks', $this->validTaskData)->assertStatus(401);
        $this->getJson('/api/v1/tasks/1')->assertStatus(401);
        $this->putJson('/api/v1/tasks/1', $this->validTaskData)->assertStatus(401);
        $this->deleteJson('/api/v1/tasks/1')->assertStatus(401);
    }
}
