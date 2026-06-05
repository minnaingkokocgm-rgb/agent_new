<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;

class EventPageController extends Controller
{
    public function index()
    {
        // TODO: Implement Blade view for admin events index
        return view('admin.events.index', [
            'events' => Event::withCount('booths')->latest()->get(),
        ]);
    }

    public function create()
    {
        // TODO: Implement Blade view for admin event create
        return view('admin.events.create');
    }

    public function show(Event $event)
    {
        // TODO: Implement Blade view for admin event show
        $event->load([
            'booths' => fn ($q) => $q->withCount('knowledgeChunks'),
            'sessions' => fn ($q) => $q->with(['visitor'])->withCount('questions')->latest(),
        ]);
        $event->loadCount('knowledgeChunks');

        return view('admin.events.show', [
            'event' => $event,
        ]);
    }

    public function summary(Event $event)
    {
        // TODO: Implement Blade view for admin event summary
        return view('admin.events.summary', [
            'event' => $event->only('id', 'name'),
            'summary' => $event->summaries()->latest('generated_at')->first(),
        ]);
    }
}
