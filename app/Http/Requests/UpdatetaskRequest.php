<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Validation Rules
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],

            'status' => [
                'sometimes',
                'in:pending,in_progress,completed'
            ],

            'priority' => [
                'sometimes',
                'in:low,medium,high'
            ],

            'due_date' => ['nullable', 'date'],
        ];
    }

    /**
     * Custom Error Messages
     */
    public function messages(): array
    {
        return [
            'status.in' => 'Invalid task status.',

            'priority.in' => 'Priority must be low, medium, or high.',

            'title.max' => 'Task title cannot exceed 255 characters.',
        ];
    }
}