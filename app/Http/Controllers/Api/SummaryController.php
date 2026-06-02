<?php

namespace App\Http\Controllers\Api;

use App\Actions\GenerateSummary;
use App\Http\Controllers\Controller;
use App\Models\Booth;
use App\Models\Event;
use App\Models\Visitor;
use Illuminate\Http\JsonResponse;

class SummaryController extends Controller
{
    public function eventSummary(Event $event, GenerateSummary $generator): JsonResponse
    {
        $summary = $generator->handle($event);

        return response()->json($summary);
    }

    public function boothSummary(Booth $booth, GenerateSummary $generator): JsonResponse
    {
        $summary = $generator->handle($booth->event, $booth);

        return response()->json($summary);
    }

    public function visitorSummary(Visitor $visitor, GenerateSummary $generator): JsonResponse
    {
        $latestSession = $visitor->sessions()->latest()->firstOrFail();

        $summary = $generator->handle($latestSession->event, booth: null, visitor: $visitor);

        return response()->json($summary);
    }

    public function regenerate(Event $event, GenerateSummary $generator): JsonResponse
    {
        // Delete existing summaries for this event and regenerate
        $event->summaries()->delete();

        $summary = $generator->handle($event);

        return response()->json($summary);
    }
}
