@extends('layouts.auth')

@section('title', 'Verify OTP')
@section('page-subtitle', 'Enter the code sent to your email')

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
        <p class="text-muted">We've sent a 6-digit OTP to</p>
        <strong>{{ $email }}</strong>
    </div>

    <form method="POST" action="{{ route('otp.verify') }}">
        @csrf

        <div class="mb-3">
            <label for="otp" class="form-label">Enter OTP</label>
            <input 
                type="text" 
                class="form-control otp-input @error('otp') is-invalid @enderror" 
                id="otp" 
                name="otp" 
                value="{{ old('otp') }}"
                maxlength="6" 
                required
                autocomplete="off"
                placeholder="000000"
            >
            @error('otp')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="countdown-timer" id="countdown">
            Resend available in <span id="timer">2:00</span>
        </div>

        <div class="resend-link">
            <a href="#" id="resendBtn" class="disabled" onclick="return false;">
                <i class="bi bi-arrow-repeat me-1"></i>Resend OTP
            </a>
        </div>

        <div class="d-grid mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg me-1"></i>
                Verify OTP
            </button>
        </div>
    </form>

    <div class="auth-links">
        <a href="{{ route('login') }}">
            <i class="bi bi-arrow-left me-1"></i>Back to Login
        </a>
    </div>
@endsection

@push('scripts')
<script>
    const RESEND_COOLDOWN = 120;
    
    function startCountdown() {
        let timeLeft = RESEND_COOLDOWN;
        const timerElement = document.getElementById('timer');
        const resendBtn = document.getElementById('resendBtn');
        
        const interval = setInterval(() => {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            
            timerElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
            
            if (timeLeft <= 0) {
                clearInterval(interval);
                resendBtn.classList.remove('disabled');
                resendBtn.onclick = resendOtp;
                document.getElementById('countdown').classList.add('d-none');
            }
            
            timeLeft--;
        }, 1000);
    }

    function resendOtp() {
        fetch('{{ route("otp.resend") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                alert(data.message);
                startCountdown();
                document.getElementById('countdown').classList.remove('d-none');
                document.getElementById('resendBtn').classList.add('disabled');
                document.getElementById('resendBtn').onclick = null;
            }
        })
        .catch(error => {
            if (error.json) {
                error.json().then(data => {
                    alert(data.error || 'Failed to resend OTP');
                });
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const otpInput = document.getElementById('otp');
        otpInput.focus();
        
        otpInput.addEventListener('input', (e) => {
            e.target.value = e.target.value.replace(/[^0-9]/g, '');
        });

        startCountdown();
    });
</script>
@endpush