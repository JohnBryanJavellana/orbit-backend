<?php

namespace App\Http\Requests\Administrator\Project;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateOrUpdateTask extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'projectCtrl' => ['required', 'exists:projects,ctrl'],
            'name' => ['required', 'string'],
            'task_completion_points' => ['required', 'numeric', 'min:1', Rule::when($this->user()->role === "ADMINISTRATOR", ['max:100'], ['nullable'])],
            'task_progress_points' => ['required', 'numeric', 'min:1', Rule::when($this->user()->role === "ADMINISTRATOR", ['max:10'], ['nullable'])],
            'description' => ['required', 'string'],
            'httpMethod' => ['required', 'string', 'in:POST,UPDATE'],
            'documentId' => ['required_if:httpMethod,UPDATE', 'exists:tasks,id']
        ];
    }
}
