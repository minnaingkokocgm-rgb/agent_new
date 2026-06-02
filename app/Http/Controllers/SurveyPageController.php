<?php

namespace App\Http\Controllers;

use App\Models\Event;

class SurveyPageController extends Controller
{
    public function chat(Event $event)
    {
        // TODO: Implement Blade view for survey chat
        return view('survey.chat', [
            'event' => $event->only('id', 'name', 'description'),
            'booth' => null,
        ]);
    }

    public function chatWithBooth(Event $event, $boothId)
    {
        $booth = $event->booths()->findOrFail($boothId);

        // TODO: Implement Blade view for survey chat with booth
        return view('survey.chat', [
            'event' => $event->only('id', 'name', 'description'),
            'booth' => $booth->only('id', 'name'),
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
