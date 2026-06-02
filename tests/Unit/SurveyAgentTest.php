<?php

use App\Ai\Agents\SurveyAgent;
use App\Ai\Tools\EventKnowledgeSearch;
use App\Models\Event;

beforeEach(function () {
    $this->event = Event::factory()->create([
        'name' => 'TechConf 2026',
        'description' => 'A technology conference.',
    ]);
});

test('survey agent asks questions sequentially', function () {
    SurveyAgent::fake([
        'Welcome! What is your name?',
        'What brings you here today?',
        'What technologies interest you?',
        'What is your timeline?',
        'How can we follow up?',
        'Thank you! [SURVEY_COMPLETE]',
    ]);

    $agent = SurveyAgent::make($this->event, null);

    $response = $agent->prompt('Start conversation');
    expect($response->text)->toContain('Welcome');

    SurveyAgent::assertPrompted('Start conversation');
});

test('survey agent includes event context in instructions', function () {
    $agent = SurveyAgent::make($this->event, null);

    $instructions = $agent->instructions();

    expect($instructions)->toContain('TechConf 2026')
        ->toContain('A technology conference');
});

test('survey agent includes booth context when provided', function () {
    $booth = $this->event->booths()->create([
        'name' => 'AI Demo Booth',
        'description' => 'Showcasing cutting-edge AI.',
    ]);

    $agent = SurveyAgent::make($this->event, $booth);

    $instructions = $agent->instructions();

    expect($instructions)->toContain('AI Demo Booth')
        ->toContain('Showcasing cutting-edge AI');
});

test('survey agent has event knowledge search tool', function () {
    $agent = SurveyAgent::make($this->event, null);

    $tools = iterator_to_array($agent->tools());

    expect($tools)->toHaveCount(1);
    expect($tools[0])->toBeInstanceOf(EventKnowledgeSearch::class);
});

test('survey agent can be faked for testing', function () {
    SurveyAgent::fake(['Hello world!']);

    $agent = SurveyAgent::make($this->event, null);

    expect(SurveyAgent::isFaked())->toBeTrue();

    $response = $agent->prompt('test');
    expect($response->text)->toBe('Hello world!');
});
