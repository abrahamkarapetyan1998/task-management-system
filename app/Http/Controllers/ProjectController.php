<?php

namespace App\Http\Controllers;

use App\Http\Requests\Projects\AssignProjectMemberRequest;
use App\Http\Requests\Projects\ProjectStoreRequest;
use App\Http\Requests\Projects\ProjectUpdateRequest;
use App\Http\Resources\ProjectResource;
use App\Services\ProjectService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProjectController extends Controller
{
    private ProjectService $projectService;
    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->per_page ?? 15;
        $projects = $this->projectService->getUserProjects(userId: Auth::user()->id, perPage: $perPage);

        return ProjectResource::collection($projects);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProjectStoreRequest $request)
    {
        $project = $this->projectService->create(data: $request->toArray(), userId: Auth::user()->id);

        return new ProjectResource($project);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $project = $this->projectService->getUserOneProject($id);

        return new ProjectResource($project);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProjectUpdateRequest $request, string $id)
    {
        $project = $this->projectService->update(
            projectId: $id,
            userId: Auth::user()->id,
            data: $request->toArray(),
        );

        return new ProjectResource($project);
    }

    public function assignToUser(Request $request, string $userId)
    {
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $this->projectService->delete(
            projectId: $id,
            userId: Auth::user()->id,
        );

        return response()->json([
            'message' => 'Project deleted successfully.',
        ]);
    }

    public function addMember(AssignProjectMemberRequest $request, int $project): JsonResponse
    {
        $project = $this->projectService->addMember(
            projectId: $project,
            creatorId: Auth::user()->id,
            userId: $request->integer('user_id'),
        );

        return response()->json([
            'message' => 'User added to project successfully.',
            'data' => new ProjectResource($project),
        ]);
    }

    public function removeMember(Request $request, int $project, int $user): JsonResponse
    {
        $project = $this->projectService->removeMember(
            projectId: $project,
            creatorId: $request->user()->id,
            userId: $user,
        );

        return response()->json([
            'message' => 'User removed from project successfully.',
            'data' => new ProjectResource($project),
        ]);
    }
}
