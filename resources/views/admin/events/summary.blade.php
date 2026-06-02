@extends('layouts.app')
@section('title', 'Summary - ' . $event['name'])

@section('content')
<div class="container py-4">
    <div class="mb-3">
        <a href="{{ route('admin.events.show', $event['id']) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to Event
        </a>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">AI-Generated Summary</h1>
            <p class="text-muted mb-0">{{ $event['name'] }}</p>
        </div>
        <button class="btn btn-primary" id="regenerate-btn">
            <i class="bi bi-arrow-repeat"></i> Regenerate
        </button>
    </div>

    <div id="summary-content">
        @if($summary)
            <div class="row g-4">
                <!-- Key Stats -->
                <div class="col-md-4">
                    <div class="card shadow-sm border-primary border-start border-3">
                        <div class="card-body text-center">
                            <h1 class="display-4 text-primary">{{ $summary->content['total_visitors'] ?? 0 }}</h1>
                            <p class="text-muted mb-0">Total Visitors</p>
                        </div>
                    </div>
                </div>

                <!-- Sentiment -->
                <div class="col-md-4">
                    <div class="card shadow-sm border-{{ ($summary->content['sentiment'] ?? 'neutral') === 'positive' ? 'success' : (($summary->content['sentiment'] ?? '') === 'negative' ? 'danger' : 'warning') }} border-start border-3">
                        <div class="card-body text-center">
                            <h1 class="display-4">
                                @if(($summary->content['sentiment'] ?? '') === 'positive')
                                    😊
                                @elseif(($summary->content['sentiment'] ?? '') === 'negative')
                                    😟
                                @else
                                    😐
                                @endif
                            </h1>
                            <p class="text-muted mb-0 text-capitalize">Sentiment: {{ $summary->content['sentiment'] ?? 'neutral' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Generated -->
                <div class="col-md-4">
                    <div class="card shadow-sm border-info border-start border-3">
                        <div class="card-body text-center">
                            <p class="text-muted small mb-1">Generated</p>
                            <p class="fw-bold mb-0">{{ $summary->generated_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mt-2">
                <!-- Key Themes -->
                @if(!empty($summary->content['key_themes']))
                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-white"><h6 class="mb-0">Key Themes</h6></div>
                        <div class="card-body">
                            @foreach($summary->content['key_themes'] as $theme)
                                <span class="badge bg-primary me-1 mb-1">{{ $theme }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <!-- Top Interests -->
                @if(!empty($summary->content['top_interests']))
                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-white"><h6 class="mb-0">Top Interests</h6></div>
                        <div class="card-body">
                            @foreach($summary->content['top_interests'] as $interest)
                                <span class="badge bg-success me-1 mb-1">{{ $interest }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <!-- Demographics -->
                @if(!empty($summary->content['demographics']))
                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-white"><h6 class="mb-0">Demographics</h6></div>
                        <div class="card-body">
                            @if(!empty($summary->content['demographics']['roles']))
                                <p class="fw-medium small mb-1">Roles:</p>
                                @foreach($summary->content['demographics']['roles'] as $role)
                                    <span class="badge bg-info me-1 mb-1">{{ $role }}</span>
                                @endforeach
                            @endif
                            @if(!empty($summary->content['demographics']['companies']))
                                <p class="fw-medium small mb-1 mt-2">Companies:</p>
                                @foreach($summary->content['demographics']['companies'] as $company)
                                    <span class="badge bg-secondary me-1 mb-1">{{ $company }}</span>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                <!-- Actionable Insights -->
                @if(!empty($summary->content['actionable_insights']))
                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-white"><h6 class="mb-0">Actionable Insights</h6></div>
                        <div class="card-body">
                            <ul class="mb-0">
                                @foreach($summary->content['actionable_insights'] as $insight)
                                    <li>{{ $insight }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Recommendations -->
                @if(!empty($summary->content['recommendations']))
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white"><h6 class="mb-0">Recommendations</h6></div>
                        <div class="card-body">
                            <p class="mb-0">{{ $summary->content['recommendations'] }}</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Raw JSON (collapsed) -->
            <div class="mt-4">
                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#rawJson">
                    <i class="bi bi-code-slash"></i> View Raw JSON
                </button>
                <div class="collapse mt-2" id="rawJson">
                    <pre class="bg-light p-3 rounded"><code>{{ json_encode($summary->content, JSON_PRETTY_PRINT) }}</code></pre>
                </div>
            </div>
        @else
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-robot text-muted" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">No Summary Yet</h5>
                    <p class="text-muted">Generate your first AI summary to analyze survey data. You need completed survey sessions first.</p>
                    <button class="btn btn-primary" id="generate-btn">
                        <i class="bi bi-magic"></i> Generate Summary
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
    function loadSummary() {
        $.get('/api/events/{{ $event['id'] }}/summary', function() {
            location.reload();
        }).fail(function() {
            alert('Failed to load summary. Make sure there are completed survey sessions.');
        });
    }

    $('#regenerate-btn').on('click', function() {
        if (!confirm('Delete existing summary and regenerate?')) return;
        const $btn = $(this);
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Regenerating...');

        $.ajax({
            url: '/api/events/{{ $event['id'] }}/summary/regenerate',
            method: 'POST',
            success: function() { location.reload(); },
            error: function() { alert('Regeneration failed.'); },
            complete: function() { $btn.prop('disabled', false).html('<i class="bi bi-arrow-repeat"></i> Regenerate'); }
        });
    });

    $('#generate-btn').on('click', function() {
        const $btn = $(this);
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Generating...');
        loadSummary();
    });
});
</script>
@endpush
