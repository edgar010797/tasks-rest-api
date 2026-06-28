<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_attributes(): void
    {
        $category = new Category();

        $this->assertEquals(['name', 'slug'], $category->getFillable());
    }

    public function test_uses_correct_table(): void
    {
        $category = new Category();

        $this->assertEquals('categories', $category->getTable());
    }

    public function test_can_be_created(): void
    {
        $category = Category::factory()->create([
            'name' => 'Test Category',
            'slug' => 'test-category',
        ]);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Test Category',
            'slug' => 'test-category',
        ]);
    }

    public function test_named_states(): void
    {
        $work = Category::factory()->work()->create();
        $this->assertEquals('work', $work->slug);

        $home = Category::factory()->home()->create();
        $this->assertEquals('home', $home->slug);

        $personal = Category::factory()->personal()->create();
        $this->assertEquals('personal', $personal->slug);
    }
}
