<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Status;

class StatusSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        Status::factory()->completed()->create();
        Status::factory()->pending()->create();
    }
}
