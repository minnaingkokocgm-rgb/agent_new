@extends('layouts.app')
@section('title', 'Forgot Password')

@section('content')
<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-5 col-lg-4">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h4 class="card-title text-center mb-4">Reset Password</h4>
                    <p class="text-muted small mb-3">Enter your email and we'll send you a reset link.</p>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required autofocus>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
                    </form>
                </div>
            </div>
            <p class="text-center text-muted mt-3"><a href="{{ route('login') }}">Back to login</a></p>
        </div>
    </div>
</div>
@endsection
