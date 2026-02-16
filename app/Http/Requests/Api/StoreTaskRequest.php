<?php

namespace App\Http\Requests\Api;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('create', Task::class);
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'in:todo,doing,done'],
            'deadline' => ['nullable', 'date'],
            'assignees' => ['nullable', 'array'],
            'assignees.*' => ['integer', 'exists:users,id'],
        ];
    }
}
