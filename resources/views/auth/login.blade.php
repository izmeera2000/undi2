@extends('layouts.guest')

@section('title', 'Login')

@section('content')

<x-auth-session-status class="mb-4" :status="session('status')" />

<div class="auth-layout auth-layout-split">
    <!-- Form Side -->
    <div class="auth-form-side">
        <div class="auth-container">

            <!-- Logo -->
            <a href="{{ url('/') }}" class="auth-logo">
                <img src="{{ asset('assets/img/logomain2.png') }}" alt="UndiScope">
                {{-- <span>UndiScope</span> --}}
            </a>

            <div class="auth-card">
                <div class="auth-card-header">
                    <h1 class="auth-title">Sign in to your account</h1>
                    <p class="auth-subtitle">Enter your credentials to access your dashboard</p>
                </div>

                <form class="auth-form" action="{{ route('login') }}" method="POST">
                    @csrf

                    <!-- Email -->
                    <div class="form-group">
                        <label for="email" class="form-label">Email address</label>

                        <input 
                            id="email"
                            type="email"
                            name="email"
                            class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="name@company.com"
                        >

                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <div class="d-flex justify-content-between align-items-center">
                            <label for="password" class="form-label">Password</label>

                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="auth-link small">
                                    Forgot password?
                                </a>
                            @endif
                        </div>

                        <div class="input-group">
                            <input 
                                type="password"
                                id="password"
                                name="password"
                                class="form-control @error('password') is-invalid @enderror"
                                placeholder="Enter your password"
                                required
                                autocomplete="current-password"
                            >
                            <button class="btn btn-outline-secondary" type="button" data-toggle-password>
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>

                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Remember Me -->
                    <div class="form-check">
                        <input 
                            class="form-check-input" 
                            type="checkbox" 
                            id="remember" 
                            name="remember"
                            {{ old('remember') ? 'checked' : '' }}
                        >
                        <label class="form-check-label" for="remember">
                            Remember me
                        </label>
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="btn btn-primary btn-block mt-3">
                        Sign in
                    </button>

 
                </form>

                @if (Route::has('register'))
                    <p class="auth-footer-text">
                        Don't have an account?
                        <a href="{{ route('register') }}" class="auth-link">
                            Create account
                        </a>
                    </p>
                @endif
            </div>

            <!-- Footer -->
       
        @include('layouts.modern_footer_guest')

        </div>
    </div>
<!-- Brand Side -->
<div class="auth-brand-side">
    <div class="auth-brand-content">
        <div class="auth-brand-icon">
           
                <img src=" {{ asset('assets/img/logo.png') }}" alt="UndiScope">

        </div>

        <h2 class="auth-brand-title">Welcome Back</h2>

        <p class="auth-brand-text">
            Sign in to access real-time voter analytics, monitor trends, and make data-driven political decisions.
        </p>

        <div class="auth-brand-features">
            <div class="auth-brand-feature">
                <i class="bi bi-check-circle-fill"></i>
                <span>256-bit SSL Encryption</span>
            </div>
 
          <div class="auth-brand-feature">
                <i class="bi bi-check-circle-fill"></i>
                <span>Real-time Voter Trends</span>
            </div>
            <div class="auth-brand-feature">
                <i class="bi bi-check-circle-fill"></i>
                <span>Political Data Insights</span>
            </div>
        </div>
    </div>
</div>

</div>

@endsection
