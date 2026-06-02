# Context — AI-Powered Event/Booth RAG Survey System

> Generated 2026-06-01 after extensive UI/UX improvements. Use this to recover context in a new session.

---

## Quick Start

```bash
# 1. Start PostgreSQL
docker compose up -d

# 2. Build & serve
npm run build
php artisan serve &
npm run dev &

# 3. Open: http://localhost:8000/admin/events
#    Login: test@example.com / password
#    Survey: http://localhost:8000/s/{event_id}
```

### Test Database (Isolated)
Tests use `survey_test` — separate from dev `survey`. Dev data survives test runs.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 13, PHP 8.5 |
| Frontend | Inertia v3, React 19, Tailwind v4, shadcn/ui |
| Database | PostgreSQL 17 + pgvector (Docker) |
| AI | Laravel AI SDK → OpenRouter (`openai/gpt-4o`, `text-embedding-3-small`) |
| Auth | Laravel Fortify v1 (login, register, passkeys, 2FA, email verify) |
| Testing | Pest 4 (64 tests, all passing) |

---

## Current UI State (After Overhaul)

### Color Theme: Blue (no purple/indigo)
All `indigo-*` replaced with `blue-*`:
- Buttons: `bg-blue-600 hover:bg-blue-700 text-white`
- Links: `text-blue-600 hover:text-blue-700`
- Focus rings: `focus:border-blue-500 focus:ring-blue-500`
- Stats highlight: `bg-blue-50 text-blue-700`

### Layout: Full-Width Content
- No `max-w-*` centering — content fills available space naturally
- Consistent padding: `px-6 py-6` on all admin pages
- Page headings: `text-xl font-semibold` (not `text-2xl font-bold`)
- Subtitles: `mt-0.5 text-sm text-muted-foreground`

### Dark Mode: Full Support
All custom pages use theme-aware CSS variable colors instead of hardcoded Tailwind colors:
- `text-foreground` / `text-muted-foreground` (not `text-neutral-900`/`text-neutral-600`)
- `bg-card` / `bg-muted` (not `bg-white` / `bg-neutral-50`)
- `border-border` / `border-input` (not `border-neutral-200` / `border-neutral-300`)

### Sidebar
- Single sidebar (no double-nesting)
- Branding: env `APP_NAME` ("Laravel")
- Nav: Events only (Dashboard removed)
- Logo links to `/admin/events`
- Footer: Documentation link only

### Survey Chat
- Card constrained to viewport: `max-h-[calc(100vh-2rem)]`
- Chat area fills remaining space: `flex-1 min-h-0` (not fixed `h-96`)
- Proper scrolling when messages overflow
- Auto-scroll to bottom on new messages
- Visitor messages: `bg-blue-600 text-white`, AI messages: `bg-muted text-foreground`

---

## Pages & Components Map

```
resources/js/pages/
├── admin/events/
│   ├── index.tsx       ← Event cards grid (3-col on lg)
│   ├── create.tsx      ← EventForm wrapper (full-width)
│   ├── show.tsx        ← Dashboard: survey link, sessions, booths, knowledge, summary
│   └── summary.tsx     ← SummaryCard with regenerate
├── survey/
│   ├── chat.tsx        ← Full chat widget (no layout)
│   └── complete.tsx    ← Thank-you page (no layout)
└── dashboard.tsx       ← Redirected → /admin/events (kept for starter kit compat)

resources/js/components/
├── admin/
│   ├── event-form.tsx      ← fetch() not Inertia useForm (avoids JSON→Inertia error)
│   ├── knowledge-upload.tsx ← fetch() to /api/events/{id}/knowledge
│   └── summary-card.tsx    ← AI summary display with regenerate
├── survey/
│   ├── chat-window.tsx     ← Message bubbles, typing dots, auto-scroll
│   └── chat-input.tsx      ← Text input + send button
└── app-sidebar.tsx         ← Single nav: Events only
```

---

## Routes

### Web (Inertia pages)
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

### API (JSON)
| Method | Path | Auth |
|---|---|---|
| GET/POST/PUT/DELETE | `/api/events/*` | none |
| POST | `/api/events/{event}/booths` | none |
| POST | `/api/events/{event}/knowledge` | none |
| POST | `/api/survey/start` | none |
| POST | `/api/survey/{session}/answer` | none |
| GET/POST | `/api/events/{event}/summary*` | none |
| GET | `/api/booths/{booth}/summary` | none |

---

## AI Agents

### SurveyAgent (`app/Ai/Agents/SurveyAgent.php`)
- Model: `openai/gpt-4o` via OpenRouter
- Traits: `Promptable`, `RemembersConversations`
- Tools: `EventKnowledgeSearch`
- Asks 4-5 adaptive questions, ends with `[SURVEY_COMPLETE]`

### SummarizationAgent (`app/Ai/Agents/SummarizationAgent.php`)
- Model: `openai/gpt-4o` (was `claude-sonnet-4` — 400 error, switched)
- Returns JSON as plain text (not structured output)
- `GenerateSummary` action has robust JSON parsing (strips ``` fences, extracts `{...}`)

---

## Auth Redirects

Custom Fortify responses force redirect to `/admin/events` after login/register (ignore intended URL):

| File | Purpose |
|---|---|
| `app/Http/Responses/LoginResponse.php` | Always → `/admin/events` |
| `app/Http/Responses/RegisterResponse.php` | Always → `/admin/events` |
| `app/Providers/FortifyServiceProvider.php` | Binds both as singletons |
| `config/fortify.php` | `home` = `/admin/events` |

---

## Database

### Connection
```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=survey        ← Development
DB_USERNAME=survey
DB_PASSWORD=password
```

### Test Database (ISOLATED)
`phpunit.xml` uses `DB_DATABASE=survey_test` — completely separate from dev. Tests use `LazilyRefreshDatabase` but only affect `survey_test`. Dev data is safe.

Docker creates `survey_test` via `docker/init-test-db.sql` on container startup.

---

## Key Files Created/Modified This Session

### New Files
- `app/Http/Responses/LoginResponse.php`
- `app/Http/Responses/RegisterResponse.php`
- `docker/init-test-db.sql`
- `docs/event-management-guide.md`
- `docs/knowledge-base-techconf-2026.md`
- `docs/knowledge-base-devtool-ai-booth-101.md`

### Major Modifications
- `resources/js/pages/admin/events/*.tsx` — removed AppLayout double-wrap, full-width, blue theme, sizing
- `resources/js/pages/survey/chat.tsx` — fixed scrolling (flex-1 min-h-0)
- `resources/js/components/survey/chat-window.tsx` — flex fill + scroll
- `resources/js/components/admin/event-form.tsx` — fetch() instead of Inertia useForm
- `resources/js/components/app-sidebar.tsx` — removed Dashboard nav
- `app/Ai/Agents/SummarizationAgent.php` — model: gpt-4o, stronger JSON prompt
- `app/Actions/GenerateSummary.php` — robust JSON parsing
- `config/fortify.php` — home: /admin/events
- `routes/web.php` — dashboard redirect, removed verified from dashboard
- `phpunit.xml` — DB_DATABASE=survey_test
- `tests/Feature/Auth/*.php` + `DashboardTest.php` — updated assertions
- `resources/js/components/app-logo.tsx` — uses env APP_NAME
- `resources/js/pages/welcome.tsx` — events link instead of dashboard

---

## Key Gotchas

1. **API endpoints return JSON, not Inertia responses** — use `fetch()` in React components, never Inertia `useForm` for API calls
2. **Test DB is `survey_test`** — create it if not exists: `docker compose exec postgres psql -U survey -d survey -c "CREATE DATABASE survey_test;"`
3. **AgentResponse::text** is a property, not a method: `$response->text`
4. **EmbeddingsResponse::embeddings** is an array property: `$response->embeddings[0]`
5. **$request->string('query')** returns Stringable — call `->value()` for embedding input
6. **pgvector only on PostgreSQL** — migrations check `DB::getDriverName() === 'pgsql'`
7. **Sidebar double-nesting** happens if page wraps `<AppLayout>` — never do this, it's provided by `app.tsx`
8. **Fortify v1** uses container singletons for response contracts, not `Fortify::loginResponseUsing()`

---

## Running Tests

```bash
php artisan test --compact              # All 64 tests (isolated DB, dev data safe)
php artisan test --compact --filter=SurveyFlow    # Specific test
```

---

## Knowledge Base Documents

Ready-to-upload files in `docs/`:
- `knowledge-base-techconf-2026.md` — Full event knowledge (10.6KB)
- `knowledge-base-devtool-ai-booth-101.md` — Booth-specific (6.7KB)

Upload via: Event detail page → Knowledge Base section → paste content → "Upload & Index Knowledge"

---

## Current Limitations / Next Steps

- [ ] API endpoints lack auth middleware (summarization, event management are public)
- [ ] Settings/profile pages still use starter kit purple (not converted to blue)
- [ ] Dashboard page exists but unused (redirects to events)
- [ ] No pagination on events list or sessions
- [ ] Survey has no idle timeout / session expiry UI
- [ ] No admin dashboard analytics (visitor counts, trends)
