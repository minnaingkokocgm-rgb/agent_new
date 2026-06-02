@extends('layouts.app')
@section('title', 'Registration Complete')

@section('content')
<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-6 text-center">
            <div class="card shadow-sm">
                <div class="card-body p-5">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                    <h3 class="mt-4">Registration Submitted!</h3>
                    <p class="text-muted mb-4">
                        Thank you for registering for<br>
                        <strong>{{ $event['name'] }}</strong>
                    </p>
                    <div class="alert alert-success text-start">
                        <i class="bi bi-info-circle"></i>
                        <strong>What's next?</strong><br>
                        A confirmation email will be sent to your inbox with event details, 
                        schedule, and venue information.
                    </div>
                    <a href="{{ route('survey.chat', $event['id']) }}" class="btn btn-outline-primary">
                        <i class="bi bi-chat-dots"></i> Take the Survey
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
