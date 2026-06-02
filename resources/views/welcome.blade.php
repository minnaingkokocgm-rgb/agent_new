@extends('layouts.app')
@section('title', 'Welcome')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 text-center">
            <i class="bi bi-robot text-primary" style="font-size: 4rem;"></i>
            <h1 class="display-5 fw-bold mt-3">AI-Powered Event Survey</h1>
            <p class="lead text-muted">Conduct intelligent, conversational surveys at events and booths — powered by AI.</p>

            <div class="d-flex gap-3 justify-content-center mt-4">
                @auth
                    <a href="{{ route('admin.events') }}" class="btn btn-primary btn-lg px-4">
                        <i class="bi bi-grid"></i> My Events
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary btn-lg px-4">
                        <i class="bi bi-box-arrow-in-right"></i> Login
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn btn-outline-primary btn-lg px-4">
                            <i class="bi bi-person-plus"></i> Register
                        </a>
                    @endif
                @endauth
            </div>

            <hr class="my-5">

            <div class="row g-4 text-start">
                <div class="col-md-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-calendar-event text-primary" style="font-size: 2rem;"></i>
                            <h5 class="mt-3">Create Events</h5>
                            <p class="text-muted small">Set up events and booths with custom knowledge bases for the AI to reference.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-chat-dots text-primary" style="font-size: 2rem;"></i>
                            <h5 class="mt-3">AI Conversations</h5>
                            <p class="text-muted small">Visitors chat naturally with an AI agent that asks adaptive questions and searches your knowledge base.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-graph-up text-primary" style="font-size: 2rem;"></i>
                            <h5 class="mt-3">Smart Summaries</h5>
                            <p class="text-muted small">Generate AI-powered summaries with sentiment analysis, key themes, demographics, and actionable insights.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
