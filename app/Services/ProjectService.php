<?php

namespace App\Services;

use App\Models\Project;
use App\Repository\ProjectRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ProjectService
{
    private ProjectRepository $projectRepository;
    public function __construct(ProjectRepository $projectRepository)
    {
        $this->projectRepository = $projectRepository;
    }

    public function getUserProjects(int $userId, int $perPage): LengthAwarePaginator
    {
        return $this->projectRepository->paginateForCreator($userId, $perPage);
    }

    public function create(array $data, int $userId)
    {
        return $this->projectRepository->create([
            ...$data,
            'created_user_id' => $userId,
        ]);
    }

    public function update(int $projectId, int $userId, array $data): Project
    {
        $project = $this->projectRepository->findCreatedByUserOrFail($projectId, $userId);

        return $this->projectRepository->update($project, $data);
    }

    public function delete(int $projectId, int $userId): void
    {
        $project = $this->projectRepository->findCreatedByUserOrFail($projectId, $userId);

        $this->projectRepository->delete($project);
    }

    public function getUserOneProject(int $id)
    {
        $userId = Auth::user()->id;

        return $this->projectRepository->findCreatedByUserOrFail($id, $userId);
    }


    public function addMember(int $projectId, int $creatorId, int $userId): Project
    {
        $project = $this->projectRepository->findCreatedByUserOrFail($projectId, $creatorId);

        if ($project->created_user_id === $userId) {
            throw ValidationException::withMessages([
                'user_id' => 'Project creator is already part of the project.',
            ]);
        }

        if ($this->projectRepository->hasMember($project, $userId)) {
            throw ValidationException::withMessages([
                'user_id' => 'User is already assigned to this project.',
            ]);
        }

        $this->projectRepository->addMember($project, $userId);

        return $project->load('members');
    }

    public function removeMember(int $projectId, int $creatorId, int $userId): Project
    {
        $project = $this->projectRepository->findCreatedByUserOrFail($projectId, $creatorId);

        if (! $this->projectRepository->hasMember($project, $userId)) {
            throw ValidationException::withMessages([
                'user_id' => 'User is not assigned to this project.',
            ]);
        }

        $this->projectRepository->removeMember($project, $userId);

        return $project->load('members');
    }
}
