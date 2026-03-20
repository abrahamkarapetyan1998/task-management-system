<?php

namespace App\Http\Controllers;

use App\Http\Requests\Tasks\IndexTaskRequest;
use App\Http\Requests\Tasks\StoreTaskRequest;
use App\Http\Requests\Tasks\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskService $taskService,
    ) {
    }

    public function index(IndexTaskRequest $request): AnonymousResourceCollection
    {
        $tasks = $this->taskService->paginateForUser(
            userId: $request->user()->id,
            filters: $request->validated(),
            perPage: max(1, min(100, (int) $request->integer('per_page', 15))),
        );

        return TaskResource::collection($tasks);
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = $this->taskService->createForUser(
            userId: Auth::user()->id,
            data: $request->validated(),
        );

        return response()->json([
            'message' => 'Task created successfully.',
            'data' => new TaskResource($task),
        ], Response::HTTP_CREATED);
    }

    public function show(Request $request, int $task): TaskResource
    {
        $task = $this->taskService->showForUser(
            userId: $request->user()->id,
            taskId: $task,
        );

        return new TaskResource($task);
    }

    public function update(UpdateTaskRequest $request, int $task): JsonResponse
    {
        $task = $this->taskService->updateForUser(
            userId: $request->user()->id,
            taskId: $task,
            data: $request->validated(),
        );

        return response()->json([
            'message' => 'Task updated successfully.',
            'data' => new TaskResource($task),
        ]);
    }

    public function destroy(Request $request, int $task): JsonResponse
    {
        $this->taskService->deleteForUser(
            userId: $request->user()->id,
            taskId: $task,
        );

        return response()->json([
            'message' => 'Task deleted successfully.',
        ]);
    }
}
