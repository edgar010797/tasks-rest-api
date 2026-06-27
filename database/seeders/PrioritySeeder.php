<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Priority;

class PrioritySeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        Priority::factory()->low()->create();
        Priority::factory()->medium()->create();
        Priority::factory()->high()->create();
    }
}
