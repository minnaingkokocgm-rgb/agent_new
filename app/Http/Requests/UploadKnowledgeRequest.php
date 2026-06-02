<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadKnowledgeRequest extends FormRequest
{
    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'min:10'],
            'source_name' => ['required', 'string', 'max:255'],
            'booth_id' => ['nullable', 'integer', 'exists:booths,id'],
        ];
    }
}
