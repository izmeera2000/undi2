@extends('layouts.guest')

@section('title', 'Error 404')

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

            <div class="error-wrapper">
                <div class="error-visual">
                    <div class="error-code-display">
                        <span class="error-digit">4</span>
                        <span class="error-digit middle">
                            <i class="bi bi-search"></i>
                        </span>
                        <span class="error-digit">4</span>
                    </div>
                    <div class="error-decoration">
                        <div class="decoration-circle circle-1"></div>
                        <div class="decoration-circle circle-2"></div>
                        <div class="decoration-circle circle-3"></div>
                    </div>
                </div>

                <div class="error-content">
                    <h1 class="error-title">Page not found</h1>
                    <p class="error-description">
                        The page you're looking for doesn't exist or has been moved.
                        Check the URL or navigate back to explore our site.
                    </p>

                  

                    <div class="error-actions">
                        <a href="{{ route('dashboard') }}" class="btn btn-primary">
                            <i class="bi bi-house me-2"></i> Go to Home
                        </a>
                        <a href="{{ redirect()->back()->getTargetUrl() }}" class="btn btn-primary">
                            <i class="bi bi-house me-2"></i> Go Back
                        </a>
                    </div>


                </div>
            </div>
        </div>
    </div>

@endsection