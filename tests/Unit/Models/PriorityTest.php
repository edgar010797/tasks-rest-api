<?php

namespace Tests\Unit\Models;

use App\Models\Priority;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PriorityTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_attributes(): void
    {
        $priority = new Priority();

        $this->assertEquals(['name', 'slug', 'color', 'level'], $priority->getFillable());
    }

    public function test_uses_correct_table(): void
    {
        $priority = new Priority();

        $this->assertEquals('priorities', $priority->getTable());
    }

    public function test_can_be_created(): void
    {
        $priority = Priority::factory()->create([
            'name' => 'Test Priority',
            'slug' => 'test-priority',
            'color' => '#ff0000',
            'level' => 1,
        ]);

        $this->assertDatabaseHas('priorities', [
            'id' => $priority->id,
            'name' => 'Test Priority',
            'slug' => 'test-priority',
            'color' => '#ff0000',
            'level' => 1,
        ]);
    }

    public function test_named_states(): void
    {
        $low = Priority::factory()->low()->create();
        $this->assertEquals('low', $low->slug);
        $this->assertEquals(0, $low->level);

        $medium = Priority::factory()->medium()->create();
        $this->assertEquals('medium', $medium->slug);
        $this->assertEquals(1, $medium->level);

        $high = Priority::factory()->high()->create();
        $this->assertEquals('high', $high->slug);
        $this->assertEquals(2, $high->level);
    }
}
