<?php

namespace App\Services;

use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class TaskService
{
    /**
     * @throws ValidationException
     */
    public function validateAvailability($userId, $start, $end)
    {
        $start = Carbon::parse($start);
        $end = Carbon::parse($end);
        $now = Carbon::now();

        if ($start->lt($now)) {
            throw ValidationException::withMessages([
                'start_date' => ['Start date cannot be in the past.']
            ]);
        }

        if ($end->lt($start)) {
            throw ValidationException::withMessages([
                'end_date' => ['End date cannot be before start date.']
            ]);
        }

        $conflict = Task::where('user_id', $userId)
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('end_date', [$start, $end])
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->where('start_date', '<=', $start)
                            ->where('end_date', '>=', $end);
                    });
            });

        if ($conflict->exists()) {
            throw ValidationException::withMessages([
                'time_range' => ['User already has a task in this time range.']
            ]);
        }
    }


    public function filterQuery(array $filters)
    {
        $tasks = Task::query()
            ->when($filters['status'] ?? null, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($filters['user_id'] ?? null, function ($query, $assigneeId) {
                $query->where('user_id', $assigneeId);
            })
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($filters['start_date'] ?? null, function ($query, $dueDate) {
                $query->whereDate('end_date', $dueDate);
            })
            ->orderByDesc('created_at')
            ->with('user')
            ->get()
        ;

        return $tasks;
    }
}
