<?php

namespace App\Http\Controllers\Api;

use App\Ai\Agents\RegistrationAssistantAgent;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Registration;
use App\Models\Visitor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RegistrationController extends Controller
{
    /**
     * Start a new registration chat session.
     */
    public function startChat(Request $request): JsonResponse
    {
        $request->validate([
            'event_id' => ['required', 'integer', 'exists:events,id'],
            'session_token' => ['nullable', 'string'],
        ]);

        $event = Event::findOrFail($request->integer('event_id'));

        // Reuse existing session token or create new
        $token = $request->input('session_token') ?? (string) Str::uuid7();

        $agent = RegistrationAssistantAgent::make($event);

        if ($request->filled('session_token')) {
            $agent->continueLastConversation(
                Registration::where('session_token', $token)->firstOrFail()
            );
        } else {
            // Create a minimal registration record just for chat tracking
            $registration = Registration::create([
                'event_id' => $event->id,
                'name' => '',
                'email' => '',
                'session_token' => $token,
                'status' => 'pending',
            ]);
            $agent->forUser($registration);
        }

        $response = $agent->prompt(
            'A visitor just opened the registration form. Greet them warmly and offer to help with any questions about the form or event.'
        );

        return response()->json([
            'session_token' => $token,
            'message' => $response->text,
        ]);
    }

    /**
     * Ask the AI assistant a question.
     */
    public function ask(Request $request): JsonResponse
    {
        $request->validate([
            'session_token' => ['required', 'string'],
            'question' => ['required', 'string', 'max:2000'],
        ]);

        $registration = Registration::where('session_token', $request->input('session_token'))->firstOrFail();
        $event = $registration->event;

        $agent = RegistrationAssistantAgent::make($event)
            ->continueLastConversation($registration);

        $response = $agent->prompt(
            "The visitor asks: \"{$request->input('question')}\". Answer helpfully."
        );

        return response()->json([
            'message' => $response->text,
        ]);
    }

    /**
     * Submit the completed registration form.
     */
    public function submit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_token' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'company' => ['nullable', 'string', 'max:255'],
            'post_code' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'organization' => ['nullable', 'string', 'max:255'],
            'occupation' => ['nullable', 'string', 'max:100'],
            'age_range' => ['nullable', 'string', 'max:50'],
            'opt_out' => ['nullable', 'boolean'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:100'],
            'source' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $registration = Registration::where('session_token', $request->input('session_token'))->firstOrFail();

        // Create a Visitor record from registration data for survey flow
        $visitor = Visitor::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'company' => $validated['company'] ?? null,
            'post_code' => $validated['post_code'] ?? null,
            'address' => $validated['address'] ?? null,
            'organization' => $validated['organization'] ?? null,
            'occupation' => $validated['occupation'] ?? null,
            'age_range' => $validated['age_range'] ?? null,
            'opt_out' => $validated['opt_out'] ?? false,
            'job_title' => $validated['job_title'] ?? null,
            'country' => $validated['country'] ?? null,
            'session_token' => $registration->session_token,
        ]);

        $registration->update([
            ...$validated,
            'visitor_id' => $visitor->id,
            'status' => 'submitted',
        ]);

        return response()->json([
            'message' => 'Registration submitted successfully!',
            'registration_id' => $registration->id,
            'visitor_id' => $visitor->id,
            'status' => 'submitted',
        ]);
    }
}
