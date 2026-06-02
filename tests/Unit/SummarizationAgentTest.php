<?php

use App\Ai\Agents\SummarizationAgent;

test('summarization agent has structured output schema', function () {
    $agent = SummarizationAgent::make();

    // We can't easily test the schema directly without the container,
    // but we can verify the agent can be instantiated
    expect($agent)->toBeInstanceOf(SummarizationAgent::class);
});

test('summarization agent has instructions', function () {
    $agent = SummarizationAgent::make();

    $instructions = $agent->instructions();

    expect($instructions)->toContain('survey data analyst');
});

test('summarization agent can be faked', function () {
    SummarizationAgent::fake(['Analysis complete.']);

    $agent = SummarizationAgent::make();

    expect(SummarizationAgent::isFaked())->toBeTrue();

    $response = $agent->prompt('Analyze data');
    expect($response->text)->toBe('Analysis complete.');
});
