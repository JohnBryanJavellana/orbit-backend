<?php

namespace App\Http\Requests\Administrator\Border;

use Illuminate\Foundation\Http\FormRequest;

class RemoveUserRareBorder extends FormRequest
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
            'userId' => ['required', 'exists:users,id'],
            'borderId' => ['required', 'exists:user_border_invs,id']
        ];
    }
}
