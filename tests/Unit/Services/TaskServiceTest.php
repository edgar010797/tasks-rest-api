<?php

namespace Tests\Unit\Services;

use App\Models\Category;
use App\Models\Priority;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class TaskServiceTest extends TestCase
{
    use RefreshDatabase;

    private TaskService $taskService;
    private User $user;
    private Status $status;
    private Priority $priority;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->taskService = new TaskService();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->status = Status::factory()->create();
        $this->priority = Priority::factory()->create();
        $this->category = Category::factory()->create();
    }

    public function test_list_returns_paginated_tasks(): void
    {
        Task::factory()->count(15)->create([
            'user_id' => $this->user->id,
            'status_id' => $this->status->id,
            'priority_id' => $this->priority->id,
            'category_id' => $this->category->id,
        ]);

        $result = $this->taskService->list([]);

        $this->assertCount(10, $result->items());
        $this->assertEquals(15, $result->total());
        $this->assertEquals(1, $result->currentPage());
    }

    public function test_list_with_custom_per_page(): void
    {
        Task::factory()->count(15)->create([
            'user_id' => $this->user->id,
            'status_id' => $this->status->id,
            'priority_id' => $this->priority->id,
            'category_id' => $this->category->id,
        ]);

        $result = $this->taskService->list(['per_page' => 5]);

        $this->assertCount(5, $result->items());
    }

    public function test_list_filters_by_authenticated_user(): void
    {
        Task::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'status_id' => $this->status->id,
            'priority_id' => $this->priority->id,
            'category_id' => $this->category->id,
        ]);

        $otherUser = User::factory()->create();
        Task::factory()->count(5)->create([
            'user_id' => $otherUser->id,
            'status_id' => $this->status->id,
            'priority_id' => $this->priority->id,
            'category_id' => $this->category->id,
        ]);

        $result = $this->taskService->list([]);

        $this->assertCount(3, $result->items());
    }

    public function test_list_with_search(): void
    {
        Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Find This Specific Task',
            'description' => 'irrelevant',
            'status_id' => $this->status->id,
            'priority_id' => $this->priority->id,
            'category_id' => $this->category->id,
        ]);
        Task::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'title' => 'Other Tasks',
            'status_id' => $this->status->id,
            'priority_id' => $this->priority->id,
            'category_id' => $this->category->id,
        ]);

        $result = $this->taskService->list(['search' => 'Find This']);

        $this->assertCount(1, $result->items());
        $this->assertEquals('Find This Specific Task', $result->items()[0]->title);
    }

    public function test_list_with_search_by_description(): void
    {
        Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Generic Title',
            'description' => 'Unique description content here',
            'status_id' => $this->status->id,
            'priority_id' => $this->priority->id,
            'category_id' => $this->category->id,
        ]);

        $result = $this->taskService->list(['search' => 'Unique description']);

        $this->assertCount(1, $result->items());
    }

    public function test_list_default_sort_is_by_due_date(): void
    {
        $task1 = Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Later Task',
            'due_date' => now()->addDays(30)->format('Y-m-d\TH:i:s'),
            'status_id' => $this->status->id,
            'priority_id' => $this->priority->id,
            'category_id' => $this->category->id,
        ]);
        $task2 = Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Earlier Task',
            'due_date' => now()->addDays(1)->format('Y-m-d\TH:i:s'),
            'status_id' => $this->status->id,
            'priority_id' => $this->priority->id,
            'category_id' => $this->category->id,
        ]);

        $result = $this->taskService->list([]);

        $this->assertEquals('Earlier Task', $result->items()[0]->title);
        $this->assertEquals('Later Task', $result->items()[1]->title);
    }

    public function test_list_with_sort_by_created_at(): void
    {
        $oldTask = Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Old Task',
            'due_date' => now()->addDays(10)->format('Y-m-d\TH:i:s'),
            'created_at' => now()->subDays(5),
            'status_id' => $this->status->id,
            'priority_id' => $this->priority->id,
            'category_id' => $this->category->id,
        ]);
        $newTask = Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'New Task',
            'due_date' => now()->addDays(5)->format('Y-m-d\TH:i:s'),
            'created_at' => now(),
            'status_id' => $this->status->id,
            'priority_id' => $this->priority->id,
            'category_id' => $this->category->id,
        ]);

        $result = $this->taskService->list(['sort' => 'created_at']);

        $this->assertEquals('Old Task', $result->items()[0]->title);
        $this->assertEquals('New Task', $result->items()[1]->title);
    }

    public function test_find_returns_task(): void
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Findable Task',
            'status_id' => $this->status->id,
            'priority_id' => $this->priority->id,
            'category_id' => $this->category->id,
        ]);

        $found = $this->taskService->find($task->id);

        $this->assertEquals($task->id, $found->id);
        $this->assertEquals('Findable Task', $found->title);
    }

    public function test_find_throws_exception_for_missing_task(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->taskService->find(99999);
    }

    public function test_find_throws_exception_for_other_users_task(): void
    {
        $otherUser = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $otherUser->id]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->taskService->find($task->id);
    }

    public function test_create_creates_task(): void
    {
        $data = [
            'title' => 'Newly Created Task',
            'description' => 'Description text',
            'due_date' => now()->addDays(5)->format('Y-m-d\TH:i:s'),
            'status_id' => $this->status->id,
            'priority_id' => $this->priority->id,
            'category_id' => $this->category->id,
        ];

        $task = $this->taskService->create($data);

        $this->assertEquals('Newly Created Task', $task->title);
        $this->assertEquals($this->user->id, $task->user_id);
        $this->assertDatabaseHas('tasks', ['title' => 'Newly Created Task', 'user_id' => $this->user->id]);
    }

    public function test_create_loads_relations(): void
    {
        $data = [
            'title' => 'Task With Relations',
            'due_date' => now()->addDays(3)->format('Y-m-d\TH:i:s'),
            'status_id' => $this->status->id,
            'priority_id' => $this->priority->id,
            'category_id' => $this->category->id,
        ];

        $task = $this->taskService->create($data);

        $this->assertTrue($task->relationLoaded('status'));
        $this->assertTrue($task->relationLoaded('priority'));
        $this->assertTrue($task->relationLoaded('category'));
    }

    public function test_update_updates_task(): void
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Old Title',
            'status_id' => $this->status->id,
            'priority_id' => $this->priority->id,
            'category_id' => $this->category->id,
        ]);

        $updated = $this->taskService->update($task->id, [
            'title' => 'New Title',
        ]);

        $this->assertEquals('New Title', $updated->title);
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'title' => 'New Title']);
    }

    public function test_update_throws_exception_for_other_users_task(): void
    {
        $otherUser = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $otherUser->id]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->taskService->update($task->id, ['title' => 'Hacked']);
    }

    public function test_update_returns_fresh_data_with_relations(): void
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Original',
            'status_id' => $this->status->id,
            'priority_id' => $this->priority->id,
            'category_id' => $this->category->id,
        ]);

        $updated = $this->taskService->update($task->id, ['title' => 'Updated']);

        $this->assertTrue($updated->relationLoaded('status'));
        $this->assertTrue($updated->relationLoaded('priority'));
        $this->assertTrue($updated->relationLoaded('category'));
    }

    public function test_delete_deletes_task(): void
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'status_id' => $this->status->id,
            'priority_id' => $this->priority->id,
            'category_id' => $this->category->id,
        ]);

        $this->taskService->delete($task->id);

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_delete_throws_exception_for_other_users_task(): void
    {
        $otherUser = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $otherUser->id]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->taskService->delete($task->id);
    }

    public function test_list_uses_cache_on_subsequent_calls(): void
    {
        Task::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'status_id' => $this->status->id,
            'priority_id' => $this->priority->id,
            'category_id' => $this->category->id,
        ]);

        $result1 = $this->taskService->list([]);
        $result2 = $this->taskService->list([]);

        $this->assertSameSize($result1->items(), $result2->items());
        $this->assertEquals($result1->total(), $result2->total());

        $cacheKey = 'tasks:list:' . $this->user->id . ':' . md5(json_encode([]) . '1');
        $this->assertTrue(Cache::tags(['user:' . $this->user->id])->has($cacheKey));
    }

    public function test_list_cache_is_flushed_after_create(): void
    {
        Task::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'status_id' => $this->status->id,
            'priority_id' => $this->priority->id,
            'category_id' => $this->category->id,
        ]);

        $this->taskService->list([]);

        $this->taskService->create([
            'title' => 'New Task After Cache',
            'due_date' => now()->addDays(3)->format('Y-m-d\TH:i:s'),
            'status_id' => $this->status->id,
            'priority_id' => $this->priority->id,
            'category_id' => $this->category->id,
        ]);

        $result = $this->taskService->list([]);
        $this->assertCount(4, $result->items());
    }

    public function test_find_uses_cache_on_subsequent_calls(): void
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Cached Task',
            'status_id' => $this->status->id,
            'priority_id' => $this->priority->id,
            'category_id' => $this->category->id,
        ]);

        $result1 = $this->taskService->find($task->id);
        $result2 = $this->taskService->find($task->id);

        $this->assertEquals($result1->id, $result2->id);
        $this->assertEquals('Cached Task', $result2->title);

        $cacheKey = 'tasks:find:' . $this->user->id . ':' . $task->id;
        $this->assertTrue(Cache::tags(['task:' . $task->id])->has($cacheKey));
    }

    public function test_find_cache_is_flushed_after_update(): void
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Original For Cache',
            'status_id' => $this->status->id,
            'priority_id' => $this->priority->id,
            'category_id' => $this->category->id,
        ]);

        $this->taskService->find($task->id);

        $this->taskService->update($task->id, ['title' => 'Updated After Cache']);

        $result = $this->taskService->find($task->id);
        $this->assertEquals('Updated After Cache', $result->title);

        $cacheKey = 'tasks:find:' . $this->user->id . ':' . $task->id;
        $value = Cache::tags(['task:' . $task->id])->get($cacheKey);
        $this->assertNotNull($value);
        $this->assertEquals('Updated After Cache', $value->title);
    }

    public function test_find_cache_is_flushed_after_delete(): void
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'status_id' => $this->status->id,
            'priority_id' => $this->priority->id,
            'category_id' => $this->category->id,
        ]);

        $this->taskService->find($task->id);

        $this->taskService->delete($task->id);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $this->taskService->find($task->id);
    }
}
