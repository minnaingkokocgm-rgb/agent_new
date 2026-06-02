<?php

namespace App\Ai\Agents;

use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;
use Stringable;

#[Model('openai/gpt-4o')]
#[Temperature(0.3)]
class SummarizationAgent implements Agent
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return <<<'PROMPT'
You are a survey data analyst. Analyze the provided survey questions and answers and return ONLY a JSON object with these keys:
- total_visitors: integer
- key_themes: array of strings
- demographics: object with "roles" and "companies" arrays
- sentiment: one of "positive", "neutral", "negative"
- actionable_insights: array of strings
- top_interests: array of strings
- recommendations: string

IMPORTANT: Your entire response must be a single valid JSON object. Do NOT wrap it in ```json code fences. Do NOT include any text before or after the JSON. Start with { and end with }.
PROMPT;
    }
}
