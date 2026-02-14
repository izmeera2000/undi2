@extends('layouts.guest')

@section('title', 'Complete Your Profile')

@section('content')

    <div class="auth-layout">
        <div class="auth-container">

            <!-- Logo -->
            <a href="{{ url('/') }}" class="auth-logo">
                <img src="{{ asset('assets/img/logomain2.png') }}" alt="ModernAdmin">
            </a>

            <div class="auth-card">

                <div class="auth-card-header">
                    <div class="auth-icon">
                        <i class="bi bi-person-check"></i>
                    </div>
                    <h1 class="auth-title">Complete Your Login</h1>
                    <p class="auth-subtitle">
                        Welcome! Please insert your password
                    </p>
                </div>

                <!-- Form to complete profile -->
                <form method="POST" action="{{ route('first.login.update') }}"  class="auth-form">
                    @csrf




                    <div class="form-group">
                        <label for="password" class="form-label">Set New Password</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password"
                            name="password" placeholder="Enter your password" required>
                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror"
                            id="password_confirmation" name="password_confirmation" placeholder="Confirm your password"
                            required>
                        @error('password_confirmation')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        Complete Setup
                    </button>
                </form>

           

            </div>

            <!-- Footer -->
            @include('layouts.modern_footer_guest')

        </div>
    </div>

@endsection