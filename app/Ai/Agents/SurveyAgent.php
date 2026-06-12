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
            if ($this->visitor->post || $this->visitor->company) {
                $parts = array_filter([$this->visitor->post, $this->visitor->company]);
                $details[] = implode(' at ', $parts);
            }
            if ($this->visitor->department) {
                $details[] = "department: {$this->visitor->department}";
            }
            if ($this->visitor->industry) {
                $details[] = "industry: {$this->visitor->industry}";
            }
            if ($this->visitor->reception_category) {
                $details[] = "reception category: {$this->visitor->reception_category}";
            }
            if ($this->visitor->responsible_organization) {
                $details[] = "responsible organization: {$this->visitor->responsible_organization}";
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

        $industry = $this->visitor->industry;

        $strategy = match ($industry) {
            'exporters', 'importers' => <<<'S'

SURVEY STRATEGY (exporter/importer):
This visitor is in international trade. Focus on:
- Trade relationships: What markets are they targeting? What countries are they exporting to or importing from?
- Product categories: What specific products do they deal with? Are they looking for new suppliers or buyers?
- Logistics and compliance: Any challenges with shipping, customs, or regulations?
- Partnership opportunities: Are they looking for new trade partners or distributors?
- Tone: Professional, trade-focused. They think in terms of supply chains and market access.

S,
            'wholesalers', 'department_stores', 'supermarkets_convenience_stores', 'other_retailers' => <<<'S'

SURVEY STRATEGY (wholesaler/retailer):
This visitor is in the retail/distribution sector. Focus on:
- Product sourcing: What product categories are they looking for? What volume do they need?
- Supplier relationships: Are they looking for new suppliers or expanding existing relationships?
- Market trends: What consumer trends are they seeing? What products are in demand?
- Quality and pricing: What are their priorities — price, quality, variety, or reliability?
- Tone: Business-focused, practical. They think in terms of margins and customer demand.

S,
            'restaurants', 'hotels', 'catering_companies' => <<<'S'

SURVEY STRATEGY (hospitality/food service):
This visitor is in the hospitality or food service industry. Focus on:
- Food and beverage needs: What specific products are they sourcing? Fresh ingredients, specialty items, beverages?
- Quality standards: Any specific quality or certification requirements (organic, halal, etc.)?
- Volume and frequency: How much do they need and how often?
- Menu innovation: Are they looking for new products to add to their offerings?
- Tone: Service-oriented, quality-focused. They care about guest experience and consistency.

S,
            'meat_manufacturers', 'agricultural_product_manufacturers', 'seafood_manufacturers', 'liquor_manufacturers', 'food_and_beverage_manufacturers', 'other_food_manufacturers' => <<<'S'

SURVEY STRATEGY (food/beverage manufacturer):
This visitor is in food or beverage manufacturing. Focus on:
- Raw materials: What ingredients or raw materials do they need? Are they looking for new suppliers?
- Production scale: What volume do they operate at? Any capacity expansion plans?
- Quality and compliance: Any specific standards, certifications, or regulations they need to meet?
- Innovation: Are they developing new products? What trends are they following?
- Tone: Technical, production-focused. They think in terms of supply reliability and quality consistency.

S,
            'producers_agricultural_cooperatives' => <<<'S'

SURVEY STRATEGY (producer/agricultural cooperative):
This visitor represents producers or agricultural cooperatives. Focus on:
- Products: What do they produce? Are they looking for buyers or distribution channels?
- Scale and capacity: What's their production capacity? Can they meet large orders?
- Market access: Are they trying to enter new markets or expand existing ones?
- Partnerships: Are they looking for exporters, distributors, or processing partners?
- Tone: Partnership-oriented, growth-focused. They want to connect their products with markets.

S,
            'logistics_warehousing' => <<<'S'

SURVEY STRATEGY (logistics/warehousing):
This visitor is in logistics or warehousing. Focus on:
- Services: What logistics services do they offer? Shipping, warehousing, distribution?
- Capacity: What's their storage or transport capacity? Are they expanding?
- Technology: Are they using any logistics tech or looking for efficiency improvements?
- Partnerships: Are they looking for clients or partners in the supply chain?
- Tone: Operational, efficiency-focused. They think in terms of throughput and reliability.

S,
            'mail_order_businesses' => <<<'S'

SURVEY STRATEGY (mail-order business):
This visitor runs a mail-order or e-commerce business. Focus on:
- Product range: What products do they sell online? Are they looking for new suppliers?
- Fulfillment: How do they handle shipping and fulfillment? Any challenges?
- Market reach: What regions do they serve? Are they expanding?
- Supplier needs: What are they looking for in suppliers — reliability, pricing, variety?
- Tone: E-commerce focused, customer-centric. They think in terms of online sales and delivery.

S,
            'import_export_support_consulting' => <<<'S'

SURVEY STRATEGY (import/export support/consulting):
This visitor provides trade support or consulting services. Focus on:
- Services: What kind of support do they offer? Market research, compliance, logistics consulting?
- Client needs: What industries do they serve? What are their clients looking for?
- Market insights: What trends are they seeing in international trade?
- Partnerships: Are they looking for partners or clients to work with?
- Tone: Advisory, knowledge-focused. They think in terms of market intelligence and client solutions.

S,
            'government_agencies_local_authorities', 'embassies_consulates' => <<<'S'

SURVEY STRATEGY (government/diplomatic):
This visitor represents a government agency or diplomatic mission. Focus on:
- Purpose: Are they here for trade promotion, market research, or diplomatic relations?
- Support: Are they looking to support businesses from their country or region?
- Partnerships: Are they facilitating trade relationships or looking for specific industries?
- Policy: Any policy or regulatory aspects they're interested in?
- Tone: Formal, respectful. They represent official institutions and think in terms of bilateral relations.

S,
            default => <<<'S'

SURVEY STRATEGY (general visitor):
- Focus on: what brought them here, product/category interests, what they're looking for
- Ask about: timeline, decision criteria, what would make the visit worthwhile
- Adapt follow-ups based on their answers

S,
        };

        return $strategy;
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
