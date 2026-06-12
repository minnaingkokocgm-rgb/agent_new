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
            <div class="mb-3">
                <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                <input type="text" name="name" id="name" class="form-control" required
                    placeholder="Your full name">
            </div>

            <div class="mb-3">
                <label for="company" class="form-label">Company</label>
                <input type="text" name="company" id="company" class="form-control"
                    placeholder="Company name">
            </div>

            <div class="mb-3">
                <label for="industry" class="form-label">Industry</label>
                <select name="industry" id="industry" class="form-select">
                    <option value="">Select industry...</option>
                    <option value="exporters">Exporters</option>
                    <option value="importers">Importers</option>
                    <option value="wholesalers">Wholesalers</option>
                    <option value="department_stores">Department stores</option>
                    <option value="supermarkets_convenience_stores">Supermarkets/convenience stores</option>
                    <option value="other_retailers">Other retailers (liquor stores, butcher shops, greengrocers, fishmongers, etc.)</option>
                    <option value="mail_order_businesses">Mail-order businesses</option>
                    <option value="restaurants">Restaurants</option>
                    <option value="hotels">Hotels</option>
                    <option value="catering_companies">Catering companies</option>
                    <option value="meat_manufacturers">Meat manufacturers</option>
                    <option value="agricultural_product_manufacturers">Agricultural product manufacturers</option>
                    <option value="seafood_manufacturers">Seafood manufacturers</option>
                    <option value="liquor_manufacturers">Liquor manufacturers</option>
                    <option value="food_and_beverage_manufacturers">Food and beverage manufacturers</option>
                    <option value="other_food_manufacturers">Other food manufacturers</option>
                    <option value="producers_agricultural_cooperatives">Producers/agricultural cooperatives</option>
                    <option value="logistics_warehousing">Logistics/warehousing</option>
                    <option value="import_export_support_consulting">Import/export support/consulting</option>
                    <option value="government_agencies_local_authorities">Government agencies/local authorities/industry associations</option>
                    <option value="embassies_consulates">Embassies/consulates</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="department" class="form-label">Department</label>
                <input type="text" name="department" id="department" class="form-control"
                    placeholder="Your department">
            </div>

            <div class="mb-3">
                <label for="post" class="form-label">Post</label>
                <input type="text" name="post" id="post" class="form-control"
                    placeholder="Your job title or position">
            </div>

            <div class="mb-3">
                <label for="post_code" class="form-label">Post Code</label>
                <input type="text" name="post_code" id="post_code" class="form-control"
                    placeholder="Postal/ZIP code">
            </div>

            <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <input type="text" name="address" id="address" class="form-control"
                    placeholder="Your address">
            </div>

            <div class="mb-3">
                <label for="phone" class="form-label">Phone Number</label>
                <input type="tel" name="phone" id="phone" class="form-control"
                    placeholder="Your phone number">
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" id="email" class="form-control" required
                    placeholder="you@example.com">
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                <input type="password" name="password" id="password" class="form-control" required
                    placeholder="Minimum 6 characters" minlength="6">
            </div>

            <div class="mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="opt_out" id="opt_out" value="1">
                    <label class="form-check-label small" for="opt_out">
                        Those who register will receive information about our exhibitions, seminars, and related services via direct mail, email, etc. If you do not wish to receive such information, please check the box below. <strong>(I do not wish to.)</strong>
                    </label>
                </div>
            </div>

            <div class="mb-3">
                <label for="reception_category" class="form-label">Category at Reception</label>
                <select name="reception_category" id="reception_category" class="form-select">
                    <option value="">Please select your application type</option>
                    <option value="category_1">Category 1</option>
                    <option value="category_2">Category 2</option>
                    <option value="category_3">Category 3</option>
                </select>
            </div>

            <div class="mb-4">
                <label for="responsible_organization" class="form-label">Responsible Organization</label>
                <select name="responsible_organization" id="responsible_organization" class="form-select">
                    <option value="">Select organization...</option>
                    <option value="organization_1">Organization 1</option>
                    <option value="organization_2">Organization 2</option>
                    <option value="organization_3">Organization 3</option>
                </select>
            </div>

            <div id="formAlert" class="alert d-none"></div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="bi bi-check-lg"></i> Submit Registration
                </button>
                <button type="button" class="btn btn-outline-secondary" id="resetBtn">
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
                company: $('#company').val(),
                industry: $('#industry').val(),
                department: $('#department').val(),
                post: $('#post').val(),
                post_code: $('#post_code').val(),
                address: $('#address').val(),
                phone: $('#phone').val(),
                email: $('#email').val(),
                password: $('#password').val(),
                opt_out: $('#opt_out').is(':checked'),
                reception_category: $('#reception_category').val(),
                responsible_organization: $('#responsible_organization').val()
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
