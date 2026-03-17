<?php

namespace App\Http\Requests\Administrator\Notification;

use Illuminate\Foundation\Http\FormRequest;

class SetAsRed extends FormRequest
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
            'documentId' => ['required', 'exists:notifications,id']
        ];
    }
}
