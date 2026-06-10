# Context — AI-Powered Event/Booth RAG Survey System

> Generated 2026-06-02 after major frontend migration and registration feature addition.
> Last updated 2026-06-10 — removed Vite/Tailwind cruft, fixed stale doc references.
> Use this to recover context in a new session.

---

## Quick Start

```bash
# 1. Start PostgreSQL
docker compose up -d

# 2. Serve
php artisan serve &

# 3. Open: http://localhost:8000/admin/events
#    Login: test@example.com / password
#    Survey: http://localhost:8000/s/{event_id}
#    Registration: http://localhost:8000/s/{event_id}/register
```

### Test Database (Isolated)
Tests use `survey_test` — separate from dev `survey`. Dev data survives test runs.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 13.12, PHP 8.5 |
| Frontend | **Blade templates + Bootstrap 5.3 + jQuery 3.7.1** (was Inertia/React/Tailwind) |
| CSS | Hand-written `public/css/app.css` + Bootstrap 5.3 CDN + Bootstrap Icons CDN |
| Database | PostgreSQL 17 + pgvector (Docker) |
| AI | Laravel AI SDK v0.7.2 → OpenRouter (`openai/gpt-4o`, `text-embedding-3-small`) |
| Auth | Laravel Fortify v1.37.2 (login, register, passkeys, 2FA, email verify) |
| Testing | Pest 4.7 (72 tests, all passing) |

### Frontend Dependencies
- **CDN-loaded** (in `layouts/app.blade.php`): Bootstrap 5.3.3 CSS + JS, Bootstrap Icons 1.11.3, jQuery 3.7.1, Axios 1.7.9
- **Local**: `public/css/app.css` (126 lines, hand-written custom CSS for chat bubbles, survey layout, event cards, etc.)

### npm package (`package.json`)
- `concurrently` v9 — used by `composer run dev` to run server + queue together

**Note:** Inertia, Wayfinder, Sail, Vite, Tailwind, and React are **not installed** (removed during/after the Blade migration).

---

## Major Changes Since Last Context

### 1. Frontend Migration: Inertia+React → Blade+Bootstrap
All pages are now **server-rendered Blade views** with Bootstrap 5 instead of client-side Inertia+React+Tailwind. The Inertia middleware and React components (`resources/js/pages/`) have been replaced by Blade templates under `resources/views/`. Vite, Tailwind, and `vite.config.js` have been removed (2026-06-10) — the app uses CDN-loaded Bootstrap + hand-written `public/css/app.css` exclusively.

### 2. Visitor Registration with AI Assistant
A full registration system with an AI chat assistant sidebar:
- **Registration page**: Split layout — form on the left (comprehensive fields: 9 occupations, 7 age ranges, location, etc.), AI assistant chat on the right
- **Form fields**: name, email, phone, company, organization, job_title, occupation (9 categories), age_range (7 ranges), post_code, address, country, source, notes, opt_out
- **AI Assistant**: `RegistrationAssistantAgent` answers questions about the form and event, uses `EventKnowledgeSearch` tool
- **Flow**: Visitor opens form → AI greets them → they can ask questions while filling → submit → redirect to survey with visitor_id
- **API**: `POST /api/registration/start` → `POST /api/registration/ask` → `POST /api/registration/submit`

### 3. Dynamic Survey Targeting
SurveyAgent now adapts questions based on visitor registration data:
- **Occupation-based strategies**: Different question focus for company owners (ROI/partnerships), employees (approval process), sole proprietors (niche/cost), investors (market trends), students (learning/career), retirees (hobbies/community), etc.
- **Age-based adjustments**: Young visitors → innovation/trends, mid-career → business value, experienced → quality/reliability, seniors → patience/enjoyment
- **Greeting fix**: Agent no longer repeats "Welcome to [event]" in every exchange; explicit instructions prevent re-greetings

### 4. RegistrationAssistantAgent
- Model: `openai/gpt-4o`, temp 0.7, max 8 steps, max 1024 tokens
- Traits: `Promptable`, `RemembersConversations`
- Tools: `EventKnowledgeSearch`
- Purpose: Helps visitors understand the registration form and event details

### 5. Registration Model
- Fields: event_id, name, email, phone, company, organization, job_title, occupation, age_range, post_code, address, country, source, notes, opt_out, document_path, session_token, status, metadata (jsonb), visitor_id
- Uses `session_token` (UUID7) for AI chat session tracking
- Status flow: `pending` → `submitted`
- Creates and links to Visitor record on submit

---

## UI Theme: Bootstrap Blue

- Navbar: `bg-primary` (Bootstrap blue #0d6efd)
- Buttons: `btn-primary` / `btn-outline-primary`
- Borders: `border-primary`
- Badges: `bg-primary`
- Chat bubbles: `.chat-bubble.user` uses `background: #0d6efd; color: #fff`, `.chat-bubble.ai` uses `background: #f0f0f0`

---

## Pages & Components Map

```
resources/views/
├── layouts/
│   └── app.blade.php          ← Single layout: Bootstrap navbar, flash messages, footer, jQuery + Bootstrap JS
├── admin/events/
│   ├── index.blade.php        ← Event cards grid (Bootstrap col-md-6 col-lg-4)
│   ├── create.blade.php       ← Event form (fetch() to API)
│   ├── show.blade.php         ← Dashboard: survey link, sessions table, booths, knowledge base, summary
│   └── summary.blade.php      ← AI summary cards with regenerate button
├── survey/
│   ├── chat.blade.php         ← Chat widget (full-screen, hides navbar/footer)
│   ├── complete.blade.php     ← Thank-you page
│   ├── register.blade.php     ← Split layout: form + AI chat assistant
│   └── register-complete.blade.php ← Registration thank-you
├── auth/                      ← Fortify auth views (Bootstrap styled)
│   ├── login.blade.php
│   ├── register.blade.php
│   ├── forgot-password.blade.php
│   ├── reset-password.blade.php
│   ├── confirm-password.blade.php
│   ├── two-factor-challenge.blade.php
│   └── verify-email.blade.php
├── settings/
│   ├── profile.blade.php      ← Update name/email, delete account
│   └── security.blade.php     ← Update password, 2FA, passkeys
└── welcome.blade.php          ← Landing page with feature cards
```

---

## AI Agents (3 total)

### SurveyAgent (`app/Ai/Agents/SurveyAgent.php`)
- Model: `openai/gpt-4o`, temp 0.7, max 12 steps, max 1024 tokens
- Traits: `Promptable`, `RemembersConversations`
- Tools: `EventKnowledgeSearch`
- **Dynamic targeting**: Builds occupation-specific survey strategy (8 occupation types) + age-based adjustments
- Asks 3-4 adaptive questions tailored to visitor profile, ends with `[SURVEY_COMPLETE]`
- **No repeated greetings**: Explicit instructions prevent re-greeting in subsequent exchanges

### SummarizationAgent (`app/Ai/Agents/SummarizationAgent.php`)
- Model: `openai/gpt-4o`, temp 0.3
- Returns JSON as plain text (not structured output)
- Keys: total_visitors, key_themes, demographics, sentiment, actionable_insights, top_interests, recommendations

### RegistrationAssistantAgent (`app/Ai/Agents/RegistrationAssistantAgent.php`)  ← NEW
- Model: `openai/gpt-4o`, temp 0.7, max 8 steps, max 1024 tokens
- Traits: `Promptable`, `RemembersConversations`
- Tools: `EventKnowledgeSearch`
- Helps visitors with registration form questions, explains fields, answers event questions

---

## Routes

### Web (Blade pages)
| Path | Page | Auth |
|---|---|---|
| `/` | welcome | public |
| `/dashboard` | → redirect to `/admin/events` | auth |
| `/admin/events` | Events index | auth+verified |
| `/admin/events/create` | Create event form | auth+verified |
| `/admin/events/{event}` | Event detail | auth+verified |
| `/admin/events/{event}/summary` | AI summary | auth+verified |
| `/s/{event}` | Survey chat | public |
| `/s/{event}/booth/{boothId}` | Booth-scoped survey | public |
| `/s/{event}/complete` | Thank you | public |
| `/s/{event}/register` | Registration form + AI chat | public |
| `/s/{event}/register/complete` | Registration thank you | public |
| `/settings/profile` | Profile settings | auth |
| `/settings/security` | Security settings | auth |

### API (JSON)
| Method | Path | Auth |
|---|---|---|
| GET/POST/PUT/DELETE | `/api/events/*` | none |
| POST | `/api/events/{event}/booths` | none |
| POST | `/api/events/{event}/knowledge` | none |
| POST | `/api/survey/start` | none |
| POST | `/api/survey/{session}/answer` | none |
| GET | `/api/survey/{session}` | none |
| POST | `/api/survey/{session}/complete` | none |
| POST | `/api/registration/start` | none |
| POST | `/api/registration/ask` | none |
| POST | `/api/registration/submit` | none |
| GET | `/api/events/{event}/summary` | none |
| GET | `/api/booths/{booth}/summary` | none |
| GET | `/api/visitors/{visitor}/summary` | none |
| POST | `/api/events/{event}/summary/regenerate` | none |

---

## Database Models (10 total)

- `User` — Fortify auth
- `Event` — has booths, sessions, knowledge chunks, summaries, registrations
- `Booth` — belongs to event, has sessions
- `KnowledgeChunk` — vector-embedded (pgvector), belongs to event/booth
- `Visitor` — has sessions
- `VisitorSession` — belongs to event, booth, visitor; has questions + answers
- `SessionQuestion` — belongs to session
- `SessionAnswer` — belongs to question, session, visitor
- `Summary` — cached AI results, belongs to event (polymorphic)
- `Registration` — **NEW** event registrations with AI chat tracking

---

## Auth Redirects

Custom Fortify responses force redirect to `/admin/events` after login/register (ignore intended URL):

| File | Purpose |
|---|---|
| `app/Http/Responses/LoginResponse.php` | Redirects to `/admin/events` (respects intended URL if set) |
| `app/Http/Responses/RegisterResponse.php` | Always → `/admin/events` |
| `app/Providers/FortifyServiceProvider.php` | Binds both as singletons |
| `config/fortify.php` | `home` = `/admin/events` |

---

## Database

### Connection
```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5433
DB_DATABASE=survey_new     ← Development
DB_USERNAME=survey
DB_PASSWORD=password
```

### Test Database (ISOLATED)
`phpunit.xml` uses `DB_DATABASE=survey_test` — completely separate from dev. Tests use `LazilyRefreshDatabase` but only affect `survey_test`. Dev data is safe.

Docker creates `survey_test` via `docker/init-test-db.sql` on container startup.

---

## Key Files

### 2026-06-10 — Vite/Tailwind Removal
- Removed `vite`, `tailwindcss`, `@tailwindcss/vite`, `laravel-vite-plugin` from `package.json`
- Deleted `vite.config.js`, `resources/js/app.js`, `resources/css/app.css`, `public/build/`
- Updated `composer.json` dev/setup scripts to no longer reference npm build/dev
- App now exclusively uses CDN Bootstrap + hand-written `public/css/app.css`

### New / Changed This Session
- `context.md` — updated with registration fields, dynamic targeting, greeting fix
- `app/Ai/Agents/SurveyAgent.php` — added `buildTargetingStrategy()` for occupation/age-based survey adaptation
- `app/Actions/StartSurveySession.php` — updated prompt to prevent generic greetings
- `app/Actions/ProcessAnswer.php` — updated prompts to prevent repeated greetings
- `app/Http/Controllers/Api/RegistrationController.php` — validates new form fields (post_code, address, organization, occupation, age_range, opt_out)
- `app/Models/Registration.php` — added new fields to fillable
- `app/Models/Visitor.php` — added new fields to fillable
- `resources/views/survey/register.blade.php` — comprehensive form with occupation/age dropdowns, location fields, opt-out checkbox
- `database/migrations/2026_06_07_102502_add_form_fields_to_registrations_and_visitors.php` — adds post_code, address, organization, occupation, age_range, opt_out
- `tests/Feature/RegistrationFlowTest.php` — updated to test new fields

### Reference Docs
- `docs/jfex-survey-simulation.md` — JFEX survey simulation guide
- `docs/survey-agent-interview-only-system-prompt-2026-06-05.md` — archived system prompt variant
- `docs/survey-agent-system-prompt-backup-2026-06-05.md` — system prompt backup

### Previous Session (still relevant)
- `app/Http/Responses/LoginResponse.php`
- `app/Http/Responses/RegisterResponse.php`
- `docker/init-test-db.sql`

---

## Key Gotchas

1. **API endpoints return JSON, not Inertia responses** — use `fetch()` or `$.ajax()` in Blade views, never Inertia `useForm`
2. **Test DB is `survey_test`** — create it if not exists: `docker compose exec postgres psql -U survey -d survey_new -c "CREATE DATABASE survey_test;"`
3. **AgentResponse::text** is a property, not a method: `$response->text`
4. **EmbeddingsResponse::embeddings** is an array property: `$response->embeddings[0]`
5. **$request->string('query')** returns Stringable — call `->value()` for embedding input
6. **pgvector only on PostgreSQL** — migrations check `DB::getDriverName() === 'pgsql'`
7. **Fortify v1** uses container singletons for response contracts, not `Fortify::loginResponseUsing()`
8. **Bootstrap + jQuery** are CDN-loaded in `layouts/app.blade.php` — no npm install needed for them
9. **Survey chat pages** hide the navbar and footer via CSS (`.survey-page .navbar { display: none; }`)
10. **Registration chat** uses `session_token` (UUID7) to track the AI conversation across requests
11. **$.ajaxSetup** in the layout sets CSRF token header globally for all jQuery AJAX calls

---

## Running Tests

```bash
php artisan test --compact              # All 72 tests (isolated DB, dev data safe)
php artisan test --compact --filter=SurveyFlow       # Specific test
php artisan test --compact --filter=RegistrationFlow # Registration flow tests
```

---

## Current Limitations / Next Steps

- [ ] API endpoints lack auth middleware (summarization, event management, registration are public)
- [ ] No pagination on events list or sessions
- [ ] Survey has no idle timeout / session expiry UI
- [ ] No admin dashboard analytics (visitor counts, trends)
- [ ] Registration documents (`document_path`) not yet implemented (form has no file upload)
- [ ] Registration `status` field only uses `pending`/`submitted` — `reviewed`/`approved`/`rejected` not wired up
- [ ] Survey chat and registration chat use similar but separate UI code (could be DRYed)
