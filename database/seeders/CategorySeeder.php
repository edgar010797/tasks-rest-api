<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        Category::factory()->work()->create();
        Category::factory()->home()->create();
        Category::factory()->personal()->create();
    }
}
