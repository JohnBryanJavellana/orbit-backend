<?php

namespace App\Http\Requests\Administrator\Account;

use Illuminate\Foundation\Http\FormRequest;

class CreateNote extends FormRequest
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
            'note' => ['required_without_all:music', 'nullable', 'string', 'max:50'],
            'music' => ['required_without_all:note', 'nullable', 'json']
        ];
    }
}
