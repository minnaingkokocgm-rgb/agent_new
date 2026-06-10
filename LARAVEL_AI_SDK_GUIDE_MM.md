# Laravel AI SDK - မြန်မာဘာသာဖြင့် လမ်းညွှန်

## မိတ်ဆက်

Laravel AI SDK (`laravel/ai`) သည် Laravel framework အတွက် official first-party AI package ဖြစ်ပါသည်။ ၎င်းသည် AI providers များစွာ (OpenAI, Anthropic, Gemini, Azure, Groq, xAI, DeepSeek, Mistral, Ollama, ElevenLabs, Cohere, Jina, VoyageAI) နှင့် unified API တစ်ခုတည်းဖြင့် အလုပ်လုပ်နိုင်သော system တစ်ခုဖြစ်ပါသည်။

---

## အဓိက Features များ

| Feature | ရှင်းလင်းချက် |
|---------|----------------|
| **Agents** | AI chatbots နှင့် conversational agents များ ဖန်တီးခြင်း |
| **Images** | AI ဖြင့် ပုံများ generate လုပ်ခြင်း |
| **Audio (TTS)** | Text-to-Speech (စာသားမှ အသံသို့) |
| **Transcription (STT)** | Speech-to-Text (အသံမှ စာသားသို့) |
| **Embeddings** | Vector embeddings များ ဖန်တီးခြင်း |
| **Reranking** | Search results များကို rank လုပ်ခြင်း |
| **Vector Stores** | Vector databases များ စီမံခန့်ခွဲခြင်း |
| **Files** | AI providers တွင် files များ သိမ်းဆည်းခြင်း |
| **Tools** | AI agents အတွက် custom tools များ ဖန်တီးခြင်း |

---

## Installation နှင့် Configuration

### Installation

```bash
composer require laravel/ai
```

### Configuration

`config/ai.php` ဖိုင်တွင် AI providers များကို configure လုပ်နိုင်ပါသည်။

```php
return [
    'default' => 'openrouter',
    'default_for_images' => 'openrouter',
    'default_for_audio' => 'openrouter',
    'default_for_transcription' => 'openrouter',
    'default_for_embeddings' => 'openrouter',
    
    'caching' => [
        'embeddings' => [
            'cache' => env('AI_CACHE_EMBEDDINGS', true),
            'store' => env('CACHE_STORE', 'database'),
        ],
    ],
    
    'providers' => [
        'openrouter' => [
            'driver' => 'openrouter',
            'key' => env('OPENROUTER_API_KEY'),
        ],
        'anthropic' => [
            'driver' => 'anthropic',
            'key' => env('ANTHROPIC_API_KEY'),
        ],
        // ... other providers
    ],
];
```

---

## ၁။ Agents (AI Chatbots)

Agent သည် Laravel AI SDK ၏ အဓိက အစိတ်အပိုင်းဖြစ်ပါသည်။ Chatbots၊ conversational AI များ ဖန်တီးရန် အသုံးပြုပါသည်။

### Basic Agent ဖန်တီးခြင်း

```php
<?php

namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;

class SalesCoach implements Agent
{
    use Promptable;

    public function instructions(): string
    {
        return 'You are a sales coach. Help users improve their sales skills.';
    }
}
```

### Agent ကို အသုံးပြုခြင်း

```php
// Simple prompt
$response = (new SalesCoach)->prompt('How do I close a deal?');
echo $response->text;

// Provider နှင့် model ကို specify လုပ်ခြင်း
use Laravel\Ai\Enums\Lab;

$response = (new SalesCoach)->prompt(
    'Analyze this transcript...',
    provider: Lab::Anthropic,
    model: 'claude-haiku-4-5-20251001',
    timeout: 120,
);
```

### PHP Attributes ဖြင့် Configuration

Agent class တွင် PHP attributes များ သုံး၍ configuration လုပ်နိုင်ပါသည်။

```php
use Laravel\Ai\Attributes\{Provider, Model, MaxSteps, MaxTokens, Temperature, Timeout};
use Laravel\Ai\Enums\Lab;

#[Provider(Lab::Anthropic)]
#[Model('claude-haiku-4-5-20251001')]
#[MaxSteps(10)]
#[MaxTokens(4096)]
#[Temperature(0.7)]
#[Timeout(120)]
class MyAgent implements Agent
{
    use Promptable;
    
    public function instructions(): string
    {
        return 'Your instructions here...';
    }
}
```

**Attributes များ:**
- `#[Provider]` - AI provider (OpenAI, Anthropic, etc.)
- `#[Model]` - Model name (gpt-4o, claude-3, etc.)
- `#[MaxSteps]` - Agent က tool မည်မျှခေါ်နိုင်သည်
- `#[MaxTokens]` - Response အများဆုံး token အရေအတွက်
- `#[Temperature]` - Creativity level (0.0 - 1.0)
- `#[Timeout]` - Timeout (seconds)

---

## ၂။ Conversation Context (စကားဝိုင်း မှတ်ဉာဏ်)

AI ကို ယခင် စကားဝိုင်းများကို မှတ်မိစေရန် ပြုလုပ်ခြင်း။

### Manual Method (Conversational Interface)

```php
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;

class SalesCoach implements Agent, Conversational
{
    use Promptable;

    public function __construct(public User $user) {}

    public function instructions(): string 
    { 
        return 'You are a sales coach.'; 
    }

    public function messages(): iterable
    {
        return History::where('user_id', $this->user->id)
            ->latest()
            ->limit(50)
            ->get()
            ->reverse()
            ->map(fn ($m) => new Message($m->role, $m->content))
            ->all();
    }
}
```

### Automatic Method (RemembersConversations Trait)

အလွယ်ဆုံး နည်းလမ်း - trait တစ်ခုတည်း ထည့်ရုံပါပဲ။

```php
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Promptable;

class SalesCoach implements Agent, Conversational
{
    use Promptable, RemembersConversations;

    public function instructions(): string 
    { 
        return 'You are a sales coach.'; 
    }
}

// စကားဝိုင်း အသစ် စတင်ခြင်း
$response = (new SalesCoach)->forUser($user)->prompt('Hello!');
$conversationId = $response->conversationId;

// ရှိပြီးသား စကားဝိုင်းကို ဆက်လက်ပြုလုပ်ခြင်း
$response = (new SalesCoach)
    ->continue($conversationId, as: $user)
    ->prompt('Tell me more.');
```

---

## ၃။ Tools (AI အတွက် Custom Functions)

Tools များသည် AI agent ကို external data sources များ၊ functions များ ခေါ်နိုင်စေပါသည်။

### Tool Interface Implementation

```php
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class WeatherTool implements Tool
{
    protected string $description = 'Get current weather for a location';

    public function description(): Stringable|string
    {
        return $this->description;
    }

    public function handle(Request $request): Stringable|string
    {
        $location = $request->string('location')->value();
        
        // Weather API ကို ခေါ်ဆိုခြင်း
        $weather = WeatherAPI::get($location);
        
        return "Current weather in {$location}: {$weather}";
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'location' => $schema
                ->string()
                ->description('City name (e.g., Yangon, Mandalay)')
                ->required(),
        ];
    }
}
```

### Agent တွင် Tools ထည့်သွင်းခြင်း

```php
use Laravel\Ai\Contracts\HasTools;

class MyAgent implements Agent, HasTools
{
    use Promptable;

    public function instructions(): string
    {
        return 'You are a helpful assistant with weather information.';
    }

    public function tools(): iterable
    {
        return [
            new WeatherTool,
            new CalculatorTool,
            new DatabaseSearchTool,
        ];
    }
}
```

### Project မှ Real Example - EventKnowledgeSearch

ဤ project တွင် RAG (Retrieval-Augmented Generation) အတွက် tool တစ်ခု အသုံးပြုထားပါသည်။

```php
<?php

namespace App\Ai\Tools;

use App\Models\KnowledgeChunk;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Embeddings;
use Laravel\Ai\Tools\Request;

class EventKnowledgeSearch implements Tool
{
    private const MinimumSimilarity = 0.1;

    public function __construct(
        private Event $event,
        private ?Booth $booth = null,
    ) {}

    public function handle(Request $request): Stringable|string
    {
        $query = $request->string('query')->value();

        // Query ကို embedding အဖြစ် ပြောင်းလဲခြင်း
        $embeddingResponse = Embeddings::for([$query])
            ->dimensions(1536)
            ->generate(provider: 'openrouter', model: 'openai/text-embedding-3-small');

        $queryEmbedding = $embeddingResponse->embeddings[0];

        // Vector similarity search ပြုလုပ်ခြင်း
        $results = KnowledgeChunk::query()
            ->where('event_id', $this->event->id)
            ->when($this->booth, fn ($q) => $q->where(function ($q) {
                $q->where('booth_id', $this->booth->id)
                    ->orWhereNull('booth_id');
            }))
            ->whereVectorSimilarTo('embedding', $queryEmbedding, self::MinimumSimilarity)
            ->limit(5)
            ->get();

        if ($results->isEmpty()) {
            return 'No relevant information found.';
        }

        return "Relevant information:\n\n"
            . $results->pluck('chunk_text')->implode("\n\n---\n\n");
    }
}
```

### Provider Built-in Tools

Laravel AI SDK တွင် built-in tools များလည်း ပါဝင်ပါသည်။

```php
use Laravel\Ai\Providers\Tools\{WebSearch, WebFetch, FileSearch};

public function tools(): iterable
{
    return [
        (new WebSearch)->max(5)->allow(['laravel.com']),
        new WebFetch,
        new FileSearch(stores: ['store_id']),
    ];
}
```

---

## ၄။ Structured Output (JSON Response)

AI ကို structured JSON format ဖြင့် response ပေးစေခြင်း။

```php
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;

class Reviewer implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): string 
    { 
        return 'Review and score content.'; 
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'feedback' => $schema->string()->required(),
            'score' => $schema->integer()->min(1)->max(10)->required(),
            'tags' => $schema->array()->items(
                $schema->string()
            )->required(),
        ];
    }
}

// အသုံးပြုပုံ
$response = (new Reviewer)->prompt('Review this article...');
echo $response['score'];      // 8
echo $response['feedback'];   // "Good article..."
print_r($response['tags']);   // ['technology', 'programming']
```

---

## ၅။ Streaming (Real-time Response)

Response ကို real-time stream လုပ်ခြင်း (ChatGPT ကဲ့သို့)။

```php
// Controller တွင်
public function chat(Request $request)
{
    return (new SalesCoach)->stream('Analyze this transcript...');
}
```

Frontend တွင် Server-Sent Events (SSE) ဖြင့် receive လုပ်နိုင်ပါသည်။

---

## ၆။ Queueing (Background Processing)

Agent ကို background တွင် run စေခြင်း။

```php
(new SalesCoach)
    ->queue('Analyze this long document...')
    ->then(function ($response) {
        // Response ပြီးသွားသောအခါ ဤနေရာတွင် process လုပ်ပါ
        Log::info('Analysis complete', ['text' => $response->text]);
    });
```

---

## ၇။ Images (ပုံများ Generate လုပ်ခြင်း)

AI ဖြင့် ပုံများ ဖန်တီးခြင်း။

```php
use Laravel\Ai\Image;

// ပုံ generate လုပ်ခြင်း
$image = Image::of('A sunset over mountains')
    ->landscape()
    ->quality('high')
    ->generate();

// Storage တွင် သိမ်းဆည်းခြင်း
$path = $image->store();
$path = $image->store('images', 'public');

// Custom filename
$path = $image->storeAs('images', 'sunset.jpg', 'public');
```

---

## ၈။ Audio (Text-to-Speech)

စာသားမှ အသံဖန်တီးခြင်း။

```php
use Laravel\Ai\Audio;

$audio = Audio::of('Hello from Myanmar.')
    ->female()
    ->instructions('Speak warmly and clearly')
    ->generate();

$path = $audio->store();
```

---

## ၉။ Transcription (Speech-to-Text)

အသံမှ စာသားသို့ ပြောင်းလဲခြင်း။

```php
use Laravel\Ai\Transcription;

// File path မှ transcription
$transcript = Transcription::fromPath('/path/to/audio.mp3')
    ->diarize()  // Speaker များကို ခွဲခြားခြင်း
    ->generate();

echo (string) $transcript;

// Storage မှ transcription
$transcript = Transcription::fromStorage('audio.mp3')
    ->generate();
```

---

## ၁၀။ Embeddings (Vector Embeddings)

Text ကို vector embeddings အဖြစ် ပြောင်းလဲခြင်း (RAG အတွက် အသုံးဝင်)။

### Basic Usage

```php
use Laravel\Ai\Embeddings;

// Multiple texts
$response = Embeddings::for(['Text one', 'Text two', 'Text three'])
    ->dimensions(1536)
    ->cache()  // Cache လုပ်ခြင်း (performance အတွက်)
    ->generate();

foreach ($response->embeddings as $embedding) {
    // $embedding သည် float array (1536 dimensions)
    echo count($embedding); // 1536
}
```

### Single Text (Stringable)

```php
use Illuminate\Support\Str;

$embedding = Str::of('Myanmar is a beautiful country.')
    ->toEmbeddings();
```

### Project မှ Real Example - IndexKnowledge Action

```php
<?php

namespace App\Actions;

use App\Models\KnowledgeChunk;
use Laravel\Ai\Embeddings;

class IndexKnowledge
{
    public function handle(Event $event, ?Booth $booth, string $content, string $sourceName): int
    {
        // Text ကို chunks များ ခွဲခြင်း
        $chunks = $this->chunkText($content, 500, 50);

        if (empty($chunks)) {
            return 0;
        }

        // Embeddings generate လုပ်ခြင်း
        $embeddingResponse = Embeddings::for($chunks)
            ->dimensions(1536)
            ->cache()
            ->generate(provider: 'openrouter', model: 'openai/text-embedding-3-small');

        // Database တွင် သိမ်းဆည်းခြင်း
        $inserted = 0;
        foreach ($chunks as $i => $chunkText) {
            KnowledgeChunk::create([
                'event_id' => $event->id,
                'booth_id' => $booth?->id,
                'chunk_text' => $chunkText,
                'embedding' => $embeddingResponse->embeddings[$i],
                'chunk_order' => $i,
                'metadata' => [
                    'source' => $sourceName,
                    'chunked_at' => now()->toIsoString(),
                ],
            ]);
            $inserted++;
        }

        return $inserted;
    }

    private function chunkText(string $text, int $wordsPerChunk, int $overlapWords): array
    {
        $words = preg_split('/\s+/', trim($text));
        $chunks = [];

        for ($i = 0; $i < count($words); $i += ($wordsPerChunk - $overlapWords)) {
            $chunk = array_slice($words, $i, $wordsPerChunk);
            if (count($chunk) < 10) {
                break;
            }
            $chunks[] = implode(' ', $chunk);
        }

        return $chunks;
    }
}
```

---

## ၁၁။ Reranking (Search Results ranking လုပ်ခြင်း)

Search results များကို relevance အလိုက် ranking လုပ်ခြင်း။

```php
use Laravel\Ai\Reranking;

$documents = [
    'Django is a Python web framework.',
    'Laravel is a PHP web framework.',
    'React is a JavaScript library.',
];

$response = Reranking::of($documents)
    ->limit(5)
    ->rerank('PHP frameworks');

// အဆင့်အမြင့်ဆုံး result
echo $response->first()->document; // "Laravel is a PHP web framework."
```

---

## ၁၂။ Files (AI Provider တွင် Files သိမ်းဆည်းခြင်း)

```php
use Laravel\Ai\Files\Document;

// File ကို provider တွင် upload လုပ်ခြင်း
$file = Document::fromPath('/path/to/document.pdf')->put();

// Storage မှ file
$file = Document::fromStorage('document.pdf')->put();
```

---

## ၁၃။ Vector Stores (Vector Database စီမံခန့်ခွဲခြင်း)

```php
use Laravel\Ai\Stores;
use Laravel\Ai\Files\Document;

// Vector store အသစ် ဖန်တီးခြင်း
$store = Stores::create('Knowledge Base');

// Store တွင် files ထည့်သွင်းခြင်း
$store->add($file->id);

// File ကို upload လုပ်ပြီး store တွင် တိုက်ရိုက်ထည့်ခြင်း
$store->add(Document::fromStorage('manual.pdf'));
```

---

## ၁၄။ Failover (Provider Fallback)

Provider တစ်ခု အလုပ်မလုပ်ပါက အခြား provider သို့ အလိုအလျောက် ပြောင်းလဲခြင်း။

```php
use Laravel\Ai\Enums\Lab;

// Provider array ကို pass လုပ်ပါ
$response = (new MyAgent)->prompt(
    'Hello', 
    provider: [Lab::OpenAI, Lab::Anthropic]
);
```

OpenAI အလုပ်မလုပ်ပါက Anthropic သို့ အလိုအလျောက် ပြောင်းသွားပါမည်။

---

## ၁၅။ Anonymous Agents (အမည်မဲ့ Agents)

Class ဖန်တီးစရာ မလိုဘဲ agent ကို တိုက်ရိုက် အသုံးပြုခြင်း။

```php
use function Laravel\Ai\agent;

$response = agent(instructions: 'You are a helpful assistant.')
    ->prompt('Hello, how are you?');

echo $response->text;
```

---

## ၁၆။ Testing (စမ်းသပ်ခြင်း)

Laravel AI SDK သည် testing အတွက် fake methods များ ပေးထားပါသည်။

### Agents Testing

```php
use App\Ai\Agents\SalesCoach;

// Fake response များ သတ်မှတ်ခြင်း
SalesCoach::fake(['Response 1', 'Response 2']);

// Assertions
SalesCoach::assertPrompted('specific query');
SalesCoach::assertNotPrompted('query that should not be called');
SalesCoach::assertNeverPrompted();

// Stray prompts ကို တားဆီးခြင်း
SalesCoach::fake()->preventStrayPrompts();
```

### Images Testing

```php
use Laravel\Ai\Image;

Image::fake();

// Test code run ပြီး...

Image::assertGenerated(fn ($prompt) => $prompt->contains('sunset'));
Image::assertNothingGenerated();
```

### Embeddings Testing

```php
use Laravel\Ai\Embeddings;

Embeddings::fake();

Embeddings::assertGenerated(fn ($prompt) => $prompt->contains('Laravel'));
```

---

## Provider Support Matrix

| Feature | Supported Providers |
|---------|-------------------|
| **Text Generation** | OpenAI, Anthropic, Gemini, Azure, Groq, xAI, DeepSeek, Mistral, Ollama |
| **Image Generation** | OpenAI, Gemini, xAI |
| **Text-to-Speech** | OpenAI, ElevenLabs |
| **Speech-to-Text** | OpenAI, ElevenLabs, Mistral |
| **Embeddings** | OpenAI, Gemini, Azure, Cohere, Mistral, Jina, VoyageAI |
| **Reranking** | Cohere, Jina |
| **File Storage** | OpenAI, Anthropic, Gemini |

---

## Common Pitfalls (သတိပြုရန် အချက်များ)

### ၁။ Namespace မှားယွင်းခြင်း

```php
// ✅ မှန်ကန်သော namespace
use Laravel\Ai\Image;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;

// ❌ မှားယွင်းသော namespace
use Illuminate\Ai\Image;
use Laravel\AI\Agent;
```

### ၂။ Provider Support မရှိသော Feature ကို ခေါ်ခြင်း

Provider တိုင်းသည် feature အားလုံးကို support မလုပ်ပါ။ Support matrix ကို စစ်ဆေးပါ။

### ၃။ Embedding Model တူညီမှု မရှိခြင်း

Embedding ဖန်တီးသော model နှင့် search လုပ်သော model သည် **တူညီရပါမည်**။

```php
// ✅ မှန်ကန်
$index = Embeddings::for($text)->generate(model: 'text-embedding-3-small');
$search = Embeddings::for($query)->generate(model: 'text-embedding-3-small');

// ❌ မှားယွင်း
$index = Embeddings::for($text)->generate(model: 'text-embedding-3-small');
$search = Embeddings::for($query)->generate(model: 'text-embedding-ada-002');
```

---

## Best Practices (အကောင်းဆုံး လုပ်ဆောင်ချက်များ)

### ၁။ Agent Class များကို သီးခြား ဖန်တီးပါ

```php
// ✅ ကောင်းသော pattern
php artisan make:agent CustomerSupportAgent
php artisan make:agent SalesCoachAgent

// Agent များကို app/Ai/Agents/ တွင် သိမ်းပါ
```

### ๒။ Tools များကို သီးခြား ဖန်တီးပါ

```php
// ✅ ကောင်းသော pattern
php artisan make:tool DatabaseSearchTool
php artisan make:tool WeatherTool

// Tools များကို app/Ai/Tools/ တွင် သိမ်းပါ
```

### ၃။ Conversation Memory ကို အသုံးပြုပါ

Long conversations များအတွက် `RemembersConversations` trait ကို အသုံးပြုပါ။

```php
class ChatBot implements Agent, Conversational
{
    use Promptable, RemembersConversations;
    // ...
}
```

### ๔။ Embeddings ကို Cache လုပ်ပါ

Performance အတွက် embeddings များကို cache လုပ်ပါ။

```php
$response = Embeddings::for($texts)
    ->dimensions(1536)
    ->cache()  // ← Cache လုပ်ခြင်း
    ->generate();
```

### ၅။ Instructions ကို ရှင်းလင်းစွာ ရေးပါ

Agent ၏ `instructions()` method တွင် ရှင်းလင်းသော prompt ကို ရေးသားပါ။

```php
public function instructions(): string
{
    return <<<'PROMPT'
You are a helpful customer support agent for XYZ Company.

Your responsibilities:
1. Answer product questions
2. Help with troubleshooting
3. Escalate complex issues

Rules:
- Be polite and professional
- Keep responses concise (2-4 sentences)
- If unsure, say "Let me check that for you"
PROMPT;
}
```

---

## Project မှ Real Examples

### Example ๑: SurveyAgent (Survey ကောက်ခံသော Agent)

```php
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
        return <<<PROMPT
You are a friendly host at "{$this->event->name}".

YOUR DUAL ROLE:
1. SURVEY — gather insights from visitors
2. HELPER — answer questions using EventKnowledgeSearch tool

Rules:
- ONE question at a time
- Use EventKnowledgeSearch for product/event questions
- Keep responses 2-4 sentences
PROMPT;
    }

    public function tools(): iterable
    {
        return [
            new EventKnowledgeSearch($this->event, $this->booth),
        ];
    }
}
```

### Example ๒: SummarizationAgent (အနှစ်ချုပ် Agent)

```php
#[Model('openai/gpt-4o')]
#[Temperature(0.3)]  // Low temperature for consistent output
class SummarizationAgent implements Agent
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return <<<'PROMPT'
You are a survey data analyst. Return ONLY a JSON object with:
- total_visitors: integer
- key_themes: array of strings
- sentiment: "positive", "neutral", or "negative"
- actionable_insights: array of strings

IMPORTANT: Return valid JSON only. No markdown fences.
PROMPT;
    }
}
```

---

## Artisan Commands

```bash
# Agent ဖန်တီးခြင်း
php artisan make:agent MyAgent

# Tool ဖန်တီးခြင်း
php artisan make:tool MyTool
```

---

## အကျဉ်းချုပ်

Laravel AI SDK သည်:

✅ **Unified API** - Provider များစွာအတွက် API တစ်ခုတည်း  
✅ **Type-Safe** - PHP 8 features များ အပြည့်အဝ အသုံးပြု  
✅ **Laravel Integration** - Laravel patterns များနှင့် ကိုက်ညီ  
✅ **Production-Ready** - Caching, queueing, failover support  
✅ **Testable** - Built-in fake methods များ  

AI-powered applications များ တည်ဆောက်ရန် Laravel AI SDK သည် အကောင်းဆုံး ရွေးချယ်မှု ဖြစ်ပါသည်။

---

## Additional Resources

- **Official Documentation**: Laravel AI SDK documentation
- **Package**: `composer require laravel/ai`
- **Namespace**: `Laravel\Ai\`
- **Provider Enum**: `Laravel\Ai\Enums\Lab`

---

**Note**: ဤ documentation သည် Laravel AI SDK v0.x အတွက် ဖြစ်ပါသည်။ အသေးစိတ် အချက်အလက်များအတွက် official documentation ကို ဖတ်ရှုပါ။
