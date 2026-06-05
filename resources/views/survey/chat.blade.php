@extends('layouts.app')
@section('title', 'Survey - ' . $event['name'])
@section('bodyClass', 'survey-page')

@section('content')
<div class="survey-header bg-primary text-white py-3 px-3 d-flex align-items-center justify-content-between">
    <div>
        <h5 class="mb-0">{{ $event['name'] }}</h5>
        <small class="opacity-75">{{ $booth['name'] ?? 'General Survey' }}</small>
    </div>
    <span class="badge bg-light text-primary" id="status-badge">Active</span>
</div>

<div class="chat-container" id="chatContainer">
    <div class="chat-messages" id="chatMessages">
        <!-- Messages will be appended here -->
    </div>

    <!-- Typing Indicator -->
    <div class="typing-dots d-none" id="typingIndicator">
        <span></span><span></span><span></span>
    </div>

    <!-- Input Area -->
    <div class="border-top bg-white p-3" id="inputArea">
        <form id="answerForm" class="d-flex gap-2">
            <input type="text" id="answerInput" class="form-control" placeholder="Type your answer..." autocomplete="off" autofocus>
            <button type="submit" class="btn btn-primary" id="sendBtn">
                <i class="bi bi-send"></i>
            </button>
        </form>
        <div class="text-center text-muted small mt-1" id="questionCounter">Question 1 of 4</div>
    </div>

    <!-- Completed State -->
    <div class="border-top bg-white p-4 text-center d-none" id="completedArea">
        <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
        <h5 class="mt-3">Thank You!</h5>
        <p class="text-muted">Your responses have been recorded. We appreciate your time!</p>
        <a href="{{ route('survey.complete', $event['id']) }}" class="btn btn-primary">Finish</a>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
    const eventId = {{ $event['id'] }};
    const boothId = {{ isset($booth) ? $booth['id'] : 'null' }};
    const visitorId = {{ $visitorId ?? 'null' }};
    let sessionId = null;
    let questionCount = 0;
    let isCompleted = false;

    const $messages = $('#chatMessages');
    const $input = $('#answerInput');
    const $typing = $('#typingIndicator');
    const $sendBtn = $('#sendBtn');
    const $inputArea = $('#inputArea');
    const $completedArea = $('#completedArea');
    const $statusBadge = $('#status-badge');
    const $questionCounter = $('#questionCounter');

    function addMessage(text, role) {
        const now = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        const bubble = $(`
            <div class="chat-bubble ${role}">
                <div class="message-text"></div>
                <div class="timestamp">${now}</div>
            </div>
        `);
        bubble.find('.message-text').text(text);
        $messages.append(bubble);
        scrollToBottom();
    }

    function showTyping() {
        $typing.removeClass('d-none');
        scrollToBottom();
    }

    function hideTyping() {
        $typing.addClass('d-none');
    }

    function scrollToBottom() {
        $messages.scrollTop($messages[0].scrollHeight);
    }

    function disableInput() {
        $input.prop('disabled', true);
        $sendBtn.prop('disabled', true);
    }

    function enableInput() {
        $input.prop('disabled', false);
        $sendBtn.prop('disabled', false);
        $input.focus();
    }

    function completeSurvey() {
        isCompleted = true;
        $inputArea.addClass('d-none');
        $completedArea.removeClass('d-none');
        $statusBadge.removeClass('bg-light text-primary').addClass('bg-success').text('Completed');
        $questionCounter.text('Completed');
    }

    function startSurvey() {
        showTyping();
        disableInput();

        $.ajax({
            url: '/api/survey/start',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                event_id: eventId,
                booth_id: boothId,
                visitor_id: visitorId
            }),
            success: function(res) {
                sessionId = res.session_id;
                questionCount = 1;
                hideTyping();
                addMessage(res.message, 'ai');
                $questionCounter.text('Question ' + questionCount + ' of 4');
                enableInput();
            },
            error: function() {
                hideTyping();
                addMessage('Sorry, something went wrong. Please refresh the page.', 'ai');
            }
        });
    }

    function submitAnswer(answer) {
        if (isCompleted || !sessionId) return;

        addMessage(answer, 'user');
        disableInput();
        showTyping();
        $input.val('');

        $.ajax({
            url: '/api/survey/' + sessionId + '/answer',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ answer: answer }),
            success: function(res) {
                hideTyping();
                addMessage(res.message, 'ai');

                if (res.status === 'completed') {
                    completeSurvey();
                } else {
                    questionCount++;
                    $questionCounter.text('Question ' + questionCount + ' of 4');
                    enableInput();
                }
            },
            error: function(xhr) {
                hideTyping();
                if (xhr.status === 422) {
                    addMessage('This survey session is already completed.', 'ai');
                    completeSurvey();
                } else {
                    addMessage('An error occurred. Please try again.', 'ai');
                    enableInput();
                }
            }
        });
    }

    // Send answer on form submit
    $('#answerForm').on('submit', function(e) {
        e.preventDefault();
        const answer = $input.val().trim();
        if (answer) {
            submitAnswer(answer);
        }
    });

    // Start the survey
    startSurvey();
});
</script>
@endpush
