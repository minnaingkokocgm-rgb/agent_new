<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateEventRequest;
use App\Models\Booth;
use App\Models\Event;
use Illuminate\Http\JsonResponse;

class BoothController extends Controller
{
    public function store(CreateEventRequest $request, Event $event): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'metadata' => ['nullable', 'array'],
        ]);

        $booth = $event->booths()->create($validated);

        return response()->json($booth, 201);
    }

    public function show(Booth $booth): JsonResponse
    {
        return response()->json(
            $booth->load(['event', 'sessions' => fn ($q) => $q->withCount('questions')])
        );
    }

    public function destroy(Booth $booth): JsonResponse
    {
        $booth->delete();

        return response()->json(null, 204);
    }
}
