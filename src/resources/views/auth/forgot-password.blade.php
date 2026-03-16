@extends('layouts.auth')

@section('title', 'Forgot Password')
@section('page-subtitle', 'Reset your password')

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-1"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="text-center mb-4">
        <p class="text-muted">Enter your email address and we'll send you an OTP to reset your password.</p>
    </div>

    <form method="POST" action="{{ route('password.forgot') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input 
                type="email" 
                class="form-control @error('email') is-invalid @enderror" 
                id="email" 
                name="email" 
                value="{{ old('email') }}" 
                required 
                autofocus
                placeholder="Enter your email"
            >
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-envelope me-1"></i>
                Send OTP
            </button>
        </div>
    </form>

    <div class="auth-links">
        <a href="{{ route('login') }}">
            <i class="bi bi-arrow-left me-1"></i>Back to Login
        </a>
    </div>
@endsection