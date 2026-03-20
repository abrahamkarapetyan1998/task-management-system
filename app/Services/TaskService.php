<?php

namespace App\Services;

use App\Models\Task;
use App\Repository\ProjectRepository;
use App\Repository\TaskRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

readonly class TaskService
{
    public function __construct(
        private TaskRepository $taskRepository,
        private ProjectRepository $projectRepository,
    ) {
    }

    public function paginateForUser(int $userId, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $cacheKey = sprintf(
            'tasks:user:%d:%s:%d',
            $userId,
            md5(json_encode($filters)),
            $perPage
        );

        return Cache::remember(
            $cacheKey,
            now()->addMinutes(5),
            fn () => $this->taskRepository->paginateForUser($userId, $filters, $perPage)
        );
    }

    public function showForUser(int $userId, int $taskId): Task
    {
        return $this->taskRepository->findOwnedByUserOrFail($taskId, $userId);
    }

    public function createForUser(int $userId, array $data): Task
    {
        $project = $this->projectRepository->findCreatedByUserOrFail(
            $data['project_id'],
            $userId,
        );
        if (
            !empty($data['assigned_user_id']) &&
            !$this->canAssignUserToProject($project, (int) $data['assigned_user_id'])
        ) {
            throw ValidationException::withMessages([
                'assigned_user_id' => 'The selected user is not a member of this project.',
            ]);
        }

        $task = $this->taskRepository->create($data);

        $this->clearUserTaskCache($userId);

        return $task;
    }

    public function updateForUser(int $userId, int $taskId, array $data): Task
    {
        $task = $this->taskRepository->findOwnedByUserOrFail($taskId, $userId);

        if (
            !empty($data['assigned_user_id']) &&
            !$this->canAssignUserToProject($task->project, (int) $data['assigned_user_id'])
        ) {
            throw ValidationException::withMessages([
                'assigned_user_id' => 'The selected user is not a member of this project.',
            ]);
        }

        $task = $this->taskRepository->update($task, $data);

        $this->clearUserTaskCache($userId);

        return $task;
    }

    public function deleteForUser(int $userId, int $taskId): void
    {
        $task = $this->taskRepository->findOwnedByUserOrFail($taskId, $userId);

        $this->taskRepository->delete($task);

        $this->clearUserTaskCache($userId);
    }

    private function canAssignUserToProject($project, int $userId): bool
    {
        if ($project->created_user_id === $userId) {
            return true;
        }

        return $project->members()
            ->where('users.id', $userId)
            ->exists();
    }

    private function clearUserTaskCache(int $userId): void
    {
        $keys = Cache::get("tasks_cache_keys:user:{$userId}", []);

        foreach ($keys as $key) {
            Cache::forget($key);
        }

        Cache::forget("tasks_cache_keys:user:{$userId}");
    }
}
