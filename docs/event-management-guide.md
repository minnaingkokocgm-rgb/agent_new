# Event & Booth Management Guide

## Overview

This guide covers how to create and manage events, booths, and knowledge bases for the AI-Powered Event Survey System.

---

## Creating an Event

### Via Admin UI

1. Navigate to **/admin/events**
2. Click **"Create Event"**
3. Fill in:
   - **Event Name** — A descriptive name (e.g., "TechConf 2026")
   - **Description** — Brief overview of the event. The AI uses this for context during surveys.
4. Click **"Create Event"**

### Via API

```bash
curl -X POST http://localhost:8000/api/events \
  -H "Content-Type: application/json" \
  -d '{
    "name": "TechConf 2026",
    "description": "The premier technology conference showcasing AI, cloud, and devtools."
  }'
```

### Via Tinker

```bash
php artisan tinker --execute "App\Models\Event::create(['name' => 'TechConf 2026', 'description' => 'The premier technology conference showcasing AI, cloud, and devtools.'])"
```

---

## Creating Booths

Booths belong to events and allow booth-specific surveys. Each booth can have its own knowledge base for targeted AI questions.

### Via Admin UI

1. Open an event at **/admin/events/{eventId}**
2. In the **"Booths"** section, click **"+ Add Booth"**
3. Enter:
   - **Booth name** — e.g., "AI Demo Station"
   - **Booth description** — What the booth showcases
4. Click **"Create Booth"**

### Via API

```bash
curl -X POST http://localhost:8000/api/events/1/booths \
  -H "Content-Type: application/json" \
  -d '{
    "name": "AI Demo Station",
    "description": "Live demos of AI-powered developer tools including code generation, testing, and deployment."
  }'
```

### Scoped Survey Links

When a booth exists, visitors can be directed to a booth-scoped survey:

```
/s/{eventId}/booth/{boothId}
```

This tells the AI to focus questions on that specific booth.

---

## Uploading Knowledge Base

The knowledge base gives the AI agent context about your event. Without it, the AI asks generic questions. With it, the AI can ask informed, relevant questions.

### Via Admin UI

1. Open an event at **/admin/events/{eventId}**
2. In the **"Knowledge Base"** section:
   - **Source Name** — A label for the document (e.g., `techconf-overview.txt`)
   - **Document Content** — Paste the knowledge base text
3. Click **"Upload & Index Knowledge"**

### Knowledge Base Format

Write in **plain text**, structured with clear sections for best results:

```
# Section headings help the chunker group related content

Use paragraphs of 100-500 words. The system chunks at ~500 words
with 50-word overlap. Each chunk gets its own vector embedding.

Include specific details:
- Product names, features, pricing
- Speaker bios and session topics
- Booth locations and descriptions
- Frequently asked questions
- Key statistics and facts
```

### What to Include

| Section | Description |
|---|---|
| Event Overview | Date, location, theme, expected attendance |
| Schedule | Keynote times, session tracks, workshops |
| Speakers | Names, titles, topics, bios |
| Exhibitors / Booths | Company names, products, booth numbers |
| FAQ | Common questions attendees ask |
| Technical Details | APIs, integrations, tech stack, specs |
| Pricing / Plans | Ticket tiers, product pricing, packages |

### Via API

```bash
curl -X POST http://localhost:8000/api/events/1/knowledge \
  -H "Content-Type: application/json" \
  -d '{
    "content": "Your knowledge base text here...",
    "source_name": "event-overview.txt",
    "booth_id": null
  }'
```

To scope knowledge to a specific booth, include `booth_id`.

### How It Works

1. Text is chunked into ~500-word segments with 50-word overlap
2. Each chunk is embedded using `text-embedding-3-small` (1536 dimensions)
3. Embeddings are stored in PostgreSQL with pgvector HNSW index
4. During a survey, the AI searches for the top-5 most relevant chunks
5. Retrieved chunks serve as context for the AI's next question

---

## Getting the Survey Link

After creating an event, share the survey link with visitors:

```
http://localhost:8000/s/{eventId}
```

The **"Survey Link"** section on the event detail page shows the link with a **Copy** button.

---

## Viewing AI Summaries

After visitors complete surveys, generate an AI summary:

1. Navigate to **/admin/events/{eventId}/summary**
2. Click **"Generate Summary"** (or **"Regenerate"** to refresh)

The summary includes:
- Total visitors
- Key themes and top interests
- Sentiment analysis (positive / neutral / negative)
- Actionable insights
- Recommendations

### Via API

```bash
# Get existing summary
curl http://localhost:8000/api/events/1/summary

# Force regenerate
curl -X POST http://localhost:8000/api/events/1/summary/regenerate
```

---

## Quick Reference: All API Endpoints

| Method | Path | Purpose |
|---|---|---|
| GET | `/api/events` | List all events |
| POST | `/api/events` | Create event |
| GET | `/api/events/{event}` | Get event details |
| PUT | `/api/events/{event}` | Update event |
| DELETE | `/api/events/{event}` | Delete event (cascades) |
| POST | `/api/events/{event}/booths` | Create booth |
| POST | `/api/events/{event}/knowledge` | Upload & index knowledge |
| POST | `/api/survey/start` | Start a survey session |
| POST | `/api/survey/{session}/answer` | Submit survey answer |
| GET | `/api/survey/{session}` | View session Q&A |
| POST | `/api/survey/{session}/complete` | Force-complete session |
| GET | `/api/events/{event}/summary` | Get event summary |
| GET | `/api/booths/{booth}/summary` | Get booth summary |
| GET | `/api/visitors/{visitor}/summary` | Get visitor profile |
| POST | `/api/events/{event}/summary/regenerate` | Regenerate summary |
