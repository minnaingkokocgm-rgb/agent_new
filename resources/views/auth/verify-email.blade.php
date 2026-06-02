@extends('layouts.app')
@section('title', 'Verify Email')

@section('content')
<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-5 col-lg-4">
            <div class="card shadow-sm">
                <div class="card-body p-4 text-center">
                    <i class="bi bi-envelope-check text-primary" style="font-size: 3rem;"></i>
                    <h4 class="mt-3">Verify Your Email</h4>
                    <p class="text-muted">A verification link has been sent to your email address. Please check your inbox.</p>

                    <form method="POST" action="{{ route('verification.send') }}" class="mt-3">
                        @csrf
                        <button type="submit" class="btn btn-primary">Resend Verification Email</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
