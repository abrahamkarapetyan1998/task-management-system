<?php

namespace App\Http\Requests\Comments;

use Illuminate\Foundation\Http\FormRequest;

class CommentStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'content' => 'required|string|min:3|max:255',
            'task_id' => 'required|integer|exists:tasks,id',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
