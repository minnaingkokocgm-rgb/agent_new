# Project Plan — Q-PASS Event Management Platform

> **Source document:** `project.pdf` — "FY2026 ICT Business Advancement Support Project: Proposed AI-Utilized Solution for Generating Technology Advancement Stages" (Event Management System, by Q-PASS / Copro System).
> **Status:** Implementation plan derived from the proposal and mapped onto the existing Laravel 13 codebase.

---

## 1. Executive Summary

Generative AI is positioned as the lever that transforms trade-show encounters and business negotiations. The proposal delivers **one integrated platform** with **six cooperating functional modules**, addressing a causal chain of pain points that starts with the **visitor experience** and ends with **organizer business continuity**.

### 1.1 Three-Party Challenge Chain

| Stakeholder | Core Pain Points |
|---|---|
| **Organizers** | Time-consuming exhibitor management (documents, reminders, Q&A); cost burden of dedicated staff and outsourced admin; no reliable mechanism to retain exhibitors. |
| **Exhibitors** | Cannot grasp visitor context (intent, budget, timeline, decision authority); inconsistent negotiation quality across reps; ROI of exhibiting cannot be quantified; renewal decisions are intuitive. |
| **Visitors** | Cannot find suitable booths; no efficient way to navigate in limited time; satisfaction drops when the "expected encounter" does not happen. |

> The proposal's thesis: the three challenges form a **causal chain that originates from the visitor experience**. Improving visitor experience → improves exhibitor negotiation quality / ROI → drives renewal → secures organizer continuity.

### 1.2 Solution Concept

A platform where Generative AI deeply understands visitor intent, supports exhibitor negotiations, and automates organizer operations. The six modules are organized into three layers:

- **Data Acquisition Layer** — captures on-site user input.
- **Intelligence Layer** — AI intent inference engine.
- **Business Support Layer** — three end-user tools (visitor concierge, exhibitor copilot, organizer autopilot + dashboard).

---

## 2. Solution Modules (Source: PDF §Overall Solution Overview, p.3)

| # | Module | Layer | Primary Beneficiary |
|---|---|---|---|
| 1 | **Visitor AI Concierge** | Business Support | Visitors (also serves organizers & exhibitors via data) |
| 2 | **AI Intent Inference Layer** | Intelligence | Exhibitors & Organizers |
| 3 | **Exhibitor Registration Mobile App** (extended with QR + conversation guidance) | Data Acquisition | Exhibitors (also visitors & organizers) |
| 4 | **Copilot for Exhibitors** | Business Support | Exhibitors (also organizers) |
| 5 | **Autopilot Exhibitor Management for Organizers** | Business Support | Organizers (also exhibitors) |
| 6 | **Dashboard for Organizers** | Business Support | Organizers |

---

## 3. Module-Level Plans

### 3.1 Module 1 — Visitor AI Concierge

> PDF §"Solution 1: Visitor AI Concierge" (p.4)

**Goal:** AI guide that deeply understands visitor intent through dynamic surveys and continuous dialogue.

**Current state in repo:** Partially implemented.
- `app/Ai/Agents/` (SurveyAgent), `app/Actions/StartSurveySession.php`, `app/Actions/ProcessAnswer.php`, `app/Http/Controllers/SurveyPageController.php`, and `resources/views/survey/` already exist.
- `Visitor`, `VisitorSession`, `SessionQuestion`, `SessionAnswer` models exist.

**Gaps vs. proposal:**
- The current survey is **event-scoped** (single event, 4 fixed exchanges) — the proposal wants a **multi-touchpoint, lifecycle-spanning** experience (registration → pre-arrival → on-site → ongoing).
- No **My Page** web UI (no app install) with chat UI.
- No **dynamic question generation** by AI based on previous answers.
- No **booth/route recommendation** engine.
- No **on-site QR activation** that switches context to "I am at booth X".
- No three-tier cost model (rule-based + lightweight AI + advanced AI).

**Build plan:**

1. **Domain model**
   - Add `visitor_profiles` table (intent topic, themes, confidence, last_updated).
   - Add `my_page_messages` table (visitor ↔ AI concierge persistent chat log).
   - Add `booth_recommendations` (visitor_id, booth_id, score, reason, generated_at).

2. **Agents & tools**
   - New agent: `VisitorConciergeAgent` (different from `SurveyAgent` — persistent persona, multi-session memory).
   - New tool: `RecommendBoothsTool` (RAG over `booths` + `knowledge_chunks`).
   - New tool: `RoutePlannerTool` (booth proximity, estimated dwell, intent-matching).
   - New tool: `CongestionStatusTool` (read aggregated dwell counts from `SessionAnswer` + future telemetry).

3. **Survey engine upgrade**
   - Replace the static 4-question loop with a **dynamic question generator** that takes prior answers and returns 3–5 follow-ups.
   - Add a `survey_phase` enum on `VisitorSession`: `registration`, `pre_arrival`, `on_site`, `post_event`.
   - Reuse existing `EventKnowledgeSearch` tool.

4. **My Page UI** (Blade)
   - `/my-page` dashboard: profile summary, recommended booths, route preview, chat panel.
   - QR-scan route `POST /my-page/scan` that activates on-site phase and injects booth context into the agent system prompt.

5. **Cost tiers**
   - Wrap LLM calls behind a `AiTiers` strategy: `rule` → `mini` (small/cheap model) → `pro` (frontier model). Tune triggers by question complexity and visitor value (e.g., VIP).

6. **Tests (Pest)**
   - `test_dynamic_questions_adapt_to_previous_answers`
   - `test_my_page_recommends_booths_matching_intent`
   - `test_qr_scan_switches_phase_and_injects_context`
   - `test_tier_strategy_routes_simple_questions_to_rule_or_mini`

---

### 3.2 Module 2 — AI Intent Inference Layer

> PDF §"Solution 2: AI Intent Inference Layer" (p.5)

**Goal:** Backend AI that integrates heterogeneous data and infers visitor intent as **structured information with confidence levels**.

**Current state in repo:** Knowledge base + embeddings already exist (`KnowledgeChunk`, `EventKnowledgeSearch`, `IndexKnowledge`). However, intent is *implicit* in survey answers — there is no explicit structured intent store.

**Build plan:**

1. **Schema**
   - New table `visitor_intents`:
     - `id`, `visitor_id`, `inference_key` (enum: `consideration_phase`, `interest_area`, `decision_involvement`, `implementation_period`, …),
     - `inference_value` (string),
     - `confidence` enum: `high|medium|low`,
     - `evidence` jsonb (source answers, knowledge chunk ids),
     - `inferred_at`, `inference_model` (e.g., `gpt-4o-mini`).
   - Indexes: (`visitor_id`, `inference_key`), GIN on `evidence`.

2. **Inference engine**
   - New action `app/Actions/InferVisitorIntent.php`:
     - Pulls: `SessionAnswer`s, `KnowledgeChunk` matches, behavior events (booth scans, dwell).
     - Prompts LLM with a **structured output schema** (Pydantic-style JSON).
     - Persists results with confidence labels.
   - New agent `IntentInferenceAgent` (uses `laravel/ai` structured output, `responseSchema()`).
   - Tag inferences as **inferences, not facts** — surfaced as such everywhere they are consumed.

3. **Batch + streaming**
   - Nightly job: `app/Jobs/RecomputeVisitorIntents.php` — for visitors active in the last 24h, re-run inference with fresh data.
   - On-survey-completion trigger: incremental update.

4. **Tuning hooks**
   - Per-organizer industry config in `config/ai_tuning.php` (e.g., food & bev vs. SaaS vs. manufacturing).
   - Organizers can override inference labels via `inference_label_overrides` table.

5. **Tests (Pest)**
   - `test_inference_returns_structured_schema_with_confidence`
   - `test_low_confidence_inference_is_flagged_in_storage`
   - `test_industry_tuning_changes_label_vocabulary`
   - `test_batch_job_recomputes_only_active_visitors`

---

### 3.3 Module 3 — Exhibitor Registration Mobile App (with Conversational Guidance)

> PDF §"Solution 3: Exhibitor registration mobile app (added functionality)" (p.6)

**Goal:** The instant a visitor's QR is scanned, the receptionist's device surfaces **AI-generated conversation guidance** — transforming the "first 30 seconds" of a business negotiation.

**Current state in repo:**
- `Registration` and `Visitor` models exist; the codebase already accepts a registration flow.
- No `Booth` ↔ `Visitor` "scan" event model. No mobile-app surface.

**Build plan:**

1. **Schema**
   - New `booth_visits` table:
     - `id`, `booth_id`, `visitor_id`, `exhibitor_user_id`, `scanned_at`, `dwell_seconds`, `context_card` jsonb, `raw_qr_token` string.
   - New `exhibitor_users` table (or pivot on `users`) with `booth_id`, `role` (`reception`, `manager`).
   - QR tokens: signed JWT containing `visitor_id` + `event_id`, short TTL.

2. **API**
   - `POST /api/booth-visits/scan` — body: `{qr_token, booth_id}`. Returns the `context_card` (visitor summary + conversation hints).
   - `POST /api/booth-visits/{id}/end-dwell` — closes the visit with dwell time.

3. **"Context Card" generation**
   - Pulls inferences from Module 2 (`visitor_intents`).
   - Calls a new **prompt transformer** `app/Ai/Prompts/ConversationHintPrompt.php` (proprietary per the PDF).
   - Returns a card:
     - Visitor basics (name, role, company).
     - **Topics of interest** (mapped from `interest_area` inferences).
     - **Recommended opener** (LLM-generated, not raw label).
     - **Subtle check points** (questions to weave into the conversation, e.g., ROI interest, integration status, phase).
     - **Recent visit history** (last 3 booths, dwell times).
   - Cache per (visitor, booth) for 5 minutes to keep scan latency low.

4. **Mobile-app surface**
   - The proposal calls for a mobile app; for v1 we deliver an **Inertia-free PWA** at `/exhibitor/app` that uses the device camera for QR scanning. (Inertia/React upgrade is a separate decision — see §6.)
   - Screens: scan, visitor card, end-dwell, history.

5. **Privacy & UX guardrails**
   - Never show raw AI labels ("Probability: high") to visitors. Card text is always phrased as **conversation guidance**, not profile.

6. **Tests (Pest)**
   - `test_scan_returns_context_card_in_under_500ms_cached`
   - `test_card_does_not_leak_raw_inference_labels`
   - `test_expired_qr_token_is_rejected`
   - `test_recent_visit_history_appears_on_card`

---

### 3.4 Module 4 — Copilot for Exhibitors

> PDF §"Solution 4: Copilot for Exhibitors" (p.7)

**Goal:** Free AI assistant that helps exhibitors complete preparation **before** they hit the organizer — preventing input errors and missed submissions upstream.

**Current state in repo:** No exhibitor-side copilot exists. Some scaffolding for "input assistance" may live inside the registration flow, but the proposal is broader.

**Build plan:**

1. **Schema**
   - New `exhibitor_documents` table: `id`, `exhibitor_id`, `type` (e.g., `hazardous_materials`, `electrical_capacity`), `status` (`draft|submitted|approved|rejected`), `payload` jsonb, `warnings` jsonb.
   - New `exhibitor_reminders` table: `id`, `exhibitor_id`, `trigger_at`, `channel` (`email|in_app`), `sent_at`, `template`.
   - New `exhibitor_qa_log` table: conversation log between exhibitor and copilot.

2. **Agent**
   - New `ExhibitorCopilotAgent` with three sub-tools:
     - `InputAssistTool` — context-aware suggestions as the exhibitor types.
     - `DocumentSupportTool` — fills hazardous-materials / electrical-capacity forms from examples.
     - `FaqTool` — RAG over organizer-provided knowledge base.
   - Escalation contract: any Q&A the FAQ cannot answer above a confidence threshold is **escalated to the organizer** via Module 5.

3. **Reminder engine**
   - Rules engine: `app/Actions/ScheduleExhibitorReminders.php` — given an `exhibitor_id` and current document statuses, schedule reminders at individualized times (not "T-7 days for everyone").
   - Dispatched via existing queue.

4. **UI (Blade)**
   - `/exhibitor/copilot` chat panel.
   - `/exhibitor/documents` document list with inline copilot suggestions.
   - `/exhibitor/reminders` notification center.

5. **Tests (Pest)**
   - `test_input_assist_warns_on_invalid_electrical_capacity`
   - `test_hazardous_materials_doc_requires_three_day_lead_time_warning`
   - `test_unsanswered_faq_is_escalated_to_organizer`
   - `test_reminder_schedule_is_individualized_not_uniform`

---

### 3.5 Module 5 — Autopilot Exhibitor Management for Organizers

> PDF §"Solution 5: Autopilot Exhibitor Management for Organizers" (p.8)

**Goal:** Automate document checking, reminders, and inquiry handling that organizers have traditionally done by hand. **Target: 70% workload reduction** vs. conventional methods.

**Current state in repo:** No organizer-side automation exists.

**Build plan:**

1. **Schema**
   - Extend `exhibitor_documents` (from §3.4) with `auto_decision` enum (`auto_approved|ai_prompted|escalated`), `decision_evidence` jsonb.
   - New `organizer_escalations` table: `id`, `exhibitor_id`, `topic`, `payload` jsonb, `status` (`open|resolved`), `past_case_refs` jsonb.
   - New `autopilot_audit_log` table: every AI decision with inputs/outputs for review.

2. **Pipeline**
   - On document submission: `app/Actions/ProcessExhibitorDocument.php`:
     1. Rule engine (organizer business rules) — fast checks.
     2. Generative AI — content + format review.
     3. Decision: `auto_approve` | `ai_prompt_fix` (minor) | `escalate` (major, attach past case examples).

3. **Cost-structure shift**
   - Pricing model moves from **man-month** to **fixed-cost AI**. This is an offering-level decision, but the platform should support per-event, per-exhibitor cost reporting for organizers (`cost_attribution` table).

4. **UI (Blade)**
   - `/organizer/autopilot` queue: AI-decided items, escalations, audit log.
   - One-click override with reason.

5. **Tests (Pest)**
   - `test_clean_document_is_auto_approved`
   - `test_minor_omission_triggers_ai_prompt_not_escalation`
   - `test_major_deficiency_is_escalated_with_past_case_refs`
   - `test_cost_attribution_reports_per_event_savings`

---

### 3.6 Module 6 — Dashboard for Organizers

> PDF §"Solution 6: Dashboard for Organizers" (p.9)

**Goal:** Real-time + annual analytics for accountability to exhibitors/sponsors, and data-driven planning for the next year.

**Current state in repo:** `Summary` model + `SummarizationAgent` + `GenerateSummary` exist; these are **post-event** summaries, not the live + comparative dashboard the proposal describes.

**Build plan:**

1. **Schema / read models**
   - New read model `event_analytics_snapshots` (materialized hourly):
     - `event_id`, `captured_at`,
     - `visitor_intent_distribution` jsonb (`under_comparison`, `info_gathering`, `specific_consideration`, `unclassified`),
     - `booth_access_counts` jsonb,
     - `venue_heat` jsonb (zone-based dwell sums).
   - New `event_yearly_kpis`: `event_id`, `year`, `visitors_total`, `exhibitor_retention_rate`, `ai_feature_adoption_rate`, etc.

2. **Real-time tiles**
   - Visitor intent distribution (pie/bar).
   - Booth access heatmap.
   - Live movement in venue.

3. **Exhibitor report**
   - "X decision-makers considering the budget visited your booth" — joins `visitor_intents` (decision_involvement = strong, implementation_period in budget-relevant range) with `booth_visits`.

4. **Annual comparison**
   - Side-by-side: 2024 vs. 2025 vs. 2026 — visitors, retention, intent trend shifts.

5. **AI planning proposals**
   - New `app/Actions/SuggestNextYearPlan.php`:
     - Aggregates inferred intents across attendees.
     - Asks `PlanningAgent` (LLM with structured output) for: session topics to add, capacity changes, theme emphasis.
     - Examples in PDF: "63% of attendees showed strong interest in 'ROI measurement'. We recommend doubling the number of slots allocated for related sessions."

6. **UI (Blade)**
   - `/organizer/dashboard` with role-gated tiles.
   - `/organizer/reports/exhibitor/{exhibitor_id}` per-exhibitor shareable report.
   - `/organizer/planning/next-year` AI-suggested plan with human approval.

7. **Tests (Pest)**
   - `test_real_time_tile_reflects_new_intent_within_5_minutes`
   - `test_exhibitor_report_counts_strong_decision_makers`
   - `test_annual_comparison_includes_prior_years`
   - `test_planning_proposal_is_evidence_backed_not_free_form`

---

## 4. Before/After Experience Map (PDF p.10)

| Stakeholder | Before | After (with Q-PASS) |
|---|---|---|
| **Organizer** | Dedicated staff for exhibitor mgmt; paperwork; no retention lever; qualitative explanations. | AI Autopilot automates processing; organizers focus on decisions; quantitative ROI evidence; retention trend visible. |
| **Exhibitor** | Intuition-based reception; uneven negotiation quality; no ROI proof; intuitive renewal. | AI conversation guidance from first 30s; core conversation early; better conversion/order value; quantitative ROI for internal use. |
| **Visitor** | Paper map wandering; no personalized routing; wasted time; no lasting satisfaction. | AI concierge personalizes route; efficient sightseeing; intent-matched encounters; higher satisfaction & repeat visits. |

> The three experience changes **cascade** and directly impact organizer business continuity. The dashboard (Module 6) is what makes this cascade measurable.

---

## 5. Effectiveness Verification (PDF p.11)

Three-tier verification, executed **during and after the demonstration event at ResorTech** during the subsidy period.

### Tier 1 — Exhibitor Post-Event Questionnaire
- Conducted immediately after the demo event.
- Cross-tabs **Q1 × Q2** to quantify: "exhibitors who used AI features had higher business-negotiation uplift".
  - Q1: How did the number of business negotiations this time compare to last time?
  - Q2: Did the AI function (visitor card) contribute to negotiation quality?
  - Q3: Which specific situations helped? (free response)
  - Q4: Do you intend to exhibit again next year?

### Tier 2 — Retention Rate (Lagging Indicator)
- Compare same-event retention rate next year against the **previous three years**.
- Slice by AI-feature utilization rate.

### Tier 3 — Behavioral Data (Quantitative)
- Implemented after one year of operation. Powers the AI accuracy improvement loop.
  - Appointment acquisition rate: card-viewed meetings vs. not.
  - Inference accuracy (accumulated exhibitor feedback).
  - Effectiveness by industry × event scale.

**Implementation in repo:**

- Add `effectiveness_surveys` table for Tier 1 responses.
- Add scheduled job `app/Jobs/ComputeRetentionRate.php` for Tier 2.
- Add telemetry events for Tier 3 (e.g., `card_viewed`, `meeting_scheduled`, `meeting_held`).
- Tests for each tier's calculation logic.

---

## 6. Joint Development — Division of Roles (PDF p.12)

| Area | Q-PASS (Copro System) | ISCO (Organizer) |
|---|---|---|
| **Product development** | Design, implementation, QA of all functions. | Review requirements, suggest improvements. |
| **AI training data** | Pipeline construction, inference engine development. | Provide business knowledge (document specs, reminder procedures, decision criteria). |
| **Business process analysis** | Design analysis methods, conduct interviews. | Disclose business processes, respond to interviews. |
| **Demonstration experiment** | Function provisioning, operational support, data-collection design. | Functional testing at ResorTech demo; on-site operation. |
| **Effectiveness verification** | Verification design, data analysis. | Survey cooperation from exhibitors/visitors; share results. |
| **Project promotion** | Overall progress management, subsidy office liaison. | Monthly regular meetings; participation in decision-making. |

**Implication for the codebase:** the architecture must keep the **organizer's business knowledge** isolated and editable by ISCO without code changes. This argues for:

- A `business_rules` table (organizer-editable rule definitions).
- A `knowledge_chunks` ownership flag (`source: organizer|qpass|system`).
- Role/permission split in `users` (exhibitor vs. organizer vs. admin).

---

## 7. Cross-Cutting Concerns

### 7.1 Three-Tier AI Cost Strategy
The proposal repeatedly emphasizes a **rule-based + lightweight AI + advanced AI** stack to control API spend. Build a `app/Ai/Tiers/` directory:

- `RuleBasedTier` — pre-LLM heuristics (regex, validators, lookups).
- `LightweightTier` — `gpt-4o-mini` / equivalent for routine work.
- `AdvancedTier` — frontier model for inference generation & planning.

A `TierRouter` decides per call. Log every tier decision for cost reporting (Module 6).

### 7.2 Data Flow Diagram (target)

```
[Visitor mobile/web]
        │  (survey answers, QR scan, dwell, route choices)
        ▼
[Data Acquisition Layer] ───► [booth_visits, session_answers, my_page_messages]
        │
        ▼
[Intelligence Layer]
        ├─► [EventKnowledgeSearch] (existing RAG)
        └─► [IntentInferenceAgent] ──► [visitor_intents]
                │                          ▲
                │                          │ (consumer)
        ┌───────┴───────┐                  │
        ▼               ▼                  │
[Visitor Concierge] [Context Card Gen] ─────┘
        │               │
        ▼               ▼
[Visitor My Page]  [Exhibitor PWA]

[Exhibitor Copilot] ◄────[business_rules, knowledge_chunks]
        │
        ▼ (clean docs)
[Autopilot Engine] ──► [organizer_escalations, audit_log]
        │
        ▼
[Dashboard] ◄──── [event_analytics_snapshots, effectiveness_surveys]
```

### 7.3 Frontend Decision (open)

The proposal is product-agnostic about UI. The repo currently uses **Blade**. Three options:

- **A. Stay on Blade** — fastest path; PWA at `/exhibitor/app` and `/my-page` are Blade + minimal JS (Alpine).
- **B. Add Inertia + React (v3)** — richer client experience for the dashboard and PWA; aligns with `inertiajs/inertia-laravel v3` and `@inertiajs/react v3` in `composer.json`. **Likely the right long-term choice.**
- **C. Hybrid** — Blade for organizer back-office, Inertia for visitor/exhibitor-facing surfaces.

Recommendation: **B**, with a phased rollout. Trigger: confirm with stakeholder before kicking off Module 6 dashboard.

### 7.4 Privacy & Compliance

- AI inferences are explicitly **labeled as inferences, not facts** everywhere they are exposed to users (visitor cards, dashboards).
- Raw inference labels are never shown to visitors.
- QR tokens are short-lived and signed.
- Audit log for every AI decision (autopilot + inference).
- Right-to-explanation: each inference row carries `evidence` jsonb (source answers / chunks).

### 7.5 Test Strategy (per project rules)

- Every change must be tested. Use Pest (`pestphp/pest v4`).
- Run minimally-scoped tests: `php artisan test --compact --filter=...`.
- Test tiers:
  - Unit: prompt builders, tier router, schema validators.
  - Feature: scan → context card, document submission → autopilot decision.
  - Architecture: `arch()` tests to prevent model-bloat / layer leaks.

### 7.6 Code Quality

- After any PHP change: `vendor/bin/pint --dirty --format agent`.
- Follow Laravel 13 conventions (constructor promotion, return types, PHPDoc array shapes).
- Use `php artisan make:` with `--no-interaction` for all new files.

---

## 8. Phased Delivery Plan

> ResorTech demonstration is the external deadline. The internal phasing below sequences work so that each module is independently demoable.

### Phase 0 — Foundations (1 sprint)
- Add `inertiajs/inertia-laravel` + `@inertiajs/react` decision (see §7.3).
- Add `business_rules` and `users.role` split.
- Add `AiTiers` infrastructure and cost logging.
- New `event_analytics_snapshots` migration scaffolding.

### Phase 1 — Module 2 (AI Intent Inference) first
- It's the **dependency** for Modules 1, 3, 6.
- Ships structured `visitor_intents` + nightly batch job.
- ✅ Demoable: "Show me inferred intents for the last 50 visitors."

### Phase 2 — Module 1 (Visitor AI Concierge)
- My Page + dynamic survey + booth recommendations.
- ✅ Demoable: full visitor flow from registration to on-site QR.

### Phase 3 — Module 3 (Exhibitor Mobile App / PWA)
- QR scan → context card.
- ✅ Demoable: "Scan this visitor's badge — here's the AI-generated opener."

### Phase 4 — Module 4 (Exhibitor Copilot)
- Document drafting, reminders, FAQ.
- ✅ Demoable: "Submit a hazardous-materials form with the copilot's help."

### Phase 5 — Module 5 (Autopilot for Organizers)
- Document pipeline + escalations.
- ✅ Demoable: organizer inbox shows 70% auto-handled items.

### Phase 6 — Module 6 (Dashboard) + Verification Suite
- Real-time tiles, exhibitor reports, annual comparison, AI planning proposals.
- Effectiveness verification tiers 1–3 wired up.
- ✅ Demoable: end-to-end before/after story at ResorTech.

### Phase 7 — Polish
- Mobile-app polish, PWA install prompt, offline caching.
- Cost reports surfaced to organizers.
- Load testing for inference batch jobs.

---

## 9. Mapping Summary — Proposal → Repository

| Proposal Module | Existing in Repo | To Build (key files / dirs) |
|---|---|---|
| Visitor AI Concierge | Partial: `SurveyAgent`, `VisitorSession`, `ProcessAnswer` | `VisitorConciergeAgent`, `MyPageController`, `booth_recommendations`, `my_page_messages`, dynamic-question engine |
| AI Intent Inference Layer | RAG only (`KnowledgeChunk`, `EventKnowledgeSearch`) | `IntentInferenceAgent`, `InferVisitorIntent` action, `visitor_intents` table, batch job |
| Exhibitor App (QR + card) | Registration flow only | `booth_visits` table, scan API, PWA surface, `ConversationHintPrompt` |
| Copilot for Exhibitors | None | `ExhibitorCopilotAgent`, `exhibitor_documents`, `exhibitor_reminders`, `exhibitor_qa_log` |
| Autopilot for Organizers | None | `ProcessExhibitorDocument` action, `organizer_escalations`, `autopilot_audit_log`, decision pipeline |
| Dashboard for Organizers | `Summary`, `SummarizationAgent` (post-event) | `event_analytics_snapshots`, real-time tiles, `SuggestNextYearPlan`, planning UI |
| Effectiveness Verification | None | `effectiveness_surveys`, `ComputeRetentionRate` job, telemetry events |

---

## 10. Open Questions / Approvals Needed

1. **Frontend stack** — confirm Inertia + React (v3) vs. Blade-only. *(Affects every page in Modules 1, 3, 4, 5, 6.)*
2. **LLM provider** — current setup uses OpenAI via OpenRouter (`text-embedding-3-small`, GPT-4o). Confirm for the new modules; consider an explicit failover config (multi-provider) for demo reliability.
3. **Organizer-editable rules** — confirm the `business_rules` table UX with ISCO before Module 5.
4. **Mobile app** — confirm PWA is acceptable for the demo, or whether a true native/hybrid build is required.
5. **ResorTech demo window** — locks Phase 6's last-merge date.
6. **Data retention** — how long to keep raw survey text vs. aggregated inferences? Affects schema and GDPR-style obligations.

---

*End of plan.md. This document is the single source of truth for sequencing the Q-PASS proposal against the existing Laravel 13 codebase. Update after every milestone.*
