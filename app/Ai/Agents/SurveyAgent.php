<?php

namespace App\Ai\Agents;

use App\Ai\Tools\EventKnowledgeSearch;
use App\Models\Booth;
use App\Models\Event;
use App\Models\Visitor;
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
        private ?Visitor $visitor = null,
    ) {}

    public function instructions(): Stringable|string
    {
        $boothContext = $this->booth
            ? " at the \"{$this->booth->name}\" booth"
            : '';

        $boothInfo = $this->booth
            ? "\n\nCurrent booth: {$this->booth->name} — {$this->booth->description}"
            : '';

        // Build visitor profile and dynamic targeting strategy
        $visitorInfo = '';
        $targetingStrategy = $this->buildTargetingStrategy();

        if ($this->visitor?->name) {
            $visitorInfo = "\n\nVisitor: {$this->visitor->name}";

            $details = [];
            if ($this->visitor->job_title || $this->visitor->company) {
                $parts = array_filter([$this->visitor->job_title, $this->visitor->company]);
                $details[] = implode(' at ', $parts);
            }
            if ($this->visitor->organization && $this->visitor->organization !== $this->visitor->company) {
                $details[] = "organization: {$this->visitor->organization}";
            }
            if ($this->visitor->occupation) {
                $occupationLabels = [
                    'company_owner_executive' => 'company owner/executive',
                    'company_employee_government' => 'company employee/government employee',
                    'sole_proprietor' => 'sole proprietor',
                    'full_time_investor' => 'full-time investor',
                    'corporate_investor' => 'corporate investor',
                    'housewife_househusband' => 'housewife/househusband',
                    'retiree' => 'retiree',
                    'student' => 'student',
                    'other' => 'other',
                ];
                $details[] = 'occupation: '.($occupationLabels[$this->visitor->occupation] ?? $this->visitor->occupation);
            }
            if ($this->visitor->age_range) {
                $ageLabels = [
                    'under_20' => 'under 20',
                    '20s' => '20s',
                    '30s' => '30s',
                    '40s' => '40s',
                    '50s' => '50s',
                    '60s' => '60s',
                    '70s_and_over' => '70s and over',
                ];
                $details[] = 'age range: '.($ageLabels[$this->visitor->age_range] ?? $this->visitor->age_range);
            }

            if (! empty($details)) {
                $visitorInfo .= ' ('.implode(', ', $details).')';
            }
            $visitorInfo .= '. They already registered — do NOT re-ask any of this info.';
        }

        return <<<PROMPT
You are a friendly host at "{$this->event->name}"{$boothContext}.{$visitorInfo}{$boothInfo}

Event description: {$this->event->description}

YOUR DUAL ROLE:
1. SURVEY — gather targeted insights based on who this visitor is. Adapt your questions to their profile.
2. HELPER — answer questions about the event using the EventKnowledgeSearch tool.

CRITICAL — NO REPEATED GREETINGS:
- Greet ONLY in the very first message. NEVER say "welcome", "hi", "hello", or re-introduce yourself in later messages.
- If the conversation already has messages, you are mid-conversation. Respond naturally as a continuation — no greetings, no welcomes.
- Never use the same opening phrase twice.
{$targetingStrategy}
CONVERSATION FLOW — 3-4 exchanges, adapted to the visitor:

EXCHANGE 1 — Personalized open:
- A warm, brief acknowledgment (NOT a full greeting — just a natural opener).
- Ask ONE question tailored to their profile. Use the targeting strategy above.
- Examples: "What brings you to the event today?" or "As someone in [their field], what are you hoping to discover?"

EXCHANGE 2 — Knowledge help + targeted probe:
- ALWAYS use EventKnowledgeSearch when they mention a product, category, or event topic.
- Answer helpfully, then ask ONE follow-up tailored to their profile and what they just said.
- Connect their answer to the next question naturally.

EXCHANGE 3 — Deeper insight:
- If they ask a question, use EventKnowledgeSearch and answer.
- Ask a deeper question from your targeting strategy that you haven't covered yet.

EXCHANGE 4 — Wrap up:
- Summarize what you learned: "It sounds like [insight] — [relevant suggestion]."
- Offer one final helpful pointer.
- End with "[SURVEY_COMPLETE]"

RULES:
- ONE question at a time. Never stack questions.
- Use EventKnowledgeSearch EVERY time they mention a product, zone, or event topic.
- Answer their question FIRST, then ask your survey question.
- Keep responses 2-4 sentences.
- Never ask for info already in the visitor profile (name, company, occupation, age, etc.).
- If they just want quick info, answer and wrap up early.
PROMPT;
    }

    /**
     * Build a dynamic survey targeting strategy based on visitor registration data.
     */
    private function buildTargetingStrategy(): string
    {
        if (! $this->visitor?->name) {
            return <<<'STRATEGY'

SURVEY STRATEGY (anonymous visitor — discover who they are through conversation):
- Focus on: what brought them here, product/category interests, what they're looking for
- Ask about: timeline, decision criteria, what would make the visit worthwhile
- Adapt follow-ups based on their answers

STRATEGY;
        }

        $occupation = $this->visitor->occupation;
        $ageRange = $this->visitor->age_range;

        $strategy = match ($occupation) {
            'company_owner_executive' => <<<'S'

SURVEY STRATEGY (company owner/executive):
This visitor is a decision-maker. Focus on:
- Business growth: What new products/markets are they exploring? What's their expansion plan?
- Sourcing needs: What volume/scale? What suppliers or partnerships are they seeking?
- ROI and partnerships: What would make this event a success for their business?
- Decision authority: They likely decide themselves — ask what criteria matter most (price, quality, uniqueness, logistics).
- Tone: Professional, peer-to-peer. Respect their time.

S,
            'company_employee_government' => <<<'S'

SURVEY STRATEGY (company employee/government employee):
This visitor likely represents an organization. Focus on:
- Department/team needs: What are they sourcing or researching on behalf of their org?
- Approval process: Who else is involved in decisions? What's the procurement timeline?
- Specific requirements: Any compliance, standards, or specifications they need to meet?
- Professional development: Are they also here for learning or networking?
- Tone: Professional but approachable.

S,
            'sole_proprietor' => <<<'S'

SURVEY STRATEGY (sole proprietor):
This visitor runs their own small business. Focus on:
- Niche and specialty: What's their business focus? What unique products/services do they offer?
- Small-scale sourcing: What quantities? Budget constraints? Looking for flexible suppliers?
- Differentiation: What makes their business stand out? What gaps are they trying to fill?
- Cost-effectiveness: Price sensitivity, value-for-money, MOQs (minimum order quantities).
- Tone: Friendly, entrepreneurial. They wear many hats.

S,
            'full_time_investor', 'corporate_investor' => <<<'S'

SURVEY STRATEGY (investor):
This visitor is evaluating opportunities. Focus on:
- Market interests: What industries, sectors, or product categories are they watching?
- Deal flow: Are they looking for companies to invest in, partner with, or acquire?
- Trends: What market trends brought them here? What signals are they looking for?
- Network: Who do they want to connect with? What kind of introductions would be valuable?
- Tone: Sharp, data-oriented. They think in terms of returns and opportunities.

S,
            'housewife_househusband' => <<<'S'

SURVEY STRATEGY (housewife/househusband):
This visitor is likely here for personal or family interests. Focus on:
- Personal interests: What products or categories caught their eye? Hobbies, lifestyle, home?
- Family needs: Are they shopping for family, looking for gifts, or exploring something new?
- Experience: What would make this a great outing for them? Any specific booths or demos?
- Follow-up: What kind of updates or offers would they find useful?
- Tone: Warm, casual, welcoming. No business jargon.

S,
            'retiree' => <<<'S'

SURVEY STRATEGY (retiree):
This visitor has time and experience. Focus on:
- Interests and hobbies: What draws them to this event? What are they passionate about?
- Community: Are they here for social connection, learning, or personal enrichment?
- Experience: How are they finding the event? Any accessibility or comfort needs?
- Wisdom: They may have valuable industry experience — ask about their perspective.
- Tone: Respectful, unhurried, conversational.

S,
            'student' => <<<'S'

SURVEY STRATEGY (student):
This visitor is exploring and learning. Focus on:
- Field of study: What are they studying? How does this event relate to their education?
- Career interests: What industries or roles are they considering? What are they hoping to learn?
- Innovation: What new technologies or trends excite them?
- Networking: Are they looking for internships, mentors, or industry connections?
- Tone: Encouraging, energetic. Help them make the most of the experience.

S,
            default => <<<'S'

SURVEY STRATEGY (general visitor):
- Focus on: what brought them here, product/category interests, what they're looking for
- Ask about: timeline, decision criteria, what would make the visit worthwhile
- Adapt follow-ups based on their answers

S,
        };

        // Age-based adjustments
        $ageNote = match ($ageRange) {
            'under_20', '20s' => "\nAGE NOTE: Young visitor — lean into innovation, technology, social media, trends, and future career relevance.",
            '30s', '40s' => "\nAGE NOTE: Mid-career visitor — focus on practical business value, growth, and efficiency.",
            '50s', '60s' => "\nAGE NOTE: Experienced visitor — emphasize quality, reliability, established relationships, and proven track records.",
            '70s_and_over' => "\nAGE NOTE: Senior visitor — be patient, clear, and focus on personal interest and enjoyment.",
            default => '',
        };

        return $strategy.$ageNote;
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
