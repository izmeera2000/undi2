@extends('layouts.guest')

@section('title', 'Reset Password')

@section('content')

    <div class="auth-layout">
        <div class="auth-container">

            <!-- Logo -->
        <a href="{{ url('/') }}" class="auth-logo">
            <img src="{{ asset('assets/img/logomain2.png') }}" alt="ModernAdmin">
         </a>

            <div class="auth-card shadow-sm p-4">

                <!-- Card Header -->
                <div class="text-center mb-4">
                    <div class="auth-icon mb-2">
                        <i class="bi bi-key fs-1 text-primary"></i>
                    </div>
                    <h1 class="auth-title h5 mb-1">Reset Your Password</h1>
                    <p class="auth-subtitle text-muted mb-0">
                        Enter a new password to continue.
                    </p>
                </div>

                <!-- Session Status -->
                <x-auth-session-status class="mb-3" :status="session(key: 'status')" />

                <!-- Reset Form -->
                <form class="auth-form" method="POST" action="{{ route('password.store') }}">
                    @csrf
                    {{-- Hidden Inputs --}}
                    <input type="hidden" name="token" value="{{ $token ?? request()->route('token') }}">
                    <input type="hidden" name="email" value="{{ $email ?? request()->email }}">

                    <!-- New Password -->
                    <div class="form-group mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password"
                            name="password" placeholder="Enter new password" required autofocus>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div class="form-group mb-4">
                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation"
                            placeholder="Confirm new password" required>
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="btn btn-primary w-100">
                        Reset Password
                    </button>
                </form>

                <!-- Footer Link -->
                <p class="auth-footer-text mt-4 text-center">
                    Remember your password?
                    <a href="{{ route('login') }}" class="auth-link fw-medium">
                        Back to sign in
                    </a>
                </p>

            </div>

            <!-- Footer -->
            @include('layouts.modern_footer_guest')

        </div>
    </div>

@endsection