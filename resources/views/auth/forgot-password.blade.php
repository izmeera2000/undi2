@extends('layouts.guest')

@section('title', 'Forgot Password')

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
                    <i class="bi bi-key"></i>
                </div>
                <h1 class="auth-title">Forgot your password?</h1>
                <p class="auth-subtitle">
                    No worries! Enter your email and we'll send you reset instructions.
                </p>
            </div>

  

            <form class="auth-form" method="POST" action="{{ route('password.email') }}">
                @csrf

                <div class="form-group">
                    <label for="email" class="form-label">Email address</label>

                    <input 
                        type="email"
                        class="form-control @error('email') is-invalid @enderror"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="name@company.com"
                        required
                        autofocus
                    >

                    {{-- <x-input-error :messages="$errors->get('email')" class="mt-2" /> --}}
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    Send reset link
                </button>
            </form>

            <p class="auth-footer-text">
                Remember your password?
                <a href="{{ route('login') }}" class="auth-link">
                    Back to sign in
                </a>
            </p>

        </div>

        <!-- Footer -->
        @include('layouts.modern_footer_guest')


    </div>
</div>

@endsection
