<?php

namespace App\Http\Controllers;

use App\Models\Event;

class SurveyPageController extends Controller
{
    public function chat(Event $event)
    {
        return view('survey.chat', [
            'event' => $event->only('id', 'name', 'description'),
            'booth' => null,
            'visitorId' => request()->integer('visitor_id') ?: null,
        ]);
    }

    public function chatWithBooth(Event $event, $boothId)
    {
        $booth = $event->booths()->findOrFail($boothId);

        return view('survey.chat', [
            'event' => $event->only('id', 'name', 'description'),
            'booth' => $booth->only('id', 'name'),
            'visitorId' => request()->integer('visitor_id') ?: null,
        ]);
    }

    public function complete(Event $event)
    {
        // TODO: Implement Blade view for survey complete
        return view('survey.complete', [
            'event' => $event->only('id', 'name'),
        ]);
    }

    public function register(Event $event)
    {
        return view('survey.register', [
            'event' => $event->only('id', 'name', 'description'),
        ]);
    }

    public function registerComplete(Event $event)
    {
        return view('survey.register-complete', [
            'event' => $event->only('id', 'name'),
        ]);
    }
}
