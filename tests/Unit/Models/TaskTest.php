<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\Priority;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Status $status;
    private Priority $priority;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->status = Status::factory()->create();
        $this->priority = Priority::factory()->create();
        $this->category = Category::factory()->create();
    }

    public function test_fillable_attributes(): void
    {
        $task = new Task();

        $this->assertEquals([
            'title',
            'description',
            'due_date',
            'created_at',
            'status_id',
            'priority_id',
            'category_id',
            'user_id',
        ], $task->getFillable());
    }

    public function test_casts(): void
    {
        $task = new Task();

        $this->assertEquals('datetime', $task->getCasts()['due_date']);
        $this->assertEquals('datetime', $task->getCasts()['created_at']);
    }

    public function test_uses_correct_table(): void
    {
        $task = new Task();

        $this->assertEquals('tasks', $task->getTable());
    }

    public function test_belongs_to_user(): void
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'status_id' => $this->status->id,
            'priority_id' => $this->priority->id,
            'category_id' => $this->category->id,
        ]);

        $this->assertInstanceOf(User::class, $task->user);
        $this->assertEquals($this->user->id, $task->user->id);
    }

    public function test_belongs_to_status(): void
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'status_id' => $this->status->id,
            'priority_id' => $this->priority->id,
            'category_id' => $this->category->id,
        ]);

        $this->assertInstanceOf(Status::class, $task->status);
        $this->assertEquals($this->status->id, $task->status->id);
    }

    public function test_belongs_to_priority(): void
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'status_id' => $this->status->id,
            'priority_id' => $this->priority->id,
            'category_id' => $this->category->id,
        ]);

        $this->assertInstanceOf(Priority::class, $task->priority);
        $this->assertEquals($this->priority->id, $task->priority->id);
    }

    public function test_belongs_to_category(): void
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'status_id' => $this->status->id,
            'priority_id' => $this->priority->id,
            'category_id' => $this->category->id,
        ]);

        $this->assertInstanceOf(Category::class, $task->category);
        $this->assertEquals($this->category->id, $task->category->id);
    }

    public function test_search_scope_finds_by_title(): void
    {
        Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Unique Searchable Title',
            'status_id' => $this->status->id,
            'priority_id' => $this->priority->id,
            'category_id' => $this->category->id,
        ]);

        $results = Task::search('Unique Searchable')->get();

        $this->assertCount(1, $results);
    }

    public function test_search_scope_finds_by_description(): void
    {
        Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Random Title',
            'description' => 'Specific description content',
            'status_id' => $this->status->id,
            'priority_id' => $this->priority->id,
            'category_id' => $this->category->id,
        ]);

        $results = Task::search('Specific description')->get();

        $this->assertCount(1, $results);
    }

    public function test_search_scope_returns_all_when_empty(): void
    {
        Task::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'status_id' => $this->status->id,
            'priority_id' => $this->priority->id,
            'category_id' => $this->category->id,
        ]);

        $results = Task::search(null)->get();

        $this->assertCount(3, $results);
    }

    public function test_search_scope_is_case_insensitive(): void
    {
        Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'CamelCase Title',
            'status_id' => $this->status->id,
            'priority_id' => $this->priority->id,
            'category_id' => $this->category->id,
        ]);

        $results = Task::search('camelcase')->get();

        $this->assertCount(1, $results);
    }

    public function test_due_date_is_carbon_instance(): void
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'due_date' => now()->addDays(3)->format('Y-m-d\TH:i:s'),
            'status_id' => $this->status->id,
            'priority_id' => $this->priority->id,
            'category_id' => $this->category->id,
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $task->due_date);
    }

    public function test_created_at_is_carbon_instance(): void
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'status_id' => $this->status->id,
            'priority_id' => $this->priority->id,
            'category_id' => $this->category->id,
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $task->created_at);
    }
}
