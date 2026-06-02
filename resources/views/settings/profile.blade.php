@extends('layouts.app')
@section('title', 'Profile Settings')

@section('content')
<div class="container py-4">
    <h1 class="h3 fw-bold mb-4">Profile Settings</h1>

    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h5 class="card-title mb-4">Update Profile</h5>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('profile.update') }}">
                        @csrf
                        @method('PATCH')
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" name="name" id="name" class="form-control" value="{{ old('name', auth()->user()->name) }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" class="form-control" value="{{ old('email', auth()->user()->email) }}" required>
                            @if(auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !auth()->user()->hasVerifiedEmail())
                                <div class="form-text text-warning">Your email is not verified. Check your inbox.</div>
                            @endif
                        </div>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mt-4 mt-lg-0">
            <div class="card shadow-sm border-danger">
                <div class="card-body p-4">
                    <h5 class="card-title text-danger mb-3">Delete Account</h5>
                    <p class="text-muted small">Once your account is deleted, all of its resources and data will be permanently deleted.</p>
                    <form method="POST" action="{{ route('profile.destroy') }}" onsubmit="return confirm('Are you sure? This cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <div class="mb-3">
                            <label for="delete_password" class="form-label">Enter your password to confirm</label>
                            <input type="password" name="password" id="delete_password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-danger">Delete Account</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
