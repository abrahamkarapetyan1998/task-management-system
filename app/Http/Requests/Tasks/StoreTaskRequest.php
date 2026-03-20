<?php

namespace App\Http\Requests\Tasks;

use App\Enums\TaskStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreTaskRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'project_id' => 'required|integer|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'status' => ['required', new Enum(TaskStatusEnum::class)],
            'due_date' => 'required|date',
            'assigned_user_id' => 'integer|exists:users,id',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
