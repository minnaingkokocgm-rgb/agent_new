# Survey AI — Complete Flow Documentation

> Traced from code, 2026-06-05. Updated 2026-06-07 with comprehensive registration fields,
> dynamic survey targeting based on visitor data, and greeting repetition fix.

---

## 0. Unified Flow: Registration → Survey

### Flow (2026-06-05)

```
GET /s/{event}/register
  → Visitor fills comprehensive registration form:
    - Contact: name, email, phone
    - Professional: company, organization, job_title, occupation (9 categories)
    - Location: post_code, address, country
    - Demographics: age_range (7 ranges)
    - Additional: source, notes, opt_out (communication preferences)
  → AI Assistant helps with questions (RegistrationAssistantAgent)
  → Submit form via POST /api/registration/submit
    → Registration record updated (status=submitted)
    → Visitor record created from all registration data
    → Registration.visitor_id linked to Visitor
    → Response includes visitor_id
  → Frontend redirects to: /s/{event}?visitor_id=X

GET /s/{event}?visitor_id=X
  → Survey chat loads with visitorId in context
  → POST /api/survey/start { event_id, booth_id, visitor_id }
    → StartSurveySession uses existing Visitor (not anonymous)
    → SurveyAgent::make($event, $booth, $visitor)  — only if $visitor->name is set
    → System prompt includes visitor profile + dynamic targeting strategy
    → Agent builds occupation-specific questions, skips identity questions
```

### Key Changes

| Before | After |
|---|---|
| Registration → thank-you page (dead end) | Registration → survey page (seamless) |
| Survey creates anonymous Visitor | Survey reuses registered Visitor |
| Agent asks name/role/company (wasteful) | Agent knows visitor profile, asks targeted questions |
| Registration and survey are separate silos | Single visitor journey: register → survey |
| Generic survey questions for everyone | Dynamic targeting based on occupation/age |
| Agent repeats "Welcome to [event]" every turn | Explicit instructions prevent repeated greetings |

### Files Changed

| File | Change |
|---|---|
| `app/Models/Visitor.php` | Added `company`, `job_title`, `country` fillable + `registrations()` relationship |
| `app/Models/Registration.php` | Added `visitor_id` fillable + `visitor()` relationship |
| `database/migrations/*_add_visitor_fields_*.php` | Added columns to visitors table |
| `database/migrations/*_add_visitor_id_to_registrations.php` | Added FK to registrations table |
| `app/Http/Controllers/Api/RegistrationController.php` | `submit()` creates Visitor, returns `visitor_id` |
| `app/Ai/Agents/SurveyAgent.php` | Constructor accepts `?Visitor`, `instructions()` injects visitor info |
| `app/Actions/StartSurveySession.php` | Accepts `?int $visitorId`, uses existing Visitor if provided |
| `app/Actions/ProcessAnswer.php` | Passes `$session->visitor` to SurveyAgent |
| `app/Http/Controllers/Api/SurveyController.php` | Passes `visitor_id` to StartSurveySession |
| `app/Http/Requests/StartSurveyRequest.php` | Validates optional `visitor_id` |
| `app/Http/Controllers/SurveyPageController.php` | Passes `visitorId` query param to view |
| `resources/views/survey/chat.blade.php` | Sends `visitor_id` in start API call |
| `resources/views/survey/register.blade.php` | Redirects to survey with `visitor_id` after submit |

### Visitor Info Injection (Updated 2026-06-07)

The SurveyAgent conditionally injects visitor details AND builds a dynamic targeting strategy. If the Visitor has a `name`, it's appended to the system prompt along with occupation-based survey strategy and age-based adjustments.

```php
// app/Ai/Agents/SurveyAgent.php — instructions()
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
            // ... 6 more occupation types
        ];
        $details[] = 'occupation: '.($occupationLabels[$this->visitor->occupation] ?? $this->visitor->occupation);
    }
    if ($this->visitor->age_range) {
        $ageLabels = [
            'under_20' => 'under 20', '20s' => '20s', '30s' => '30s',
            '40s' => '40s', '50s' => '50s', '60s' => '60s', '70s_and_over' => '70s and over',
        ];
        $details[] = 'age range: '.($ageLabels[$this->visitor->age_range] ?? $this->visitor->age_range);
    }
    
    if (!empty($details)) {
        $visitorInfo .= ' ('.implode(', ', $details).')';
    }
    $visitorInfo .= '. They already registered — do NOT re-ask any of this info.';
}

// Dynamic targeting strategy based on occupation
$targetingStrategy = $this->buildTargetingStrategy();
// Returns occupation-specific survey focus + age-based adjustments
```

The `StartSurveySession` and `ProcessAnswer` actions only pass the Visitor to SurveyAgent when it has a name:

```php
SurveyAgent::make($event, $booth, $visitor->name ? $visitor : null)
```

---

## 1. High-Level Flow

```
POST /api/survey/start
  → StartSurveySession::handle($event, $booth, $visitorId?)
    → Visitor looked up by $visitorId OR created (anonymous, UUID7 session_token)
    → VisitorSession created (event_id, booth_id, status=active)
    → SurveyAgent::make($event, $booth, $visitor)->forUser($visitor)
    → prompt("A visitor just arrived. This is your FIRST and ONLY greeting. Give a brief, warm acknowledgment and ask one tailored opening question based on their profile. Do NOT use generic phrases like 'Welcome to [event name]' — be natural and personal.")
    → Returns personalized opening + targeted question

POST /api/survey/{session}/answer  (× 3-4)
  → ProcessAnswer::handle($session, $answer)
    → Store SessionAnswer linked to current question
    → SurveyAgent::make($event, $booth, $visitor)->continueLastConversation($visitor)
    → prompt("The visitor said: '...'. This is a CONTINUING conversation — do NOT greet, welcome, or re-introduce yourself. Respond naturally, answer any questions using the knowledge tool if needed, then ask the next targeted question.")
    → Returns next response or [SURVEY_COMPLETE]
```

---

## 2. System Prompt

Built in `app/Ai/Agents/SurveyAgent.php::instructions()`. Hardcoded template with DB values injected.

### Injected Data (Updated 2026-06-07)

| Data | Source DB Column | Example |
|---|---|---|
| `$this->event->name` | `events.name` | `"TechConf 2026"` |
| `$this->event->description` | `events.description` | `"Brief overview of the event..."` |
| `$this->booth->name` | `booths.name` | `"AI Demo Station"` (booth-scoped only) |
| `$this->booth->description` | `booths.description` | `"What the booth showcases"` (booth-scoped only) |
| Visitor info | `visitors.name`, `company`, `job_title`, `organization`, `occupation`, `age_range` | `"Visitor: John Smith (Software Engineer at Acme Corp, occupation: company owner/executive, age range: 30s)"` — only when visitor has a name |
| **Dynamic targeting strategy** | `visitors.occupation`, `visitors.age_range` | Occupation-specific survey focus + age-based adjustments (see below) |

### Actual System Prompt Summary (from code, updated 2026-06-07)

The current `SurveyAgent` prompt defines a dual role with **dynamic targeting**:

1. **SURVEY** — gather targeted insights based on visitor profile. Adapt questions to their occupation and age.
2. **HELPER** — answer event, booth, product, schedule, and logistics questions using the `EventKnowledgeSearch` tool.

The prompt instructs the agent to:

- **NO REPEATED GREETINGS**: Greet ONLY in the very first message. Never say "welcome", "hi", "hello", or re-introduce in later messages.
- Ask ONE personalized question tailored to their profile (occupation/age).
- Never ask for name, company, occupation, age, email, phone, or country because these come from registration.
- Use `EventKnowledgeSearch` whenever the visitor mentions a product, zone, or event topic.
- Answer visitor questions first, then ask one survey question.
- Keep responses to 2-4 sentences.
- Complete after 3-4 exchanges with `[SURVEY_COMPLETE]`.

### Dynamic Targeting Strategy (New 2026-06-07)

The `buildTargetingStrategy()` method generates occupation-specific survey strategies:

**Occupation-based focus:**
- **Company owner/executive**: Business growth, ROI, partnerships, decision authority, sourcing volume
- **Company/government employee**: Department needs, approval process, compliance, procurement timeline
- **Sole proprietor**: Niche/specialty, small-scale sourcing, differentiation, cost-effectiveness
- **Investor** (full-time/corporate): Market trends, deal flow, investment opportunities, networking
- **Housewife/househusband**: Personal interests, family needs, experience, follow-up preferences
- **Retiree**: Interests/hobbies, community, experience, personal enrichment
- **Student**: Field of study, career interests, innovation, networking/internships
- **Other**: Generic approach (interests, timeline, decision criteria)

**Age-based adjustments:**
- **Under 20 / 20s**: Innovation, technology, social media, trends, future career relevance
- **30s / 40s**: Practical business value, growth, efficiency
- **50s / 60s**: Quality, reliability, established relationships, proven track records
- **70s and over**: Patience, clarity, personal interest, enjoyment

**Anti-greeting rules:**
The system prompt includes explicit instructions:
```
CRITICAL — NO REPEATED GREETINGS:
- Greet ONLY in the very first message. NEVER say "welcome", "hi", "hello", or re-introduce yourself in later messages.
- If the conversation already has messages, you are mid-conversation. Respond naturally as a continuation — no greetings, no welcomes.
- Never use the same opening phrase twice.
```

This prevents the agent from repeating "Welcome to [event name]" on every turn.

### Agent Configuration

```php
#[Model('openai/gpt-4o')]     // via OpenRouter
#[Temperature(0.7)]
#[MaxSteps(12)]                // max tool-calling loops per turn
#[MaxTokens(1024)]             // max response tokens
```

### Key Point

**Knowledge base chunks are NOT in the system prompt.** They are only available when the agent chooses to call `EventKnowledgeSearch`. The system prompt only contains basic event/booth identity from DB columns plus optional visitor info.

---

## 3. Conversation History

Managed by `RemembersConversations` trait + `RememberConversation` middleware (Laravel AI SDK).

### Turn 1 — Start

```
forUser($visitor)
  → conversation_id = null (new conversation)
  → prompt("A visitor just arrived. Greet them warmly...")

RememberConversation middleware:
  1. No existing conversation → creates one:
       conversation_id = UUID7
       title = generated from prompt (3-5 words)
  2. Stores user message: "A visitor just arrived..."
  3. Stores assistant response: greeting + opening question

Result: conversation persisted in agent_conversations + agent_conversation_messages
```

### Turn 2+ — Answer

```
continueLastConversation($visitor)
  → loads conversation_id from DB by visitor_id
  → messages() returns all previous messages from store (up to 100)
  → prompt("The visitor said: 'John, software engineer at Acme'. Respond naturally...")

LLM sees:
  [SYSTEM]  ← instructions()
  [USER]    ← "A visitor just arrived. Greet them warmly..."
  [ASSISTANT] ← "Hi! Welcome... What brings you here?"
  [USER]    ← "The visitor said: 'I'm interested in AI tools.' Respond naturally..."
  → Assistant responds

RememberConversation middleware:
  1. Conversation already exists → no new creation
  2. Stores user message
  3. Stores assistant response

Result: conversation grows by 2 messages per turn
```

### Storage Tables

| Table | Purpose |
|---|---|
| `agent_conversations` | Conversation metadata (id, participant_id, title) |
| `agent_conversation_messages` | Individual messages (conversation_id, role, content, tokens) |

### Participant Identity

| Method | What it does |
|---|---|
| `forUser($visitor)` | Starts new conversation, keys to `visitor->id` |
| `continueLastConversation($visitor)` | Finds latest conversation for `visitor->id`, loads messages |

The `Visitor` model's `id` is used as the participant identifier. Each visitor gets their own conversation thread.

### Max Context Window

```php
protected function maxConversationMessages(): int
{
    return 100;  // last 100 messages included in context (default from RemembersConversations)
}
```

---

## 4. Knowledge Retrieval (EventKnowledgeSearch)

The agent calls this tool **only when it decides to** — it's not automatic. Triggered by instructions: "When the visitor asks about the event, booths, products, schedule, or any event-related topic, use the EventKnowledgeSearch tool".

### Search Flow

```
Agent decides to call: EventKnowledgeSearch(query="what products do you have")

1. Embed the query:
   Embeddings::for(["what products do you have"])
     ->dimensions(1536)
     ->generate(provider: 'openrouter', model: 'openai/text-embedding-3-small')

2. Vector search with scoping:
   KnowledgeChunk::query()
     ->where('event_id', $this->event->id)        // always scoped to event
     ->when($this->booth, fn($q) => $q->where(function ($q) {
         $q->where('booth_id', $this->booth->id)  // booth-specific chunks
           ->orWhereNull('booth_id');             // PLUS event-wide chunks
     }))
     ->whereVectorSimilarTo('embedding', $queryEmbedding, 0.1)  // similarity ≥ 0.1
     ->limit(5)
     ->get()

3. Return results as concatenated text:
   "Relevant event information found:

   This is booth A which showcases AI and machine learning demos...

   ---

   We have a chatbot demo, NLP pipeline, and computer vision showcase..."
```

### Scoping Behavior

```
Without booth (event-level survey):
  WHERE event_id = X
  → Searches ALL chunks for this event (event-wide + all booths)

With booth (booth-scoped survey):
  WHERE event_id = X AND (booth_id = Y OR booth_id IS NULL)
  → Searches booth-specific chunks AND event-wide chunks
```

### ✅ Fixed (2026-06-05)

Event-wide chunks (`booth_id=NULL`) were previously excluded from booth-scoped search.
Now the query includes `OR booth_id IS NULL`, so both booth-specific and event-wide chunks are returned.

---

## 5. Completion Logic

In `ProcessAnswer::handle()` — after **4 exchanges** (4 questions asked):

```php
$questionCount = $session->questions()->count();

if ($questionCount >= 4) {
    // Force complete — mark session done, send final prompt
    $session->update([
        'status' => 'completed',
        'completed_at' => now(),
    ]);

    $agent = SurveyAgent::make($session->event, $session->booth,
        $session->visitor->name ? $session->visitor : null
    )->continueLastConversation($session->visitor);

    $response = $agent->prompt(
        "The visitor said: \"{$answer}\". This is the FINAL exchange. " .
        "Wrap up warmly — summarize what you learned, offer one helpful pointer, " .
        "and end with [SURVEY_COMPLETE]. Do NOT greet or welcome again."
    );
}
```

The `[SURVEY_COMPLETE]` marker is detected by the frontend to show the thank-you state.

**Before the limit**, each answer triggers a natural continuation:

```php
$agent = SurveyAgent::make($session->event, $session->booth,
    $session->visitor->name ? $session->visitor : null
)->continueLastConversation($session->visitor);

$response = $agent->prompt(
    "The visitor said: \"{$answer}\". This is a CONTINUING conversation — " .
    "do NOT greet, welcome, or re-introduce yourself. Respond naturally, " .
    "answer any questions using the knowledge tool if needed, " .
    "then ask the next targeted question."
);
```

---

## 6. Data Flow Diagram

```
                         survey/start
                              │
                    ┌─────────┴─────────┐
                    │  StartSurveySession │
                    └─────────┬─────────┘
                              │
              ┌───────────────┼───────────────┐
              ▼               ▼               ▼
         Visitor        VisitorSession    SurveyAgent
         (looked up     (created)         (instantiated with
          or created)                      event, booth, visitor)
                                              │
                              ┌───────────────┘
                              ▼
                    instructions()
                    ┌─────────────────────────┐
                    │ event.name              │──▶ events table
                    │ event.description       │──▶ events table
                    │ booth?.name             │──▶ booths table
                    │ booth?.description      │──▶ booths table
                    │ visitor.name/company/   │──▶ visitors table
                    │   job_title (optional)  │    (only if name set)
                    │ hardcoded rules         │──▶ SurveyAgent.php
                    └─────────────────────────┘
                              │
                              ▼
                        prompt()
                              │
                    ┌─────────┴─────────┐
                    │  LLM Response      │
                    │  (greeting + Q)    │
                    └─────────┬─────────┘
                              │
                    RememberConversation
                    middleware stores:
                    • conversation_id
                    • user message
                    • assistant response
                              │
                              ▼
                         response
                              │
            survey/{session}/answer  (× 3-4)
                              │
                    ┌─────────┴─────────┐
                    │   ProcessAnswer    │
                    └─────────┬─────────┘
                              │
              ┌───────────────┼───────────────┐
              ▼               ▼               ▼
        SessionAnswer   continueLast      prompt()
        (stored)        Conversation      ("visitor
                        (loads history)    said:...")
                              │
                    ┌─────────┴─────────┐
                    │ Agent may call:    │
                    │ EventKnowledgeSearch│
                    └─────────┬─────────┘
                              │
                    ┌─────────┴─────────┐
                    │ pgvector search    │
                    │ knowledge_chunks   │
                    │ scoped to event    │
                    │ + booth if set     │
                    │ (incl. NULL booth) │
                    └─────────┬─────────┘
                              │
                              ▼
                    Next response OR
                    [SURVEY_COMPLETE]
```

---

## 7. Key Files

| File | Role |
|---|---|
| `app/Ai/Agents/SurveyAgent.php` | System prompt, model config, tools |
| `app/Ai/Tools/EventKnowledgeSearch.php` | Vector search with event/booth scoping |
| `app/Actions/StartSurveySession.php` | Creates visitor + session, boots agent |
| `app/Actions/ProcessAnswer.php` | Stores answer, continues conversation, completes at 4 exchanges |
| `app/Http/Controllers/Api/SurveyController.php` | API endpoints (start, answer, show, complete) |
| `vendor/laravel/ai/src/Concerns/RemembersConversations.php` | Conversation ID + message retrieval |
| `vendor/laravel/ai/src/Middleware/RememberConversation.php` | Stores messages after each prompt |
| `vendor/laravel/ai/src/Providers/Concerns/GeneratesText.php` | Assembles system + messages + tools → LLM call |

---

## 8. Current Limitations

1. **Hardcoded 4-exchange limit** — Not configurable per event. Always completes after `$questionCount >= 4`.
2. **No knowledge caching** — Every `EventKnowledgeSearch` call re-embeds and re-searches, even for repeated queries.
3. **Anonymous visitors** — `Visitor` record created with only a `session_token` (no name/email until registration or survey captures it).
4. **Chat UI code duplication** — Survey chat (`chat.blade.php`) and registration chat (`register.blade.php` chat panel) use similar but separate code for chat bubbles, typing indicators, and AJAX handling.
5. **No idle timeout** — Survey sessions have no auto-expiry or idle disconnect UI.

### ✅ Fixed (2026-06-07)

- **Registration form required-field mismatch** — Fixed. All form fields now properly validate as nullable where appropriate.
- **Repeated greetings** — Fixed. System prompt and action prompts now explicitly prevent greeting repetition.
- **Generic survey questions** — Fixed. SurveyAgent now builds occupation-specific targeting strategies and age-based adjustments.
