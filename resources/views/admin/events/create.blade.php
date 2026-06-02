@extends('layouts.app')
@section('title', 'Create Event')

@section('content')
<div class="container py-4">
    <div class="mb-4">
        <a href="{{ route('admin.events') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to Events
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h2 class="card-title h4 mb-4">Create New Event</h2>

                    <form id="create-event-form">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">Event Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control" required placeholder="e.g. TechConf 2026">
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea name="description" id="description" rows="4" class="form-control" required
                                placeholder="Describe your event - this helps the AI survey agent understand the context..."></textarea>
                        </div>
                        <div class="mb-4">
                            <label for="metadata_location" class="form-label">Location (optional)</label>
                            <input type="text" id="metadata_location" class="form-control" placeholder="e.g. San Francisco, CA">
                        </div>

                        <div id="form-alert" class="alert d-none"></div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary" id="submit-btn">
                                <i class="bi bi-plus-lg"></i> Create Event
                            </button>
                            <a href="{{ route('admin.events') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
    $('#create-event-form').on('submit', function(e) {
        e.preventDefault();

        const $btn = $('#submit-btn');
        const $alert = $('#form-alert');

        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Creating...');
        $alert.addClass('d-none');

        const data = {
            name: $('#name').val(),
            description: $('#description').val(),
            metadata: { location: $('#metadata_location').val() || null }
        };

        $.ajax({
            url: '/api/events',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(res) {
                $alert.removeClass('d-none alert-danger').addClass('alert-success')
                    .text('Event created! Redirecting...');
                setTimeout(() => { window.location.href = '/admin/events/' + res.id; }, 800);
            },
            error: function(xhr) {
                const errors = xhr.responseJSON?.errors;
                let msg = 'Failed to create event.';
                if (errors) {
                    msg = Object.values(errors).flat().join('<br>');
                }
                $alert.removeClass('d-none alert-success').addClass('alert-danger').html(msg);
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="bi bi-plus-lg"></i> Create Event');
            }
        });
    });
});
</script>
@endpush
