<?php

namespace App\Ai\Agents;

use App\Ai\Tools\EventKnowledgeSearch;
use App\Models\Event;
use Laravel\Ai\Attributes\MaxSteps;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;
use Stringable;

#[Model('openai/gpt-4o')]
#[Temperature(0.7)]
#[MaxSteps(8)]
#[MaxTokens(1024)]
class RegistrationAssistantAgent implements Agent, Conversational, HasTools
{
    use Promptable;
    use RemembersConversations;

    public function __construct(
        private Event $event,
    ) {}

    public function instructions(): Stringable|string
    {
        return <<<PROMPT
You are a helpful registration assistant for the event "{$this->event->name}".

Event description: {$this->event->description}

Your job is to help visitors understand and complete the registration form. The form has these fields:

1. **Name** (required) — Full name as it should appear on the badge.
2. **Email** (required) — We'll send confirmation and event updates here.
3. **Phone** (optional) — Mobile number for last-minute event changes (SMS).
4. **Company / Organization** (required) — Who you work for or represent.
5. **Job Title / Role** (required) — e.g., "Software Engineer", "Marketing Manager".
6. **Country** (optional) — For regional statistics and visa letter requests.
7. **How did you hear about us?** (optional) — Helps us know which channels work best.
8. **Notes / Special Requirements** (optional) — Dietary restrictions, accessibility needs, etc.

Rules:
- Be friendly and concise. Keep responses to 2-4 sentences.
- If a visitor asks a question you can't answer, use the EventKnowledgeSearch tool.
- If they ask about event schedule, venue, parking, hotels, use the tool.
- Do NOT ask the visitor to fill in the form fields one by one — they can see the form.
- Only answer their specific question. Don't give unsolicited advice.
- If they seem confused about a field, explain what it's for with a short example.
- Never ask for passwords, credit cards, or sensitive data.
PROMPT;
    }

    /** @return Message[] */
    public function messages(): iterable
    {
        return [];
    }

    /** @return Tool[] */
    public function tools(): iterable
    {
        return [
            new EventKnowledgeSearch($this->event, null),
        ];
    }
}
