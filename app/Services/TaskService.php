<?php

namespace App\Services;

use App\Models\Task;
use App\Services\Interfaces\TaskServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TaskService implements TaskServiceInterface
{
    public function list(array $filters): LengthAwarePaginator
    {
        $query = Task::with(['status', 'priority', 'category'])
            ->where('user_id', auth()->id())
            ->search($filters['search'] ?? null);

        $query->orderBy(
            $this->resolveSortField($filters['sort'] ?? null)
        );

        $perPage = max(1, min((int)($filters['per_page'] ?? 10), 100));

        return $query->paginate($perPage);
    }

    public function find(int $id): Task
    {
        $task = Task::with(['status', 'priority', 'category'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        return $task;
    }

    public function create(array $data): Task
    {
        $data['user_id'] = auth()->id();

        $task = Task::create($data);
        return $task->load(['status', 'priority', 'category']);
    }

    public function update(int $id, array $data): Task
    {
        $task = Task::query()
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $task->update($data);

        return $task->fresh(['status', 'priority', 'category']);
    }

    public function delete(int $id): void
    {
        $task = Task::query()
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $task->delete();
    }

    private function resolveSortField(?string $sort): string
    {
        $allowed = ['due_date', 'created_at'];

        return in_array($sort, $allowed, true) ? $sort : 'due_date';
    }
}
