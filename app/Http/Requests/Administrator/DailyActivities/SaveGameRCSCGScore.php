<?php

namespace App\Http\Requests\Administrator\DailyActivities;

use Illuminate\Foundation\Http\FormRequest;

class SaveGameRCSCGScore extends FormRequest
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
            'score' => ['required'],
            'gameService' => ['required', 'string', 'in:cup_shuffle,daily_roulette,color_game,plinko_game'],
        ];
    }
}
