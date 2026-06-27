<?php

namespace Database\Seeders;

use App\Models\Task;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TaskSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            StatusSeeder::class,
            PrioritySeeder::class,
            CategorySeeder::class,
            UserSeeder::class,
        ]);

        Task::factory()->count(200)->ofUser('admin@email.ru')->create();
        Task::factory()->ofUser('admin@email.ru')->title('Какая то задача')->create();
        Task::factory()->count(1000)->create();
    }
}
