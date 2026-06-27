<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreTaskRequest;
use App\Http\Requests\Api\V1\UpdateTaskRequest;
use App\Http\Resources\Api\V1\TaskResource;
use App\Services\Interfaces\TaskServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskServiceInterface $taskService
    ) {}

    public function index(): JsonResponse
    {
        $paginator = $this->taskService->list(
            request()->only(['search', 'sort', 'per_page'])
        );

        return (TaskResource::collection($paginator))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function show(int $id): JsonResponse
    {
        $task = $this->taskService->find($id);

        return (new TaskResource($task))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = $this->taskService->create($request->validated());

        return (new TaskResource($task))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(UpdateTaskRequest $request, int $id): JsonResponse
    {
        $task = $this->taskService->update($id, $request->validated());

        return (new TaskResource($task))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function destroy(int $id): Response
    {
        $this->taskService->delete($id);

        return response()->noContent();
    }
}
