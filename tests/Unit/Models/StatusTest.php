<?php

namespace Tests\Unit\Models;

use App\Models\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_attributes(): void
    {
        $status = new Status();

        $this->assertEquals(['name', 'slug'], $status->getFillable());
    }

    public function test_uses_correct_table(): void
    {
        $status = new Status();

        $this->assertEquals('statuses', $status->getTable());
    }

    public function test_can_be_created(): void
    {
        $status = Status::factory()->create([
            'name' => 'Test Status',
            'slug' => 'test-status',
        ]);

        $this->assertDatabaseHas('statuses', [
            'id' => $status->id,
            'name' => 'Test Status',
            'slug' => 'test-status',
        ]);
    }
}
