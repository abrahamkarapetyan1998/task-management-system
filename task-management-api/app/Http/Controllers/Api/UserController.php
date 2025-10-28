<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->get('query', '');

        $users = User::where('name', 'like', "%{$query}%")
            ->limit(10)
            ->get(['id', 'name']);

        return UserResource::collection($users);
    }
}
