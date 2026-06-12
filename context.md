# Project Context

## Overview
This is a **Laravel 13 AI-powered Event Survey & Registration System** that uses conversational AI to conduct intelligent visitor surveys, assist with event registration, and generate analytics summaries. The system leverages Laravel's official AI SDK (`laravel/ai`) with OpenAI models via OpenRouter, and uses vector embeddings (pgvector) for knowledge retrieval.

## Technology Stack

### Backend
- **Framework**: Laravel 13.8 (PHP 8.3+)
- **AI/ML**: 
  - Laravel AI SDK v0.7.2 (`laravel/ai`)
  - OpenAI GPT-4o via OpenRouter
  - OpenAI text-embedding-3-small for vector embeddings
  - PostgreSQL with pgvector extension for similarity search
- **Authentication**: Laravel Fortify v1.37.2 (with 2FA and passkey support)
- **Testing**: Pest v4.7
- **Code Quality**: Laravel Pint v1.27
- **Development Tools**: Laravel Boost v2.2, Laravel Pail v1.2.5

### Frontend
- **Views**: Blade templates (server-side rendering)
- **No JavaScript framework** - Traditional Blade views with minimal JS

## Core Features

### 1. AI-Powered Survey System
Conducts intelligent, adaptive conversations with event visitors to gather insights.

**Key Components:**
- `SurveyAgent` - Main conversational AI agent that conducts surveys
- `StartSurveySession` - Action to initialize survey sessions
- `ProcessAnswer` - Action to handle visitor responses and continue conversations
- `SurveyController` (API) - Endpoints for survey interactions
- `SurveyPageController` (Web) - Blade views for survey UI

**Survey Flow:**
1. Visitor arrives at event/booth → Survey starts
2. AI greets and asks tailored questions based on visitor profile
3. 3-4 exchanges with adaptive follow-ups
4. Uses `EventKnowledgeSearch` tool to answer visitor questions
5. Marks session complete after 4 questions
6. Generates summary analytics

**Targeting Strategies:**
The SurveyAgent dynamically adapts questions based on visitor industry:
- Exporters/Importers
- Wholesalers/Retailers
- Hospitality/Food Service
- Food/Beverage Manufacturers
- Producers/Agricultural Cooperatives
- Logistics/Warehousing
- Government/Diplomatic
- And more...

### 2. Event Registration System
AI assistant helps visitors complete registration forms.

**Key Components:**
- `RegistrationAssistantAgent` - AI helper for registration questions
- `RegistrationController` (API) - Registration endpoints
- `Registration` model - Stores registration data
- `Visitor` model - Created from registration for survey flow

**Registration Fields:**
- Required: name, email, password
- Optional: company, industry, department, post, post_code, address, phone, opt_out, reception_category, responsible_organization

**Flow:**
1. Visitor opens registration form → AI assistant greets
2. Visitor can ask questions about form fields or event
3. AI answers using knowledge base or explains validation rules
4. Visitor submits form → Creates Visitor record for future surveys

### 3. Knowledge Base & RAG System
Stores event/booth information as vector embeddings for intelligent retrieval.

**Key Components:**
- `KnowledgeChunk` model - Stores text chunks with vector embeddings
- `IndexKnowledge` action - Chunks text, generates embeddings, stores in DB
- `EventKnowledgeSearch` tool - RAG tool for AI agents to search knowledge
- Vector similarity search using pgvector

**How It Works:**
1. Admin uploads event/booth knowledge (text content)
2. Text is chunked (500 words, 50-word overlap)
3. Each chunk is embedded using text-embedding-3-small (1536 dimensions)
4. Stored in PostgreSQL with pgvector
5. AI agents search using vector similarity (minimum 0.1 similarity)
6. Results cached for 1 hour with version-based invalidation

### 4. Survey Analytics & Summarization
Generates AI-powered summaries of survey data.

**Key Components:**
- `SummarizationAgent` - AI analyst that processes survey data
- `GenerateSummary` action - Builds context and generates JSON summaries
- `SummaryController` (API) - Endpoints for summary generation
- `Summary` model - Stores generated summaries

**Summary Output (JSON):**
```json
{
  "total_visitors": 123,
  "key_themes": ["theme1", "theme2"],
  "demographics": {
    "roles": ["role1", "role2"],
    "companies": ["company1", "company2"]
  },
  "sentiment": "positive|neutral|negative",
  "actionable_insights": ["insight1", "insight2"],
  "top_interests": ["interest1", "interest2"],
  "recommendations": "detailed recommendations"
}
```

## Data Models

### Core Entities

**Event**
- `id`, `name`, `description`, `metadata` (JSON)
- Has many: booths, sessions, knowledgeChunks, summaries

**Booth**
- `id`, `event_id`, `name`, `description`, `metadata` (JSON)
- Belongs to: event
- Has many: sessions, knowledgeChunks, summaries

**Visitor**
- `id`, `session_token`, `name`, `email`, `phone`, `company`, `industry`, `department`, `post`, `post_code`, `address`, `opt_out`, `reception_category`, `responsible_organization`, `metadata` (JSON)
- Has many: sessions, answers, registrations, summaries

**VisitorSession**
- `id`, `visitor_id`, `event_id`, `booth_id`, `status` (active|completed), `started_at`, `completed_at`
- Belongs to: visitor, event, booth
- Has many: questions, answers

**SessionQuestion**
- `id`, `session_id`, `question_text`, `question_order`, `asked_at`
- Belongs to: session
- Has one: answer

**SessionAnswer**
- `id`, `question_id`, `session_id`, `visitor_id`, `answer_text`, `metadata` (JSON)
- Belongs to: question, session, visitor

**Registration**
- `id`, `event_id`, `visitor_id`, `name`, `email`, `password`, `phone`, `company`, `industry`, `department`, `post`, `post_code`, `address`, `opt_out`, `reception_category`, `responsible_organization`, `document_path`, `session_token`, `status` (pending|submitted), `metadata` (JSON)
- Belongs to: event, visitor

**KnowledgeChunk**
- `id`, `event_id`, `booth_id`, `chunk_text`, `embedding` (vector), `chunk_order`, `metadata` (JSON)
- Belongs to: event, booth
- Uses: pgvector for vector similarity search

**Summary**
- `id`, `event_id`, `booth_id`, `visitor_id`, `content` (JSON), `generated_at`
- Belongs to: event, booth, visitor

**User** (Admin)
- Standard Laravel user with Fortify authentication
- Supports: 2FA, passkeys, email verification

## API Endpoints

### Event Management (Admin)
```
GET    /api/events                          - List all events
POST   /api/events                          - Create event
GET    /api/events/{event}                  - Show event details
PUT    /api/events/{event}                  - Update event
DELETE /api/events/{event}                  - Delete event
POST   /api/events/{event}/knowledge        - Upload knowledge base
POST   /api/events/{event}/booths           - Create booth for event
```

### Booth Management (Admin)
```
GET    /api/booths/{booth}                  - Show booth details
DELETE /api/booths/{booth}                  - Delete booth
```

### Survey (Public/Visitor)
```
POST   /api/survey/start                    - Start new survey session
GET    /api/survey/{session}                - Get session details
POST   /api/survey/{session}/answer         - Submit answer to question
POST   /api/survey/{session}/complete       - Mark session as complete
```

### Registration (Public/Visitor)
```
POST   /api/registration/start              - Start registration chat
POST   /api/registration/ask                - Ask registration assistant
POST   /api/registration/submit             - Submit registration form
```

### Summarization (Admin)
```
GET    /api/events/{event}/summary          - Generate event summary
GET    /api/booths/{booth}/summary          - Generate booth summary
GET    /api/visitors/{visitor}/summary      - Generate visitor summary
POST   /api/events/{event}/summary/regenerate - Regenerate event summary
```

## Web Routes

### Public
```
GET    /                                    - Welcome page
GET    /s/{event}                           - Survey chat page
GET    /s/{event}/booth/{boothId}           - Survey chat with booth
GET    /s/{event}/complete                  - Survey complete page
GET    /s/{event}/register                  - Registration page
GET    /s/{event}/register/complete         - Registration complete page
```

### Admin (Authenticated)
```
GET    /dashboard                           - Redirects to /admin/events
GET    /admin/events                        - Events index
GET    /admin/events/create                 - Create event form
GET    /admin/events/{event}                - Event details
GET    /admin/events/{event}/summary        - Event summary view
```

### Settings (Authenticated)
```
GET    /settings/profile                    - Profile settings
PATCH  /settings/profile                    - Update profile
DELETE /settings/profile                    - Delete account
GET    /settings/security                   - Security settings
PUT    /settings/password                   - Update password
```

### Authentication (Fortify)
Auto-registered routes for login, register, password reset, email verification, 2FA, etc.

## AI Agents

### SurveyAgent
- **Model**: openai/gpt-4o
- **Temperature**: 0.7
- **Max Steps**: 12
- **Max Tokens**: 1024
- **Tools**: EventKnowledgeSearch
- **Purpose**: Conduct adaptive survey conversations with visitors
- **Key Features**:
  - Remembers conversation context
  - Adapts questions based on visitor profile and industry
  - Answers visitor questions using knowledge base
  - Prevents repeated greetings
  - 3-4 exchange conversations

### RegistrationAssistantAgent
- **Model**: openai/gpt-4o
- **Temperature**: 0.7
- **Max Steps**: 8
- **Max Tokens**: 1024
- **Tools**: EventKnowledgeSearch
- **Purpose**: Help visitors with registration form questions
- **Key Features**:
  - Explains form field validation rules
  - Answers event-related questions
  - Friendly, concise responses (2-4 sentences)

### SummarizationAgent
- **Model**: openai/gpt-4o
- **Temperature**: 0.3
- **Purpose**: Analyze survey data and generate JSON summaries
- **Key Features**:
  - Returns structured JSON with themes, demographics, sentiment, insights
  - Low temperature for consistent output

## AI Tools

### EventKnowledgeSearch
- **Purpose**: RAG (Retrieval Augmented Generation) tool for searching event knowledge
- **How It Works**:
  1. Takes search query from AI agent
  2. Generates embedding for query
  3. Searches knowledge_chunks using vector similarity
  4. Returns top 5 matching chunks
  5. Caches results for 1 hour
- **Caching**:
  - Cache TTL: 1 hour
  - Version-based invalidation when knowledge is updated
  - Query normalization for better cache hits

## Configuration

### AI Configuration (config/ai.php)
- Default provider: openrouter
- Default for embeddings: openrouter
- Embedding cache enabled
- Supports multiple providers: Anthropic, Azure, Bedrock, Cohere, DeepSeek, ElevenLabs, Gemini, Groq, Jina, Mistral, Ollama, OpenAI, OpenRouter, VoyageAI, xAI

### Environment Variables
Key variables needed in `.env`:
```
OPENROUTER_API_KEY=your_key_here
DB_CONNECTION=pgsql (for pgvector support)
CACHE_STORE=database
```

## Directory Structure

```
app/
├── Actions/                    # Business logic actions
│   ├── Fortify/               # Auth actions
│   ├── GenerateSummary.php
│   ├── IndexKnowledge.php
│   ├── ProcessAnswer.php
│   └── StartSurveySession.php
├── Ai/
│   ├── Agents/                # AI agent classes
│   │   ├── RegistrationAssistantAgent.php
│   │   ├── SummarizationAgent.php
│   │   └── SurveyAgent.php
│   └── Tools/                 # AI tools
│       └── EventKnowledgeSearch.php
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   │   └── EventPageController.php
│   │   ├── Api/               # API endpoints
│   │   │   ├── BoothController.php
│   │   │   ├── EventController.php
│   │   │   ├── RegistrationController.php
│   │   │   ├── SummaryController.php
│   │   │   └── SurveyController.php
│   │   ├── Settings/          # User settings
│   │   └── SurveyPageController.php
│   └── Requests/              # Form request validation
├── Models/                    # Eloquent models
└── Providers/                 # Service providers

database/
├── factories/                 # Model factories
├── migrations/                # Database migrations
└── seeders/                   # Database seeders

resources/
└── views/                     # Blade templates
    ├── admin/events/          # Admin event management
    ├── auth/                  # Authentication views
    ├── layouts/               # Layout templates
    ├── settings/              # User settings views
    └── survey/                # Public survey views

routes/
├── api.php                    # API routes
├── web.php                    # Web routes
└── settings.php               # Settings routes
```

## Database Schema

### Key Tables
- `users` - Admin users
- `events` - Event information
- `booths` - Booth information (belongs to event)
- `visitors` - Visitor profiles
- `visitor_sessions` - Survey sessions
- `session_questions` - Questions asked during sessions
- `session_answers` - Answers provided by visitors
- `registrations` - Event registrations
- `knowledge_chunks` - Knowledge base with vector embeddings
- `summaries` - AI-generated summaries

### Vector Embeddings
- Uses PostgreSQL pgvector extension
- Embedding dimension: 1536 (text-embedding-3-small)
- Similarity search with minimum threshold: 0.1

## Testing

- **Framework**: Pest v4.7
- **Run tests**: `php artisan test --compact`
- **Filter tests**: `php artisan test --compact --filter=testName`
- **Factories**: All models have factories for testing
- **Database**: Use RefreshDatabase trait in tests

## Development Commands

```bash
# Setup
composer setup

# Development server
composer run dev

# Run tests
composer test

# Format code
vendor/bin/pint --dirty --format agent

# Run migrations
php artisan migrate

# Create new files
php artisan make:model ModelName -mfs  # Model + migration + factory + seeder
php artisan make:test TestName --pest
php artisan make:controller ControllerName
```

## Key Design Decisions

1. **Conversational AI over Forms**: Uses AI agents for natural conversations instead of static forms
2. **RAG for Knowledge**: Vector embeddings enable intelligent knowledge retrieval
3. **Industry-Based Targeting**: Survey questions adapt based on visitor industry/profile
4. **Session-Based Tracking**: Visitor sessions track conversation state
5. **Blade Views**: Server-side rendering instead of SPA (despite AGENTS.md mentioning Inertia)
6. **API-First**: RESTful API for frontend flexibility
7. **Caching**: Aggressive caching for knowledge search (1 hour TTL)
8. **Vector Search**: Uses pgvector for semantic similarity over keyword matching

## Common Workflows

### Adding New Event Knowledge
1. Call `POST /api/events/{event}/knowledge` with text content
2. `IndexKnowledge` action chunks text and generates embeddings
3. Knowledge becomes searchable via `EventKnowledgeSearch` tool
4. Cache is invalidated automatically

### Conducting a Survey
1. Visitor visits `/s/{event}` or `/s/{event}/booth/{boothId}`
2. Frontend calls `POST /api/survey/start` with event_id, booth_id, visitor_id
3. `StartSurveySession` creates session and generates first question
4. For each answer, frontend calls `POST /api/survey/{session}/answer`
5. `ProcessAnswer` generates follow-up questions
6. After 4 exchanges, session is marked complete
7. Admin can generate summary via `GET /api/events/{event}/summary`

### Processing Registration
1. Visitor visits `/s/{event}/register`
2. Frontend calls `POST /api/registration/start` with event_id
3. AI assistant greets and offers help
4. Visitor can ask questions via `POST /api/registration/ask`
5. Visitor submits form via `POST /api/registration/submit`
6. Creates Visitor record for future surveys
7. Visitor can proceed to survey with their profile

## Notes

- **No Inertia.js**: Despite AGENTS.md mentioning Inertia React, the project uses Blade views
- **No Frontend Build**: No Vite/Webpack setup for JavaScript bundling
- **API-Driven**: Frontend likely uses fetch/axios to call API endpoints
- **OpenRouter**: All AI calls go through OpenRouter (supports multiple models)
- **PostgreSQL Required**: pgvector extension needed for vector embeddings
- **Conversation Memory**: AI agents use `RemembersConversations` trait for context
