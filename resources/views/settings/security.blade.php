@extends('layouts.app')
@section('title', 'Security Settings')

@section('content')
<div class="container py-4">
    <h1 class="h3 fw-bold mb-4">Security Settings</h1>

    <div class="row">
        <div class="col-lg-6">
            <!-- Update Password -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="card-title mb-4">Update Password</h5>

                    @if ($errors->updatePassword->any())
                        <div class="alert alert-danger">
                            @foreach ($errors->updatePassword->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('user-password.update') }}">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" name="current_password" id="current_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" name="password" id="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm New Password</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Password</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <!-- Two-Factor -->
            @if($canManageTwoFactor ?? false)
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="card-title mb-3">Two-Factor Authentication</h5>

                    @if($twoFactorEnabled ?? false)
                        <div class="alert alert-success">
                            <i class="bi bi-shield-check"></i> Two-factor authentication is enabled.
                        </div>
                        @if($requiresConfirmation ?? false)
                            <p class="text-muted small">Finish enabling two-factor by scanning the QR code from your authenticator app.</p>
                        @endif
                        <form method="POST" action="{{ url('/user/two-factor-authentication') }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger">Disable Two-Factor</button>
                        </form>
                    @else
                        <p class="text-muted small">Add additional security to your account using two-factor authentication.</p>
                        <form method="POST" action="{{ url('/user/two-factor-authentication') }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary">Enable Two-Factor</button>
                        </form>
                    @endif
                </div>
            </div>
            @endif

            <!-- Passkeys -->
            @if($canManagePasskeys ?? false)
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h5 class="card-title mb-3">Passkeys</h5>
                    @if(!empty($passkeys))
                        <ul class="list-group list-group-flush mb-3">
                            @foreach($passkeys as $passkey)
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <span class="fw-medium">{{ $passkey['name'] }}</span>
                                        <br><small class="text-muted">Added {{ $passkey['created_at_diff'] }}</small>
                                    </div>
                                    <form method="POST" action="{{ url('/user/passkeys/' . $passkey['id']) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted small">No passkeys registered yet.</p>
                    @endif
                    <form method="POST" action="{{ url('/user/passkeys') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary">Register Passkey</button>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
