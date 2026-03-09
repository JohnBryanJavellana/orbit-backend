<?php

namespace App\Http\Requests\Administrator\Members;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateOrUpdateMember extends FormRequest
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
            'first_name' => ['required', 'string'],
            'middle_name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
            'birthday' => ['required', 'date'],
            'gender' => ['required', 'string', 'in:MALE,FEMALE'],
            'role' => [Rule::when($this->httpMethod === "POST" && \in_array($this->user()->role, ['SUPERADMIN']), ['required', 'string', 'in:ADMINISTRATOR,MEMBER'], ['nullable'])],
            'email' => [ 'required', 'email', $this->httpMethod === "UPDATE" ? Rule::unique('users')->ignore($this->documentId) : Rule::unique('users')],
            'documentId' => [Rule::when($this->httpMethod === "UPDATE", ['required', 'exists:users,id'], ['nullable'])]
        ];
    }
}
