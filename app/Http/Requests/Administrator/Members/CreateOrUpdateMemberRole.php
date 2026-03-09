<?php

namespace App\Http\Requests\Administrator\Members;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateOrUpdateMemberRole extends FormRequest
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
            'role' => ['required', 'string'],
            'httpMethod' => ['required', 'string', 'in:POST,UPDATE'],
            'documentId' => [Rule::when($this->httpMethod === "UPDATE", ['required', 'exists:member_roles,id'], ['nullable'])]
        ];
    }
}
