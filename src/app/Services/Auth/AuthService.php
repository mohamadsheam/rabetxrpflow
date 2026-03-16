<?php

namespace App\Services\Auth;

use App\Models\OtpLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class AuthService
{
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOCKOUT_DURATION = 900; // 15 minutes

    public function findUserByEmail($email)
    {
        return User::where('email', $email)->first();
    }

    public function checkRateLimit(string $key, string $type): array
    {
        $maxAttempts = match ($type) {
            'login' => self::MAX_LOGIN_ATTEMPTS,
            'forgot_password' => 3,
            'otp_verify' => 5,
            default => 5,
        };

        $decaySeconds = match ($type) {
            'login' => self::LOCKOUT_DURATION,
            'forgot_password' => 600,
            'otp_verify' => 300,
            default => 300,
        };

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            $minutes = ceil($seconds / 60);
            
            return [
                'success' => false,
                'message' => "Too many attempts. Please try again in {$minutes} minutes.",
            ];
        }

        RateLimiter::hit($key, $decaySeconds);

        return ['success' => true];
    }

    public function attemptLogin($request): array
    {
        $user = $this->findUserByEmail($request->email);

        if (!$user) {
            return $this->loginFailedResponse('Invalid email or password.');
        }

        if ($user->locked_until && now()->lessThan($user->locked_until)) {
            $minutes = now()->diffInMinutes($user->locked_until);
            return [
                'success' => false,
                'message' => "Account is locked. Try again in {$minutes} minutes.",
                'redirect' => route('login'),
            ];
        } else {
            if ($user->locked_until) {
                $user->update([
                    'locked_until' => null,
                    'failed_login_attempts' => 0,
                ]);
            }
        }

        if (!Hash::check($request->password, $user->password)) {
            return $this->handleFailedLogin($user);
        }

        if (!$user->hasRole('admin')) {
            return $this->loginFailedResponse('You do not have permission to access this system.');
        }

        Auth::login($user);

        $user->update([
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'last_login_at' => now(),
        ]);

        return [
            'success' => true,
            'message' => 'Login successful!',
            'redirect' => route('dashboard'),
        ];
    }

    private function handleFailedLogin(User $user): array
    {
        $user->increment('failed_login_attempts');

        $attempts = $user->failed_login_attempts;
        $remainingAttempts = self::MAX_LOGIN_ATTEMPTS - $attempts;

        if ($attempts >= self::MAX_LOGIN_ATTEMPTS) {
            $user->update([
                'locked_until' => now()->addSeconds(self::LOCKOUT_DURATION),
            ]);

            RateLimiter::clear('login:' . request()->ip());

            return [
                'success' => false,
                'message' => 'Too many failed attempts. Account locked for 15 minutes.',
                'redirect' => route('login'),
            ];
        }

        $warning = $remainingAttempts <= 2 
            ? " {$remainingAttempts} attempts remaining." 
            : '';

        session()->put('login_attempts_warning', "Incorrect password.{$warning}");

        return [
            'success' => false,
            'message' => 'Invalid email or password.',
        ];
    }

    private function loginFailedResponse(string $message): array
    {
        return [
            'success' => false,
            'message' => $message,
        ];
    }

    public function sendPasswordResetOtp(User $user): array
    {
        $otp = $this->generateOtp();

        OtpLog::create([
            'user_id' => $user->id,
            'otp' => $otp,
            'type' => 'password_reset',
            'expires_at' => now()->addMinutes(config('app.otp_expiry_minutes', 2)),
        ]);

        Session::put('otp_verification_user_id', $user->id);
        Session::put('otp_type', 'password_reset');
        Session::put('password_reset_user_id', $user->id);

        // In production, send OTP via email
        // Mail::to($user)->send(new PasswordResetOtp($otp));

        return [
            'success' => true,
            'message' => 'OTP sent successfully!',
            'otp' => $otp, // Remove in production
        ];
    }

    public function validateOtp(string $otp): array
    {
        $userId = session('otp_verification_user_id');
        $otpType = session('otp_type');

        if (!$userId || !$otpType) {
            return [
                'success' => false,
                'message' => 'Session expired. Please try again.',
                'redirect' => route('login'),
            ];
        }

        $otpLog = OtpLog::where('user_id', $userId)
            ->where('type', $otpType)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$otpLog) {
            return [
                'success' => false,
                'message' => 'Invalid or expired OTP.',
            ];
        }

        if ($otp !== $otpLog->otp) {
            $otpLog->increment('attempts');

            if ($otpLog->attempts >= 3) {
                $otpLog->update(['is_used' => true]);
                session()->forget(['otp_verification_user_id', 'otp_type', 'password_reset_user_id']);

                return [
                    'success' => false,
                    'message' => 'Too many wrong attempts. Please request a new OTP.',
                    'redirect' => route('password.forgot'),
                ];
            }

            $remaining = 3 - $otpLog->attempts;
            return [
                'success' => false,
                'message' => "Incorrect OTP. {$remaining} attempts remaining.",
            ];
        }

        $otpLog->update(['is_used' => true]);

        if ($otpType === 'password_reset') {
            session()->put('password_reset_verified', true);
            session()->forget(['otp_verification_user_id', 'otp_type']);

            return [
                'success' => true,
                'message' => 'OTP verified successfully!',
                'redirect' => route('password.reset.form'),
            ];
        }

        return [
            'success' => true,
            'message' => 'OTP verified successfully!',
            'redirect' => route('dashboard'),
        ];
    }

    public function resendOtp(): array
    {
        $userId = session('otp_verification_user_id');
        $otpType = session('otp_type');

        if (!$userId || !$otpType) {
            return [
                'success' => false,
                'message' => 'Session expired. Please start the process again.',
            ];
        }

        $rateLimitKey = "resend_otp:{$userId}";
        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            return [
                'success' => false,
                'message' => 'Too many resend attempts. Please wait before trying again.',
            ];
        }

        RateLimiter::hit($rateLimitKey, 300);

        OtpLog::where('user_id', $userId)
            ->where('type', $otpType)
            ->where('is_used', false)
            ->update(['is_used' => true]);

        $user = User::find($userId);
        
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found.',
            ];
        }

        return $this->sendPasswordResetOtp($user);
    }

    public function resetPassword(string $password): array
    {
        $userId = session('password_reset_user_id');

        if (!$userId || !session('password_reset_verified')) {
            return [
                'success' => false,
                'message' => 'Session expired. Please start the process again.',
                'redirect' => route('password.forgot'),
            ];
        }

        $user = User::find($userId);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found.',
                'redirect' => route('password.forgot'),
            ];
        }

        $user->update([
            'password' => Hash::make($password),
        ]);

        session()->forget(['password_reset_user_id', 'password_reset_verified']);

        return [
            'success' => true,
            'message' => 'Password reset successfully! Please login with your new password.',
            'redirect' => route('login'),
        ];
    }

    private function generateOtp(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}