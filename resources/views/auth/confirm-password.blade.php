@extends('layouts.app')
@section('title', 'Confirm Password')

@section('content')
<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-5 col-lg-4">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h4 class="card-title text-center mb-4">Confirm Password</h4>
                    <p class="text-muted small mb-3">This is a secure area. Please confirm your password to continue.</p>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.confirm') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" name="password" id="password" class="form-control" required autofocus>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Confirm</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
