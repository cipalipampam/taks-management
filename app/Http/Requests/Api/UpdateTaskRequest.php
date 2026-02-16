<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('update', $this->route('task'));
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'string', 'in:todo,doing,done'],
            'deadline' => ['nullable', 'date'],
            'assignees' => ['sometimes', 'array'],
            'assignees.*' => ['integer', 'exists:users,id'],
        ];
    }
}
