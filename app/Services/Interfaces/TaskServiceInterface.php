<?php

namespace App\Services\Interfaces;

use App\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TaskServiceInterface
{
    public function list(array $filters): LengthAwarePaginator;
    public function find(int $id): Task;
    public function create(array $data): Task;
    public function update(int $id, array $data): Task;
    public function delete(int $id): void;
}
