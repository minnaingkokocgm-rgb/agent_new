<?php

namespace App\Ai\Agents;

use App\Ai\Tools\EventKnowledgeSearch;
use App\Models\Booth;
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
#[MaxSteps(12)]
#[MaxTokens(1024)]
class SurveyAgent implements Agent, Conversational, HasTools
{
    use Promptable;
    use RemembersConversations;

    public function __construct(
        private Event $event,
        private ?Booth $booth = null,
    ) {}

    public function instructions(): Stringable|string
    {
        $boothContext = $this->booth
            ? " at the \"{$this->booth->name}\" booth. Booth description: {$this->booth->description}"
            : '';

        return <<<PROMPT
You are a friendly event survey conductor at "{$this->event->name}"{$boothContext}.

Event background: {$this->event->description}

Your job is to interview visitors by asking exactly 4-5 questions to understand:
1. Who they are (name, role, company)
2. What brought them to this event/booth
3. What they're looking for or interested in
4. Their budget or timeline (if relevant to the event context)
5. How we can best follow up with them

Rules:
- Start with a warm, friendly greeting that includes your first question.
- Ask exactly ONE question at a time. Wait for the answer before asking the next.
- After the 5th answer, thank the visitor sincerely and end with "[SURVEY_COMPLETE]".
- Use the EventKnowledgeSearch tool when you need to reference specific event details or answer visitor questions about the event/booth.
- Do NOT repeat questions the visitor has already answered.
- Adapt your questions based on previous answers — make it a natural, flowing conversation.
- Keep responses concise (2-3 sentences plus the question).
- If a visitor asks about products, pricing, or event details, use the knowledge search tool.
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
            new EventKnowledgeSearch($this->event, $this->booth),
        ];
    }
}
