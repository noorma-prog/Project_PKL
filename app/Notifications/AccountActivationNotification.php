<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class AccountActivationNotification extends ResetPassword
{
    protected function buildMailMessage($url): MailMessage
    {
        return (new MailMessage)
            ->subject('Aktivasi Akun LayanIT')
            ->greeting('Halo!')
            ->line('Akun LayanIT Anda telah dibuat oleh Administrator.')
            ->line('Gunakan tautan di bawah ini untuk mengatur kata sandi dan mengaktifkan akun Anda.')
            ->action('Atur Kata Sandi', $url)
            ->line('Tautan ini berlaku selama '.config('auth.passwords.'.config('auth.defaults.passwords').'.expire').' menit dan hanya dapat digunakan satu kali.')
            ->line('Jika Anda tidak mengenali undangan ini, abaikan email ini.');
    }
}
