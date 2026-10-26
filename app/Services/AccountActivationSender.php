<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Exception\UnsupportedSchemeException;

class AccountActivationSender
{
    public function send(User $user): bool
    {
        try {
            return Password::sendResetLink(['email' => $user->email]) === Password::RESET_LINK_SENT;
        } catch (TransportExceptionInterface|UnsupportedSchemeException $exception) {
            Log::error('Account activation email failed to send.', [
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
