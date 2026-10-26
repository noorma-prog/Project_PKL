<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Exception\UnsupportedSchemeException;

class EmailVerificationSender
{
    public const FAILURE_MESSAGE = 'Email verifikasi belum dapat dikirim. Silakan coba lagi beberapa saat.';

    public function send(User $user): bool
    {
        try {
            $user->sendEmailVerificationNotification();

            return true;
        } catch (TransportExceptionInterface|UnsupportedSchemeException $exception) {
            Log::error('Email verification notification failed to send.', [
                'user_id' => $user->getKey(),
                'mailer' => config('mail.default'),
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
                'exception_class' => get_class($exception),
                'exception_code' => $exception->getCode(),
            ]);

            return false;
        }
    }
}
