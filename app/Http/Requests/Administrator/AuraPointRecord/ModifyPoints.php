<?php

namespace App\Http\Requests\Administrator\AuraPointRecord;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ModifyPoints extends FormRequest
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
            'playerId' => ['required', 'exists:users,id'],
            'contentText' => ['required', 'string'],
            'modifyType' => ['required', 'string', 'in:INCREASE,DECREASE'],
            'points' => ['required', 'numeric']
        ];
    }
}
