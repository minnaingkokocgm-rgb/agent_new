<?php

namespace App\Http\Controllers\Api;

use App\Actions\ProcessAnswer;
use App\Actions\StartSurveySession;
use App\Http\Controllers\Controller;
use App\Http\Requests\StartSurveyRequest;
use App\Http\Requests\SubmitAnswerRequest;
use App\Models\Booth;
use App\Models\Event;
use App\Models\VisitorSession;
use Illuminate\Http\JsonResponse;

class SurveyController extends Controller
{
    public function start(StartSurveyRequest $request, StartSurveySession $starter): JsonResponse
    {
        $event = Event::findOrFail($request->integer('event_id'));

        $booth = null;
        if ($request->filled('booth_id')) {
            $booth = Booth::findOrFail($request->integer('booth_id'));
        }

        return response()->json(
            $starter->handle($event, $booth, $request->integer('visitor_id') ?: null)
        );
    }

    public function answer(SubmitAnswerRequest $request, VisitorSession $session, ProcessAnswer $processor): JsonResponse
    {
        if ($session->status !== 'active') {
            return response()->json([
                'message' => 'This survey session is already completed.',
                'status' => 'completed',
            ], 422);
        }

        return response()->json(
            $processor->handle($session, $request->string('answer')->value())
        );
    }

    public function show(VisitorSession $session): JsonResponse
    {
        return response()->json(
            $session->load(['questions.answer', 'visitor', 'event', 'booth'])
        );
    }

    public function complete(VisitorSession $session): JsonResponse
    {
        if ($session->status === 'completed') {
            return response()->json([
                'message' => 'Session was already completed.',
                'status' => 'completed',
            ]);
        }

        $session->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return response()->json([
            'message' => 'Session marked as completed.',
            'status' => 'completed',
        ]);
    }
}
