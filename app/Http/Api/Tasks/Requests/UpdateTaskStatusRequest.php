<?php

namespace App\Http\Api\Tasks\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateTaskStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('updateStatus', $this->route('task'));
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:todo,doing,done'],
        ];
    }
}
