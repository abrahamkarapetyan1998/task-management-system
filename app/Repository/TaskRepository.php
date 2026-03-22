<?php

namespace App\Repository;

use App\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TaskRepository
{
    public function paginateForUser(int $userId, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return Task::query()
            ->whereHas('project', fn ($query) => $query->where('created_user_id', $userId))
            ->when(
                $filters['status'] ?? null,
                fn ($query, $status) => $query->where('status', $status)
            )
            ->when(
                $filters['due_date'] ?? null,
                fn ($query, $dueDate) => $query->whereDate('due_date', $dueDate)
            )
            ->when(
                $filters['search'] ?? null,
                fn ($query, $search) => $query->whereFullText(['title', 'description'], $search)
            )
            ->latest()
            ->paginate($perPage);
    }

    public function findOwnedByUserOrFail(int $taskId, int $userId): Task
    {
        return Task::query()
            ->with(['comments.user'])
            ->whereKey($taskId)
            ->whereHas('project', fn ($query) => $query->where('created_user_id', $userId))
            ->firstOrFail();
    }

    public function create(array $attributes): Task
    {
        return Task::query()->create($attributes);
    }

    public function update(Task $task, array $attributes): Task
    {
        $task->update($attributes);

        return $task->refresh();
    }

    public function delete(Task $task): void
    {
        $task->delete();
    }
}
