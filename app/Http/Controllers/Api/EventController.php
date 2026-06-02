<?php

namespace App\Http\Controllers\Api;

use App\Actions\IndexKnowledge;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateEventRequest;
use App\Http\Requests\UploadKnowledgeRequest;
use App\Models\Booth;
use App\Models\Event;
use Illuminate\Http\JsonResponse;

class EventController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            Event::with('booths')->latest()->get()
        );
    }

    public function store(CreateEventRequest $request): JsonResponse
    {
        $event = Event::create($request->validated());

        return response()->json($event, 201);
    }

    public function show(Event $event): JsonResponse
    {
        return response()->json(
            $event->load(['booths', 'sessions' => fn ($q) => $q->withCount('questions')])
        );
    }

    public function update(CreateEventRequest $request, Event $event): JsonResponse
    {
        $event->update($request->validated());

        return response()->json($event);
    }

    public function destroy(Event $event): JsonResponse
    {
        $event->delete();

        return response()->json(null, 204);
    }

    public function uploadKnowledge(UploadKnowledgeRequest $request, Event $event, IndexKnowledge $indexer): JsonResponse
    {
        $booth = null;
        if ($request->filled('booth_id')) {
            $booth = Booth::findOrFail($request->integer('booth_id'));
        }

        $chunksCreated = $indexer->handle(
            $event,
            $booth,
            $request->string('content')->value(),
            $request->string('source_name')->value(),
        );

        return response()->json([
            'message' => 'Knowledge indexed successfully.',
            'chunks_created' => $chunksCreated,
        ]);
    }
}
