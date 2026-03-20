<?php

namespace App\Repository;

use App\Models\Project;
use Illuminate\Pagination\LengthAwarePaginator;

class ProjectRepository
{
    public function paginateForCreator(int $creatorId, int $perPage): LengthAwarePaginator
    {
        return Project::query()
            ->where('created_user_id', $creatorId)
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): Project
    {
        return Project::query()->create($data);
    }

    public function update(Project $project, array $attributes): Project
    {
        $project->update($attributes);

        return $project->refresh();
    }

    public function delete(Project $project): void
    {
        $project->delete();
    }

    public function findCreatedByUserOrFail(int $projectId, int $creatorId): Project
    {
        return Project::query()
            ->where('id', $projectId)
            ->where('created_user_id', $creatorId)
            ->firstOrFail();
    }

    public function addMember(Project $project, int $userId): void
    {
        $project->members()->syncWithoutDetaching([$userId]);
    }

    public function removeMember(Project $project, int $userId): void
    {
        $project->members()->detach($userId);
    }

    public function hasMember(Project $project, int $userId): bool
    {
        return $project->members()
            ->where('users.id', $userId)
            ->exists();
    }
}
