<?php

namespace App\Http\Requests\Administrator\Border;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateOrUpdateBorder extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->role === "SUPERADMIN";
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:FREE,RARE'],
            'httpMethod' => ['required', 'string', 'in:POST,UPDATE'],
            'border' => ['string', Rule::when($this->httpMethod === "POST", ['required'], ['nullable'])],
            'documentId' => [Rule::when($this->httpMethod === "UPDATE", ['required', 'exists:custom_borders,id'], ['nullable'])]
        ];
    }
}
