<?php

namespace App\Services\Auth;

use App\Models\OtpLog;
use Illuminate\Support\Str;

class OtpService
{
    private const OTP_LENGTH = 6;
    private const OTP_EXPIRY_MINUTES = 2;

    public function findActiveOtp($userId, $otpType)
    {
        return OtpLog::where('user_id', $userId)
            ->where('type', $otpType)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->first();
    }

    public function createOtp(int $userId, string $type): array
    {
        $otp = $this->generateOtp();

        $otpLog = OtpLog::create([
            'user_id' => $userId,
            'otp' => $otp,
            'type' => $type,
            'expires_at' => now()->addMinutes(self::OTP_EXPIRY_MINUTES),
            'is_used' => false,
            'attempts' => 0,
        ]);

        return [
            'otp' => $otp,
            'expires_at' => $otpLog->expires_at,
            'otp_log_id' => $otpLog->id,
        ];
    }

    public function verifyOtp(int $userId, string $otp, string $type): array
    {
        $otpLog = $this->findActiveOtp($userId, $type);

        if (!$otpLog) {
            return [
                'success' => false,
                'message' => 'No active OTP found or OTP has expired.',
            ];
        }

        if ($otpLog->attempts >= 3) {
            $otpLog->update(['is_used' => true]);
            return [
                'success' => false,
                'message' => 'Too many attempts. Please request a new OTP.',
            ];
        }

        if ($otpLog->otp !== $otp) {
            $otpLog->increment('attempts');
            $remaining = 3 - $otpLog->attempts;
            return [
                'success' => false,
                'message' => "Invalid OTP. {$remaining} attempts remaining.",
            ];
        }

        $otpLog->update(['is_used' => true]);

        return [
            'success' => true,
            'message' => 'OTP verified successfully!',
        ];
    }

    public function invalidateOldOtps(int $userId, string $type): void
    {
        OtpLog::where('user_id', $userId)
            ->where('type', $type)
            ->where('is_used', false)
            ->update(['is_used' => true]);
    }

    private function generateOtp(): string
    {
        return Str::random(self::OTP_LENGTH, 'numeric');
    }
}