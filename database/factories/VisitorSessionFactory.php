<?php

namespace Database\Factories;

use App\Models\Booth;
use App\Models\Event;
use App\Models\Visitor;
use App\Models\VisitorSession;
use Illuminate\Database\Eloquent\Factories\Factory;

class VisitorSessionFactory extends Factory
{
    protected $model = VisitorSession::class;

    public function definition(): array
    {
        return [
            'visitor_id' => Visitor::factory(),
            'event_id' => Event::factory(),
            'booth_id' => null,
            'status' => 'active',
            'started_at' => now(),
            'completed_at' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function forBooth(Booth $booth): static
    {
        return $this->state(fn () => [
            'event_id' => $booth->event_id,
            'booth_id' => $booth->id,
        ]);
    }
}
