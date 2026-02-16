@extends('layouts.guest')

@section('title', 'Error 403')

@section('content')
    <div class="error-page">
        <div class="error-wrapper">
            <div class="error-visual">
                <div class="error-shield">
                    <div class="shield-icon">
                        <i class="bi bi-shield-lock"></i>
                    </div>
                    <div class="shield-ring ring-1"></div>
                    <div class="shield-ring ring-2"></div>
                </div>
            </div>

            <div class="error-content">
                <span class="error-badge warning">
                    <i class="bi bi-exclamation-triangle me-1"></i> Access Denied
                </span>
                <h1 class="error-title">You don't have permission</h1>
                <p class="error-description">
                    You don't have the required permissions to access this page.
                    This might be due to insufficient privileges or an expired session.
                </p>

                <div class="error-info-card">
                    <div class="error-info-icon">
                        <i class="bi bi-info-circle"></i>
                    </div>
                    <div class="error-info-content">
                        <h6>What you can do:</h6>
                        <ul>
                            <li>Check if you're logged in with the correct account</li>
                            <li>Contact your administrator for access</li>
                            <li>Return to the homepage and try again</li>
                        </ul>
                    </div>
                </div>

                <div class="error-actions">
                    <a href="{{ redirect()->back()->getTargetUrl() }}" class="btn btn-primary">
                        <i class="bi bi-house me-2"></i> Go Back
                    </a>

                    <a href="{{ route(name: 'login') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-box-arrow-in-right me-2"></i> Sign In
                    </a>

                </div>

                {{-- <div class="error-contact">
                    <p>Need help? <a href="contact.html">Contact Support</a></p>
                </div> --}}
            </div>
        </div>
    </div>

@endsection