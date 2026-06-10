# Database Schema Documentation

## Database: `survey_new`

**Engine:** PostgreSQL (pgvector extension enabled)  
**Host:** 127.0.0.1:5433  
**Connection:** `pgsql`

---

## Tables Overview

| # | Table | Description |
|---|-------|-------------|
| 1 | `users` | Application admin users |
| 2 | `password_reset_tokens` | Password reset tokens for users |
| 3 | `sessions` | User session management |
| 4 | `cache` | Application cache storage |
| 5 | `cache_locks` | Cache lock management |
| 6 | `jobs` | Queue jobs |
| 7 | `job_batches` | Batched queue jobs |
| 8 | `failed_jobs` | Failed queue jobs |
| 9 | `passkeys` | WebAuthn passkeys for user auth |
| 10 | `agent_conversations` | AI agent conversation threads |
| 11 | `agent_conversation_messages` | Individual messages in conversations |
| 12 | `events` | Event definitions |
| 13 | `booths` | Booths within events |
| 14 | `knowledge_chunks` | **Vector-embedded knowledge base chunks** |
| 15 | `visitors` | Event visitors |
| 16 | `visitor_sessions` | Visitor chat sessions at booths |
| 17 | `session_questions` | Questions asked in sessions |
| 18 | `session_answers` | Answers to session questions |
| 19 | `summaries` | Generated summaries |
| 20 | `registrations` | Event registrations |

---

## Table Schemas

### 1. `users`

| Column | Type | Notes |
|--------|------|-------|
| id | bigint (PK) | Auto-increment |
| name | string | |
| email | string | Unique |
| email_verified_at | timestamp | Nullable |
| password | string | |
| remember_token | string | |
| two_factor_secret | text | Nullable |
| two_factor_recovery_codes | text | Nullable |
| two_factor_confirmed_at | timestamp | Nullable |
| created_at / updated_at | timestamps | |

### 2. `password_reset_tokens`

| Column | Type | Notes |
|--------|------|-------|
| email | string (PK) | |
| token | string | |
| created_at | timestamp | Nullable |

### 3. `sessions`

| Column | Type | Notes |
|--------|------|-------|
| id | string (PK) | |
| user_id | bigint (FK → users) | Nullable, indexed |
| ip_address | string(45) | Nullable |
| user_agent | text | Nullable |
| payload | longText | |
| last_activity | integer | Indexed |

### 4. `cache`

| Column | Type | Notes |
|--------|------|-------|
| key | string (PK) | |
| value | mediumText | |
| expiration | bigint | Indexed |

### 5. `cache_locks`

| Column | Type | Notes |
|--------|------|-------|
| key | string (PK) | |
| owner | string | |
| expiration | bigint | Indexed |

### 6. `jobs`

| Column | Type | Notes |
|--------|------|-------|
| id | bigint (PK) | Auto-increment |
| queue | string | Indexed |
| payload | longText | |
| attempts | smallint (unsigned) | |
| reserved_at | integer (unsigned) | Nullable |
| available_at | integer (unsigned) | |
| created_at | integer (unsigned) | |

### 7. `job_batches`

| Column | Type | Notes |
|--------|------|-------|
| id | string (PK) | |
| name | string | |
| total_jobs | integer | |
| pending_jobs | integer | |
| failed_jobs | integer | |
| failed_job_ids | longText | |
| options | mediumText | Nullable |
| cancelled_at | integer | Nullable |
| created_at | integer | |
| finished_at | integer | Nullable |

### 8. `failed_jobs`

| Column | Type | Notes |
|--------|------|-------|
| id | bigint (PK) | Auto-increment |
| uuid | string | Unique |
| connection | string | |
| queue | string | |
| payload | longText | |
| exception | longText | |
| failed_at | timestamp | |

- Composite index on `(connection, queue, failed_at)`

### 9. `passkeys`

| Column | Type | Notes |
|--------|------|-------|
| id | bigint (PK) | Auto-increment |
| user_id | bigint (FK → users) | Cascades delete, indexed |
| name | string | |
| credential_id | string | Unique |
| credential | json | |
| last_used_at | timestamp | Nullable |
| created_at / updated_at | timestamps | |

### 10. `agent_conversations`

| Column | Type | Notes |
|--------|------|-------|
| id | string(36) (PK) | UUID |
| user_id | bigint (FK) | Nullable |
| title | string | |
| created_at / updated_at | timestamps | |

- Composite index on `(user_id, updated_at)`

### 11. `agent_conversation_messages`

| Column | Type | Notes |
|--------|------|-------|
| id | string(36) (PK) | UUID |
| conversation_id | string(36) | Indexed |
| user_id | bigint (FK) | Nullable, indexed |
| agent | string | |
| role | string(25) | |
| content | text | |
| attachments | text | |
| tool_calls | text | |
| tool_results | text | |
| usage | text | |
| meta | text | |
| created_at / updated_at | timestamps | |

- Composite index on `(conversation_id, user_id, updated_at)`

### 12. `events`

| Column | Type | Notes |
|--------|------|-------|
| id | bigint (PK) | Auto-increment |
| name | string | |
| description | text | |
| metadata | jsonb | Nullable |
| created_at / updated_at | timestamps | |

### 13. `booths`

| Column | Type | Notes |
|--------|------|-------|
| id | bigint (PK) | Auto-increment |
| event_id | bigint (FK → events) | Cascades delete |
| name | string | |
| description | text | |
| metadata | jsonb | Nullable |
| created_at / updated_at | timestamps | |

### 14. `knowledge_chunks` ⭐ (Vector Table)

| Column | Type | Notes |
|--------|------|-------|
| id | bigint (PK) | Auto-increment |
| event_id | bigint (FK → events) | Nullable, cascades delete |
| booth_id | bigint (FK → booths) | Nullable, cascades delete |
| chunk_text | text | The original text chunk |
| embedding | **vector(1536)** | 1536-dimensional vector embedding |
| chunk_order | integer | Default 0 |
| metadata | jsonb | Nullable |
| created_at / updated_at | timestamps | |

- **HNSW Index:** `idx_knowledge_chunks_embedding` using `hnsw (embedding vector_cosine_ops)` for fast approximate nearest-neighbor search

### 15. `visitors`

| Column | Type | Notes |
|--------|------|-------|
| id | bigint (PK) | Auto-increment |
| session_token | string | Unique |
| name | string | Nullable |
| email | string | Nullable |
| company | string | Nullable |
| post_code | string | Nullable |
| address | string | Nullable |
| organization | string | Nullable |
| occupation | string | Nullable |
| age_range | string | Nullable |
| opt_out | boolean | Default false |
| job_title | string | Nullable |
| country | string | Nullable |
| phone | string | Nullable |
| metadata | jsonb | Nullable |
| created_at / updated_at | timestamps | |

### 16. `visitor_sessions`

| Column | Type | Notes |
|--------|------|-------|
| id | bigint (PK) | Auto-increment |
| visitor_id | bigint (FK → visitors) | Cascades delete |
| event_id | bigint (FK → events) | Cascades delete |
| booth_id | bigint (FK → booths) | Nullable, cascades delete |
| status | string | Default `'active'` (active/completed) |
| started_at | timestamp | Default current |
| completed_at | timestamp | Nullable |
| created_at / updated_at | timestamps | |

### 17. `session_questions`

| Column | Type | Notes |
|--------|------|-------|
| id | bigint (PK) | Auto-increment |
| session_id | bigint (FK → visitor_sessions) | Cascades delete |
| question_text | text | |
| question_order | integer | |
| asked_at | timestamp | Default current |
| created_at / updated_at | timestamps | |

### 18. `session_answers`

| Column | Type | Notes |
|--------|------|-------|
| id | bigint (PK) | Auto-increment |
| question_id | bigint (FK → session_questions) | Cascades delete |
| session_id | bigint (FK → visitor_sessions) | Cascades delete |
| visitor_id | bigint (FK → visitors) | Cascades delete |
| answer_text | text | |
| metadata | jsonb | Nullable |
| created_at / updated_at | timestamps | |

### 19. `summaries`

| Column | Type | Notes |
|--------|------|-------|
| id | bigint (PK) | Auto-increment |
| event_id | bigint (FK → events) | Nullable, cascades delete |
| booth_id | bigint (FK → booths) | Nullable, cascades delete |
| visitor_id | bigint (FK → visitors) | Nullable, cascades delete |
| content | jsonb | |
| generated_at | timestamp | Default current |
| created_at / updated_at | timestamps | |

### 20. `registrations`

| Column | Type | Notes |
|--------|------|-------|
| id | bigint (PK) | Auto-increment |
| event_id | bigint (FK → events) | Cascades delete |
| visitor_id | bigint (FK → visitors) | Nullable, cascades delete |
| name | string | |
| email | string | |
| phone | string | Nullable |
| company | string | Nullable |
| post_code | string | Nullable |
| address | string | Nullable |
| organization | string | Nullable |
| occupation | string | Nullable |
| age_range | string | Nullable |
| opt_out | boolean | Default false |
| job_title | string | Nullable |
| country | string | Nullable |
| source | string | Nullable |
| notes | text | Nullable |
| document_path | string | Nullable |
| session_token | string | Unique |
| status | string | Default `'pending'` |
| metadata | jsonb | Nullable |
| created_at / updated_at | timestamps | |

---

## Entity Relationship Diagram (Simplified)

```
users ──────────┐
  │              │
  ├── passkeys   ├── agent_conversations ── agent_conversation_messages
  │
events ─────────┤
  │              │
  ├── booths     ├── knowledge_chunks ⭐ (vector embeddings)
  │              │
  ├── visitor_sessions ── session_questions ── session_answers
  │     │
  │     └── visitors
  │              │
  ├── registrations
  │
  └── summaries
```

---

## How Vector Data is Stored

### Architecture Overview

This project implements a **RAG (Retrieval-Augmented Generation)** pipeline using PostgreSQL's **pgvector** extension to store and query vector embeddings. The system enables AI-powered semantic search over event and booth knowledge bases.

### Technology Stack

| Component | Technology |
|-----------|-----------|
| Vector Extension | **pgvector** (PostgreSQL extension) |
| PHP Library | `pgvector/pgvector` ^0.2.2 |
| Embedding Model | `openai/text-embedding-3-small` (via OpenRouter) |
| Embedding Dimensions | **1536** |
| Similarity Metric | **Cosine similarity** |
| Index Type | **HNSW** (Hierarchical Navigable Small World) |
| Laravel AI SDK | `laravel/ai` (Embeddings API) |

### Step-by-Step Flow

#### 1. Indexing (Writing Vectors) — `IndexKnowledge` Action

```
Raw Content (text)
    │
    ▼
┌─────────────────────────┐
│  Text Chunking           │  Split into ~500-word chunks
│  (500 words, 50 overlap) │  with 50-word overlap for context
└───────────┬─────────────┘
            │
            ▼
┌─────────────────────────┐
│  Embedding Generation    │  OpenRouter → openai/text-embedding-3-small
│  (1536-dim vectors)      │  Each chunk → float[1536]
└───────────┬─────────────┘
            │
            ▼
┌─────────────────────────┐
│  Store in PostgreSQL     │  knowledge_chunks table
│  (pgvector column)       │  embedding vector(1536)
└─────────────────────────┘
```

**Key details:**
- The `IndexKnowledge` action takes raw text content (e.g., event descriptions, booth product info) and processes it
- Text is split into overlapping chunks of ~500 words with 50-word overlap to preserve context across chunk boundaries
- Each chunk is sent to the **OpenAI text-embedding-3-small** model via OpenRouter to generate a 1536-dimensional embedding vector
- Results are cached (`->cache()`) to avoid re-embedding identical text
- Each chunk is stored as a `KnowledgeChunk` record with:
  - `chunk_text`: the original text
  - `embedding`: the 1536-dimensional float vector (cast via `Pgvector\Laravel\Vector`)
  - `event_id` / `booth_id`: scoping the chunk to an event or booth
  - `metadata`: JSON source information

#### 2. Searching (Reading Vectors) — `EventKnowledgeSearch` AI Tool

```
User Query (text)
    │
    ▼
┌─────────────────────────┐
│  Embed Query             │  Same model: openai/text-embedding-3-small
│  → float[1536]           │
└───────────┬─────────────┘
            │
            ▼
┌─────────────────────────┐
│  Vector Similarity Search │  whereVectorSimilarTo()
│  Cosine distance < 0.1   │  HNSW index scan
│  Scoped to event/booth   │  LIMIT 5 results
└───────────┬─────────────┘
            │
            ▼
┌─────────────────────────┐
│  Return matching chunks   │  Top 5 most similar chunks
│  as context for AI agent  │  Fed into LLM prompt
└─────────────────────────┘
```

**Key details:**
- When the AI agent needs event knowledge, the `EventKnowledgeSearch` tool is invoked
- The user's query is embedded using the **same model** (ensuring vector space alignment)
- A **cosine similarity search** is performed using `whereVectorSimilarTo()` (from the `HasNeighbors` trait)
- A minimum similarity threshold of `0.1` filters out irrelevant results
- Results are scoped to the current event (and optionally booth + event-wide chunks)
- The top 5 matching chunks are returned as context for the AI agent to use in generating responses

#### 3. Database-Level Details

```sql
-- Column definition
embedding vector(1536)   -- pgvector native type

-- HNSW index for fast approximate nearest-neighbor search
CREATE INDEX idx_knowledge_chunks_embedding
    ON knowledge_chunks
    USING hnsw (embedding vector_cosine_ops);
```

- **pgvector** adds a native `vector` data type to PostgreSQL that stores arrays of floats efficiently
- The **HNSW index** provides O(log n) approximate nearest-neighbor lookups instead of brute-force O(n) scans
- `vector_cosine_ops` configures the index to use cosine distance as the similarity metric
- The `Pgvector\Laravel\Vector` Eloquent cast handles serialization/deserialization between PHP arrays and the pgvector column type
- The `Pgvector\Laravel\HasNeighbors` trait provides the `whereVectorSimilarTo()` query builder method

### Summary

| Aspect | Detail |
|--------|--------|
| **Storage** | PostgreSQL `vector(1536)` column via pgvector extension |
| **Indexing** | HNSW index with cosine distance for fast ANN search |
| **Embedding model** | `openai/text-embedding-3-small` (1536 dims) via OpenRouter |
| **Chunking** | 500-word chunks with 50-word overlap |
| **Search** | Cosine similarity with 0.1 minimum threshold, top-5 results |
| **Purpose** | RAG pipeline — provides context to AI agents answering visitor questions about events/booths |
