<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StartSurveyRequest extends FormRequest
{
    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'event_id' => ['required', 'integer', 'exists:events,id'],
            'booth_id' => ['nullable', 'integer', 'exists:booths,id'],
            'visitor_id' => ['nullable', 'integer', 'exists:visitors,id'],
        ];
    }
}
