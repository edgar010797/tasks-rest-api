<?php

namespace App\Services;

use App\Models\Task;
use App\Services\Interfaces\TaskServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class TaskService implements TaskServiceInterface
{
    public function list(array $filters): LengthAwarePaginator
    {
        $userId = auth()->id();
        $page = request()->get('page', 1);
        $key = 'tasks:list:' . $userId . ':' . md5(json_encode($filters) . $page);

        return Cache::tags(['user:' . $userId])->remember($key, now()->addMinutes(10), function () use ($filters, $userId) {
            $query = Task::with(['status', 'priority', 'category'])
                ->where('user_id', $userId)
                ->search($filters['search'] ?? null);

            $query->orderBy($this->resolveSortField($filters['sort'] ?? null));
            $perPage = max(1, min((int)($filters['per_page'] ?? 10), 100));

            return $query->paginate($perPage);
        });
    }

    public function find(int $id): Task
    {
        $userId = auth()->id();
        $key = "tasks:find:{$userId}:{$id}";

        return Cache::tags(['task:' . $id])->remember($key, config('cache.cacheDuration'), function () use ($id, $userId) {
            return Task::with(['status', 'priority', 'category'])
                ->where('user_id', $userId)
                ->findOrFail($id);
        });
    }

    public function create(array $data): Task
    {
        $data['user_id'] = auth()->id();

        $task = Task::create($data);
        $result = $task->load(['status', 'priority', 'category']);

        Cache::tags(['user:' . auth()->id()])->flush();

        return $result;
    }

    public function update(int $id, array $data): Task
    {
        $task = Task::query()
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $task->update($data);
        $result = $task->fresh(['status', 'priority', 'category']);

        Cache::tags(['task:' . $id])->flush();
        Cache::tags(['user:' . auth()->id()])->flush();

        return $result;
    }

    public function delete(int $id): void
    {
        $task = Task::query()
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $task->delete();

        Cache::tags(['task:' . $id])->flush();
        Cache::tags(['user:' . auth()->id()])->flush();
    }

    private function resolveSortField(?string $sort): string
    {
        $allowed = ['due_date', 'created_at'];

        return in_array($sort, $allowed, true) ? $sort : 'due_date';
    }
}
