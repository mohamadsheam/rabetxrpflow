@extends('layouts.auth')

@section('title', 'Reset Password')
@section('page-subtitle', 'Create a new password')

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
        <p class="text-muted">Create a new password for your account.</p>
    </div>

    <form method="POST" action="{{ route('password.reset') }}">
        @csrf

        <div class="mb-3">
            <label for="password" class="form-label">New Password</label>
            <input 
                type="password" 
                class="form-control @error('password') is-invalid @enderror" 
                id="password" 
                name="password" 
                required
                placeholder="Enter new password"
            >
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirm Password</label>
            <input 
                type="password" 
                class="form-control @error('password_confirmation') is-invalid @enderror" 
                id="password_confirmation" 
                name="password_confirmation" 
                required
                placeholder="Confirm new password"
            >
            @error('password_confirmation')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg me-1"></i>
                Reset Password
            </button>
        </div>
    </form>

    <div class="auth-links">
        <a href="{{ route('login') }}">
            <i class="bi bi-arrow-left me-1"></i>Back to Login
        </a>
    </div>
@endsection