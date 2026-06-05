@extends('layouts.app')
@section('title', 'Register - ' . $event['name'])
@section('bodyClass', 'survey-page')

@push('styles')
<style>
    .registration-page {
        display: flex;
        height: calc(100vh - 56px);
        overflow: hidden;
    }
    .registration-form-panel {
        flex: 1;
        overflow-y: auto;
        padding: 1.5rem;
        border-right: 1px solid #dee2e6;
    }
    .registration-chat-panel {
        width: 380px;
        display: flex;
        flex-direction: column;
        background: #f8f9fa;
    }
    .chat-panel-header {
        padding: 1rem;
        background: #0d6efd;
        color: white;
        font-weight: 600;
    }
    .chat-panel-messages {
        flex: 1;
        overflow-y: auto;
        padding: 1rem;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    .chat-panel-input {
        padding: 0.75rem;
        background: white;
        border-top: 1px solid #dee2e6;
    }
    .chat-panel-bubble {
        max-width: 90%;
        padding: 0.5rem 0.75rem;
        border-radius: 0.75rem;
        font-size: 0.85rem;
        line-height: 1.4;
        word-wrap: break-word;
    }
    .chat-panel-bubble.ai {
        align-self: flex-start;
        background: white;
        border: 1px solid #dee2e6;
    }
    .chat-panel-bubble.user {
        align-self: flex-end;
        background: #0d6efd;
        color: white;
    }
    .chat-panel-typing {
        align-self: flex-start;
        padding: 0.5rem 0.75rem;
        font-size: 0.85rem;
        color: #999;
    }
    @media (max-width: 768px) {
        .registration-page {
            flex-direction: column;
        }
        .registration-chat-panel {
            width: 100%;
            max-height: 300px;
        }
    }
    .form-section-title {
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #0d6efd;
        font-weight: 600;
        margin-bottom: 0.75rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #e0e0e0;
    }
</style>
@endpush

@section('content')
<div class="registration-page">
    <!-- Left: Registration Form -->
    <div class="registration-form-panel">
        <div class="mb-4">
            <h4 class="fw-bold">{{ $event['name'] }}</h4>
            <p class="text-muted small mb-0">Complete the form below. Need help? Use the AI assistant on the right.</p>
        </div>

        <form id="registrationForm">
            <div class="form-section-title">
                <i class="bi bi-person-badge"></i> Personal Information
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" class="form-control" required
                        placeholder="As it should appear on your badge">
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                    <input type="email" name="email" id="email" class="form-control" required
                        placeholder="you@company.com">
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="phone" class="form-label">Phone Number <small class="text-muted">(optional)</small></label>
                    <input type="text" name="phone" id="phone" class="form-control"
                        placeholder="+1-555-123-4567">
                </div>
                <div class="col-md-6">
                    <label for="country" class="form-label">Country <small class="text-muted">(optional)</small></label>
                    <select name="country" id="country" class="form-select">
                        <option value="">Select country...</option>
                        <option value="US">United States</option>
                        <option value="GB">United Kingdom</option>
                        <option value="DE">Germany</option>
                        <option value="FR">France</option>
                        <option value="JP">Japan</option>
                        <option value="SG">Singapore</option>
                        <option value="MM">Myanmar</option>
                        <option value="TH">Thailand</option>
                        <option value="VN">Vietnam</option>
                        <option value="ID">Indonesia</option>
                        <option value="PH">Philippines</option>
                        <option value="AU">Australia</option>
                        <option value="CA">Canada</option>
                    </select>
                </div>
            </div>

            <div class="form-section-title mt-4">
                <i class="bi bi-building"></i> Professional Information
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="company" class="form-label">Company / Organization <span class="text-danger">*</span></label>
                    <input type="text" name="company" id="company" class="form-control"
                        placeholder="Where you work">
                </div>
                <div class="col-md-6">
                    <label for="job_title" class="form-label">Job Title / Role <span class="text-danger">*</span></label>
                    <input type="text" name="job_title" id="job_title" class="form-control"
                        placeholder="e.g. Software Engineer, CTO">
                </div>
            </div>

            <div class="form-section-title mt-4">
                <i class="bi bi-info-circle"></i> Additional Information
            </div>

            <div class="mb-3">
                <label for="source" class="form-label">How did you hear about this event? <small class="text-muted">(optional)</small></label>
                <select name="source" id="source" class="form-select">
                    <option value="">Select...</option>
                    <option value="social_media">Social Media (LinkedIn, Twitter, etc.)</option>
                    <option value="email">Email Invitation</option>
                    <option value="referral">Friend / Colleague Referral</option>
                    <option value="website">Event Website</option>
                    <option value="search">Search Engine</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div class="mb-4">
                <label for="notes" class="form-label">Notes / Special Requirements <small class="text-muted">(optional)</small></label>
                <textarea name="notes" id="notes" rows="3" class="form-control"
                    placeholder="Dietary restrictions, accessibility needs, questions for organizers..."></textarea>
            </div>

            <div id="formAlert" class="alert d-none"></div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                    <i class="bi bi-check-lg"></i> Submit Registration
                </button>
                <button type="button" class="btn btn-outline-secondary btn-lg" id="resetBtn">
                    Reset Form
                </button>
            </div>
        </form>
    </div>

    <!-- Right: AI Chat Assistant -->
    <div class="registration-chat-panel">
        <div class="chat-panel-header">
            <i class="bi bi-robot"></i> AI Assistant
        </div>
        <div class="chat-panel-messages" id="chatMessages">
            <!-- Messages via JS -->
        </div>
        <div id="chatTyping" class="chat-panel-typing d-none">
            <span class="spinner-border spinner-border-sm me-1"></span> Thinking...
        </div>
        <div class="chat-panel-input">
            <form id="chatForm" class="d-flex gap-2">
                <input type="text" id="chatInput" class="form-control form-control-sm"
                    placeholder="Ask about the form or event...">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-send"></i>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
    const eventId = {{ $event['id'] }};
    let sessionToken = null;
    let isSubmitting = false;

    const $chatMessages = $('#chatMessages');
    const $chatInput = $('#chatInput');
    const $chatTyping = $('#chatTyping');
    const $chatForm = $('#chatForm');
    const $formAlert = $('#formAlert');
    const $submitBtn = $('#submitBtn');
    const $registrationForm = $('#registrationForm');

    function addChatMessage(text, role) {
        const bubble = $('<div class="chat-panel-bubble ' + role + '"></div>').text(text);
        $chatMessages.append(bubble);
        $chatMessages.scrollTop($chatMessages[0].scrollHeight);
    }

    function showChatTyping() {
        $chatTyping.removeClass('d-none');
        $chatMessages.scrollTop($chatMessages[0].scrollHeight);
    }

    function hideChatTyping() {
        $chatTyping.addClass('d-none');
    }

    // Start AI chat session on page load
    function startAIChat() {
        showChatTyping();
        $.ajax({
            url: '/api/registration/start',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ event_id: eventId }),
            success: function(res) {
                sessionToken = res.session_token;
                hideChatTyping();
                addChatMessage(res.message, 'ai');
            },
            error: function() {
                hideChatTyping();
                addChatMessage('Hi! I\'m here to help with your registration. Feel free to ask questions!', 'ai');
            }
        });
    }

    // Ask AI a question
    $chatForm.on('submit', function(e) {
        e.preventDefault();
        const question = $chatInput.val().trim();
        if (!question || !sessionToken) return;

        addChatMessage(question, 'user');
        $chatInput.val('');
        showChatTyping();

        $.ajax({
            url: '/api/registration/ask',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                session_token: sessionToken,
                question: question
            }),
            success: function(res) {
                hideChatTyping();
                addChatMessage(res.message, 'ai');
            },
            error: function() {
                hideChatTyping();
                addChatMessage('Sorry, I had trouble answering that. Please try again.', 'ai');
            }
        });
    });

    // Submit registration form
    $registrationForm.on('submit', function(e) {
        e.preventDefault();
        if (isSubmitting || !sessionToken) return;

        isSubmitting = true;
        $submitBtn.prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm"></span> Submitting...');
        $formAlert.addClass('d-none');

        $.ajax({
            url: '/api/registration/submit',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                session_token: sessionToken,
                name: $('#name').val(),
                email: $('#email').val(),
                phone: $('#phone').val(),
                company: $('#company').val(),
                job_title: $('#job_title').val(),
                country: $('#country').val(),
                source: $('#source').val(),
                notes: $('#notes').val()
            }),
            success: function(res) {
                $formAlert.removeClass('d-none alert-danger').addClass('alert-success')
                    .html('<i class="bi bi-check-circle"></i> ' + res.message + ' Redirecting to survey...');
                // Redirect to survey page with visitor_id
                setTimeout(function() {
                    window.location.href = '/s/' + eventId + '?visitor_id=' + res.visitor_id;
                }, 800);
            },
            error: function(xhr) {
                const errors = xhr.responseJSON?.errors;
                let msg = 'Please fix the errors below and try again.';
                if (errors) {
                    msg = '<strong>Please fix:</strong><br>' + Object.values(errors).flat().join('<br>');
                }
                $formAlert.removeClass('d-none alert-success').addClass('alert-danger').html(msg);
            },
            complete: function() {
                isSubmitting = false;
                $submitBtn.prop('disabled', false)
                    .html('<i class="bi bi-check-lg"></i> Submit Registration');
            }
        });
    });

    // Reset form
    $('#resetBtn').on('click', function() {
        $registrationForm[0].reset();
        $formAlert.addClass('d-none');
        $submitBtn.prop('disabled', false)
            .html('<i class="bi bi-check-lg"></i> Submit Registration')
            .removeClass('btn-success').addClass('btn-primary');
        $registrationForm.find('input, select, textarea').prop('disabled', false);
    });

    // Start chat on load
    startAIChat();
});
</script>
@endpush
