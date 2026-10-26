<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\EmailVerificationSender;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Exception\UnsupportedSchemeException;
use Symfony\Component\Mailer\Transport\Dsn;
use Tests\TestCase;

class EmailVerificationSenderTest extends TestCase
{
    public function test_transport_failure_is_logged_without_credentials_and_returns_false(): void
    {
        $user = \Mockery::mock(User::class)->makePartial();
        $user->id = 123;
        $user->shouldReceive('sendEmailVerificationNotification')
            ->once()
            ->andThrow(new TransportException('SMTP authentication failed'));

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => 'smtp.gmail.com',
            'mail.mailers.smtp.port' => 587,
        ]);

        Log::shouldReceive('error')
            ->once()
            ->with('Email verification notification failed to send.', \Mockery::on(function (array $context): bool {
                $this->assertSame(123, $context['user_id']);
                $this->assertSame('smtp', $context['mailer']);
                $this->assertSame('smtp.gmail.com', $context['host']);
                $this->assertSame(587, $context['port']);
                $this->assertSame(TransportException::class, $context['exception_class']);
                $this->assertArrayNotHasKey('password', $context);

                return true;
            }));

        $this->assertFalse((new EmailVerificationSender)->send($user));
    }

    public function test_successful_notification_returns_true(): void
    {
        $user = \Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('sendEmailVerificationNotification')->once();

        $this->assertTrue((new EmailVerificationSender)->send($user));
    }

    public function test_unsupported_mailer_scheme_is_logged_and_returns_false(): void
    {
        $user = \Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('sendEmailVerificationNotification')
            ->once()
            ->andThrow(new UnsupportedSchemeException(
                new Dsn('tls', 'smtp.gmail.com', null, null, 587),
                'smtp',
                ['smtp', 'smtps']
            ));

        Log::shouldReceive('error')
            ->once()
            ->with('Email verification notification failed to send.', \Mockery::on(
                fn (array $context): bool => $context['exception_class'] === UnsupportedSchemeException::class
            ));

        $this->assertFalse((new EmailVerificationSender)->send($user));
    }
}
