<?php

namespace App\Http\Requests\Administrator\Project;

use Illuminate\Foundation\Http\FormRequest;

class AssignUserAsTaskCollaborator extends FormRequest
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
            'taskCtrl' => ['required', 'exists:tasks,ctrl'],
            'memberId' => ['required', 'exists:users,id', 'unique:members,member_id'],
            'memberRoleId' => ['nullable', 'exists:member_roles,id']
        ];
    }
}
