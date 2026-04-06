<?php

namespace App\Http\Requests\Administrator\Announcement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateOrUpdateAnnouncement extends FormRequest
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
            'contentText' => ['required_without_all:oldAttachmentIds,newAttachments', 'nullable', 'string'],
            'httpMethod' => ['required', 'string', 'in:POST,UPDATE'],
            'status' => ['required_if:httpMethod,UPDATE', 'nullable', 'in:SHOW,HIDE'],
            'documentId' => ['required_if:httpMethod,UPDATE', 'exists:announcements,id'],
            'oldAttachmentIds' => ['array'],
            'oldAttachmentIds.*' => ['exists:announcement_attachments,id'],
            'newAttachments' => ['array']
        ];
    }
}
