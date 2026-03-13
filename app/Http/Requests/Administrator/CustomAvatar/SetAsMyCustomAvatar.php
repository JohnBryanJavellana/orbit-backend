<?php

namespace App\Http\Requests\Administrator\CustomAvatar;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SetAsMyCustomAvatar extends FormRequest
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
            'using' => ['required', 'string', 'in:MAIN,CUSTOM'],
            'userCustomAvatarId' => ['string', 'required', 'exists:user_custom_avatars,id'],
            'avatarId' => [Rule::when($this->using !== "MAIN", ['required', 'exists:custom_avatars,id'], ['required'])]
        ];
    }
}
