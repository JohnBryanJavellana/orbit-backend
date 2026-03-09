<?php

namespace App\Http\Requests\Administrator\Project;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskProgress extends FormRequest
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
            'progressId' => ['required', 'exists:task_progress,id'],
            'status' => ['required', 'in:PENDING,VERIFIED,NOT WORKING PROPERLY,DECLINED'],
            'remarks' => [Rule::when($this->status === 'DECLINED', ['required', 'string'], ['nullable'])],
        ];
    }
}
