@extends('layouts.app')
@section('title', 'Thank You!')

@section('content')
<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-5 text-center">
            <div class="card shadow-sm">
                <div class="card-body p-5">
                    <i class="bi bi-check-circle text-success" style="font-size: 5rem;"></i>
                    <h3 class="mt-4">Survey Complete!</h3>
                    <p class="text-muted mb-4">Thank you for participating in<br><strong>{{ $event['name'] }}</strong></p>

                    <p class="text-muted small">Your responses have been recorded and will help us improve future events.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
