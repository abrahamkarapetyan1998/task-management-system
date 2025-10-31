<?php

namespace App\Http\Controllers\Api;

use App\Enums\TaskStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Redis;

class TaskController extends Controller
{
    private TaskService $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->all();
        if (empty(Cache::has("tasks"))) {
            $tasks = $this->taskService->filterQuery($filters);
            Cache::set('tasks', TaskResource::collection($tasks));
        } else {
            $tasks = Cache::get('tasks');
        }

        return  TaskResource::collection($tasks);
    }

    /**
     * Store a newly created resource in storage.
     * @throws ValidationException
     */
    public function store(Request $request): TaskResource
    {
        $data = $request->all();

        $this->taskService->validateAvailability($data['user_id'], $data['start_date'], $data['end_date']);

        $task = Task::create($data);
        Cache::put('tasks', TaskResource::collection($task));

        return new TaskResource($task);
    }


    public function userTasks(User $user): TaskResource
    {
        $tasks = $user->tasks()->with('user')->get();

        return new TaskResource($tasks);
    }

    /**
     * Update the specified resource in storage.
     * @throws ValidationException
     */

    public function update(Request $request, Task $task): TaskResource
    {
        $data = $request->all();

        $newUserId = $data['user_id'] ?? $task->user_id;
        $newStart  = isset($data['start_date']) ? Carbon::parse($data['start_date']) : $task->start_date;
        $newEnd    = isset($data['end_date']) ? Carbon::parse($data['end_date']) : $task->end_date;

        $currentStart = Carbon::parse($task->start_date);
        $currentEnd   = Carbon::parse($task->end_date);

        if ($newUserId != $task->user_id ||
            !$newStart->eq($currentStart) ||
            !$newEnd->eq($currentEnd)
        ) {
            $this->taskService->validateAvailability($newUserId, $newStart, $newEnd);
        }

        $task->update($data);
        $task->save();

        return new TaskResource($task);
    }



    public function destroy(Task $task)
    {
        $task->delete();

        Cache::forget('tasks');

        return response()->json(['message' => 'Task deleted']);
    }
}
