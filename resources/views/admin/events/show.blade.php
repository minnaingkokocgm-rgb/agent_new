@extends('layouts.app')
@section('title', $event->name)

@section('content')
<div class="container py-4">
    <div class="mb-3">
        <a href="{{ route('admin.events') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <!-- Event Header -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <h1 class="h3 fw-bold mb-2">{{ $event->name }}</h1>
                    <p class="text-muted mb-0">{{ $event->description }}</p>
                    @if($event->metadata)
                        <span class="badge bg-secondary mt-2">{{ $event->metadata['location'] ?? '' }}</span>
                    @endif
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('admin.events.summary', $event) }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-graph-up"></i> AI Summary
                    </a>
                    <a href="{{ route('survey.chat', $event) }}" target="_blank" class="btn btn-outline-success btn-sm">
                        <i class="bi bi-chat-dots"></i> Open Survey
                    </a>
                    <a href="{{ route('survey.register', $event) }}" target="_blank" class="btn btn-outline-info btn-sm">
                        <i class="bi bi-pencil-square"></i> Registration
                    </a>
                    <button class="btn btn-outline-danger btn-sm" id="delete-event-btn">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Column: Booths -->
        <div class="col-lg-5">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Booths ({{ $event->booths->count() }})</h5>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createBoothModal">
                        <i class="bi bi-plus-lg"></i> Add Booth
                    </button>
                </div>
                <div class="card-body">
                    @forelse($event->booths as $booth)
                        <div class="d-flex justify-content-between align-items-start mb-3 pb-3 border-bottom">
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-1">{{ $booth->name }}</h6>
                                    <div class="d-flex gap-1 align-items-center">
                                        @if($booth->knowledge_chunks_count > 0)
                                            <span class="badge bg-success" title="{{ $booth->knowledge_chunks_count }} knowledge chunks indexed">
                                                <i class="bi bi-database-check"></i> {{ $booth->knowledge_chunks_count }} chunks
                                            </span>
                                        @else
                                            <span class="badge bg-warning text-dark" title="No knowledge uploaded yet">
                                                <i class="bi bi-exclamation-triangle"></i> No knowledge
                                            </span>
                                        @endif
                                        <a href="#knowledge-form" class="btn btn-outline-primary btn-sm" onclick="selectBoothForUpload({{ $booth->id }})" title="Upload knowledge for this booth">
                                            <i class="bi bi-upload"></i>
                                        </a>
                                        <button class="btn btn-outline-danger btn-sm delete-booth-btn" data-id="{{ $booth->id }}" title="Delete booth">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <p class="text-muted small mb-0">{{ Str::limit($booth->description, 100) }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center mb-0">No booths yet. Add one to scope survey questions to a specific booth.</p>
                    @endforelse
                </div>
            </div>

            <!-- Knowledge Base -->
            <div class="card shadow-sm" id="knowledge-form">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Knowledge Base</h5>
                    @if($event->knowledge_chunks_count > 0)
                        <span class="badge bg-success">
                            <i class="bi bi-database-check"></i> {{ $event->knowledge_chunks_count }} total chunks
                        </span>
                    @else
                        <span class="badge bg-warning text-dark">
                            <i class="bi bi-exclamation-triangle"></i> No knowledge uploaded
                        </span>
                    @endif
                </div>
                <div class="card-body">
                    <p class="text-muted small">Upload documents to help the AI agent answer visitor questions about your event.</p>
                    <form id="knowledge-upload-form">
                        <div class="mb-3">
                            <label for="source_name" class="form-label">Source Name</label>
                            <input type="text" id="source_name" class="form-control" placeholder="e.g. product-info.txt" required>
                        </div>
                        <div class="mb-3">
                            <label for="booth_select" class="form-label">Scope to Booth <small class="text-muted">— scoped chunks power booth-specific surveys</small></label>
                            <select id="booth_select" class="form-select">
                                <option value="">Event-wide (applies to all booths)</option>
                                @foreach($event->booths as $booth)
                                    <option value="{{ $booth->id }}">
                                        {{ $booth->name }} ({{ $booth->knowledge_chunks_count }} chunks)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="knowledge_content" class="form-label">Content</label>
                            <textarea id="knowledge_content" rows="6" class="form-control" required
                                placeholder="Paste your knowledge base content here (FAQ, product details, pricing, etc.)..."></textarea>
                        </div>
                        <div id="knowledge-alert" class="alert d-none"></div>
                        <button type="submit" class="btn btn-primary" id="knowledge-btn">
                            <i class="bi bi-upload"></i> Upload &amp; Index
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Column: Sessions -->
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Survey Sessions ({{ $event->sessions->count() }})</h5>
                </div>
                <div class="card-body p-0">
                    @if($event->sessions->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-chat-square-text" style="font-size: 2rem;"></i>
                            <p class="mt-2">No sessions yet. Share the survey link to start collecting responses.</p>
                            <div class="input-group input-group-sm w-75 mx-auto">
                                <input type="text" class="form-control" readonly
                                    value="{{ url('/s/' . $event->id) }}" id="survey-url">
                                <button class="btn btn-outline-secondary" onclick="copySurveyUrl()">
                                    <i class="bi bi-clipboard"></i> Copy
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Visitor</th>
                                        <th>Booth</th>
                                        <th>Questions</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($event->sessions as $session)
                                        <tr>
                                            <td>
                                                <span class="fw-medium">{{ $session->visitor->name ?? 'Anonymous' }}</span><br>
                                                <small class="text-muted">{{ $session->visitor->email ?? '' }}</small>
                                            </td>
                                            <td><small>{{ $session->booth?->name ?? '—' }}</small></td>
                                            <td>{{ $session->questions_count }}</td>
                                            <td>
                                                <span class="badge bg-{{ $session->status === 'completed' ? 'success' : 'warning' }}">
                                                    {{ $session->status }}
                                                </span>
                                            </td>
                                            <td><small>{{ $session->created_at->format('M d, H:i') }}</small></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Booth Modal -->
<div class="modal fade" id="createBoothModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="create-booth-form">
                <div class="modal-header">
                    <h5 class="modal-title">Add Booth</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="booth_name" class="form-label">Booth Name</label>
                        <input type="text" id="booth_name" class="form-control" required placeholder="e.g. NLP Demo Station">
                    </div>
                    <div class="mb-3">
                        <label for="booth_description" class="form-label">Description</label>
                        <textarea id="booth_description" rows="3" class="form-control" required
                            placeholder="What does this booth showcase?"></textarea>
                    </div>
                    <div id="booth-alert" class="alert d-none"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Booth</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function copySurveyUrl() {
    const input = document.getElementById('survey-url');
    input.select();
    document.execCommand('copy');
    alert('Survey URL copied!');
}

// Scroll to knowledge form and pre-select a booth
function selectBoothForUpload(boothId) {
    $('#booth_select').val(boothId);
    $('#knowledge_content').focus();
}

$(function() {
    // Create Booth
    $('#create-booth-form').on('submit', function(e) {
        e.preventDefault();
        const $alert = $('#booth-alert');
        $alert.addClass('d-none');

        $.ajax({
            url: '/api/events/{{ $event->id }}/booths',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                name: $('#booth_name').val(),
                description: $('#booth_description').val()
            }),
            success: function() {
                location.reload();
            },
            error: function(xhr) {
                const errors = xhr.responseJSON?.errors;
                let msg = 'Failed to create booth.';
                if (errors) msg = Object.values(errors).flat().join('<br>');
                $alert.removeClass('d-none').addClass('alert-danger').html(msg);
            }
        });
    });

    // Delete Booth
    $('.delete-booth-btn').on('click', function() {
        if (!confirm('Delete this booth? All associated data will be removed.')) return;
        const id = $(this).data('id');
        $.ajax({
            url: '/api/booths/' + id,
            method: 'DELETE',
            success: function() { location.reload(); }
        });
    });

    // Upload Knowledge
    $('#knowledge-upload-form').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#knowledge-btn');
        const $alert = $('#knowledge-alert');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Indexing...');
        $alert.addClass('d-none');

        $.ajax({
            url: '/api/events/{{ $event->id }}/knowledge',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                content: $('#knowledge_content').val(),
                source_name: $('#source_name').val(),
                booth_id: $('#booth_select').val() || null
            }),
            success: function(res) {
                $alert.removeClass('d-none alert-danger').addClass('alert-success')
                    .html('Indexed <strong>' + res.chunks_created + '</strong> chunks successfully! Reloading...');
                $('#knowledge_content').val('');
                setTimeout(function() { location.reload(); }, 800);
            },
            error: function(xhr) {
                $alert.removeClass('d-none alert-success').addClass('alert-danger')
                    .text('Upload failed: ' + (xhr.responseJSON?.message || 'Unknown error'));
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="bi bi-upload"></i> Upload &amp; Index');
            }
        });
    });

    // Delete Event
    $('#delete-event-btn').on('click', function() {
        if (!confirm('Delete this event and ALL associated data? This cannot be undone.')) return;
        $.ajax({
            url: '/api/events/{{ $event->id }}',
            method: 'DELETE',
            success: function() { window.location.href = '/admin/events'; },
            error: function() { alert('Failed to delete event.'); }
        });
    });
});
</script>
@endpush
