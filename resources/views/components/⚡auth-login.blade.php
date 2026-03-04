<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

new  class extends Component {
    
    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required')]
    public string $password = '';

    public bool $remember = false;
    public bool $showPassword = false;

    /**
     * Handle the authentication attempt.
     */
    public function login()
    {
        $this->validate();

        // Standard Laravel Auth Attempt
        if (!Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        // Standard Laravel Session Management
        session()->regenerate();

        // Standard Laravel Redirect (with Livewire navigate enabled)
        return $this->redirectIntended(
            default: route('dashboard', absolute: false), 
            navigate: true
        );
    }

    public function togglePassword()
    {
        $this->showPassword = !$this->showPassword;
    }
}; ?>

<div>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="auth-layout auth-layout-split">
        <div class="auth-form-side">
            <div class="auth-container">

                <a href="{{ url('/') }}" class="auth-logo" wire:navigate>
                    <img src="{{ asset('assets/img/logomain2.png') }}" alt="UndiScope">
                </a>

                <div class="auth-card">
                    <div class="auth-card-header">
                        <h1 class="auth-title">Sign in to your account</h1>
                        <p class="auth-subtitle">Enter your credentials to access your dashboard</p>
                    </div>

                    <form class="auth-form" wire:submit="login">
                        <div class="form-group mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input 
                                wire:model="email"
                                id="email"
                                type="email"
                                class="form-control @error('email') is-invalid @enderror"
                                placeholder="name@company.com"
                                required
                                autofocus
                            >
                            @error('email') <div class="invalid-feedback d-block mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label for="password" class="form-label">Password</label>
                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}" class="auth-link small" wire:navigate>
                                        Forgot password?
                                    </a>
                                @endif
                            </div>

                            <div class="input-group">
                                <input 
                                    wire:model="password"
                                    type="{{ $showPassword ? 'text' : 'password' }}"
                                    id="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    placeholder="Enter your password"
                                    required
                                >
                                <button class="btn btn-outline-secondary" type="button" wire:click="togglePassword">
                                    <i class="bi {{ $showPassword ? 'bi-eye-slash' : 'bi-eye' }}"></i>
                                </button>
                            </div>
                            @error('password') <div class="invalid-feedback d-block mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-check mb-3">
                            <input wire:model="remember" class="form-check-input" type="checkbox" id="remember">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block w-100 mt-3" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="login">Sign in</span>
                            <span wire:loading wire:target="login">
                                <span class="spinner-border spinner-border-sm me-2"></span> Authenticating...
                            </span>
                        </button>
                    </form>
                </div>

                @include('layouts.modern_footer_guest')
            </div>
        </div>

        <div class="auth-brand-side">
            <div class="auth-brand-content">
                <div class="auth-brand-icon">
                    <img src="{{ asset('assets/img/logo.png') }}" alt="UndiScope">
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
</div>