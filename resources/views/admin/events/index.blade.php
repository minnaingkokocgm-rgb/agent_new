@extends('layouts.app')
@section('title', 'Events')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Events</h1>
            <p class="text-muted mb-0">Manage your events and booths</p>
        </div>
        <a href="{{ route('admin.events.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> New Event
        </a>
    </div>

    @if($events->isEmpty())
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                <h5 class="mt-3">No Events Yet</h5>
                <p class="text-muted">Create your first event to start collecting survey responses.</p>
                <a href="{{ route('admin.events.create') }}" class="btn btn-primary">Create Event</a>
            </div>
        </div>
    @else
        <div class="row g-4">
            @foreach($events as $event)
                <div class="col-md-6 col-lg-4">
                    <div class="card shadow-sm event-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0">
                                    <a href="{{ route('admin.events.show', $event) }}" class="text-decoration-none text-dark">
                                        {{ $event->name }}
                                    </a>
                                </h5>
                                <span class="badge bg-primary rounded-pill">{{ $event->booths_count }} booth(s)</span>
                            </div>
                            <p class="card-text text-muted small">{{ Str::limit($event->description, 120) }}</p>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.events.show', $event) }}" class="btn btn-sm btn-outline-primary">Manage</a>
                                <a href="{{ route('admin.events.summary', $event) }}" class="btn btn-sm btn-outline-secondary">Summary</a>
                                <a href="{{ route('survey.chat', $event) }}" target="_blank" class="btn btn-sm btn-outline-success">
                                    <i class="bi bi-box-arrow-up-right"></i> Survey
                                </a>
                                <a href="{{ route('survey.register', $event) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                    <i class="bi bi-pencil-square"></i> Register
                                </a>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent text-muted small">
                            Created {{ $event->created_at->diffForHumans() }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
