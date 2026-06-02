@extends('layouts.app')
@section('title', 'Two-Factor Authentication')

@section('content')
<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-5 col-lg-4">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h4 class="card-title text-center mb-4">Two-Factor Code</h4>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('two-factor.login') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="code" class="form-label">Authentication Code</label>
                            <input type="text" name="code" id="code" class="form-control" inputmode="numeric" autocomplete="one-time-code" required autofocus>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Verify</button>
                    </form>

                    <hr class="my-3">
                    <form method="POST" action="{{ route('two-factor.login') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="recovery_code" class="form-label">Or Use Recovery Code</label>
                            <input type="text" name="recovery_code" id="recovery_code" class="form-control" autocomplete="one-time-code">
                        </div>
                        <button type="submit" class="btn btn-outline-secondary w-100">Use Recovery Code</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
