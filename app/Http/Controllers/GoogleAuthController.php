<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class GoogleAuthController extends Controller
{
    public function redirectToProvider(): RedirectResponse
    {
        if (! $this->isConfigured()) {
            return redirect()->route('login')->with(
                'error',
                'Login Google belum dikonfigurasi. Hubungi Administrator.'
            );
        }

        return Socialite::driver('google')->redirect();
    }

    public function handleProviderCallback(Request $request): RedirectResponse
    {
        if ($request->filled('error')) {
            return redirect()->route('login')->with('error', 'Login dengan Google dibatalkan.');
        }

        if (! $this->isConfigured()) {
            return redirect()->route('login')->with(
                'error',
                'Login Google belum dikonfigurasi. Hubungi Administrator.'
            );
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (InvalidStateException) {
            return redirect()->route('login')->with(
                'error',
                'Sesi login Google tidak valid atau telah kedaluwarsa. Silakan coba kembali.'
            );
        }

        $googleEmail = strtolower(trim((string) $googleUser->getEmail()));
        $googleIdentity = $googleUser->getRaw();
        $googleEmailVerified = ($googleIdentity['email_verified'] ?? $googleIdentity['verified_email'] ?? false) === true;

        if (! $googleEmailVerified || ! filter_var($googleEmail, FILTER_VALIDATE_EMAIL)) {
            return $this->denyLogin();
        }

        $matchingUsers = User::query()
            ->whereRaw('LOWER(email) = ?', [$googleEmail])
            ->limit(2)
            ->get();

        if ($matchingUsers->isEmpty()) {
            $request->session()->put('google_registration', [
                'email' => $googleEmail,
                'name' => $googleUser->getName(),
                'expires_at' => now()->addMinutes(15)->timestamp,
            ]);

            return redirect()->route('register')->with(
                'google_registration_notice',
                'Email Google belum terdaftar. Lengkapi formulir untuk mendaftar sebagai User/Pelapor.'
            );
        }

        if ($matchingUsers->count() !== 1) {
            return $this->denyLogin();
        }

        $user = $matchingUsers->first();

        if (! $user || ! in_array($user->role, [
            User::ROLE_ADMIN,
            User::ROLE_PADAL,
            User::ROLE_TEKNISI,
            User::ROLE_USER,
        ], true)) {
            return $this->denyLogin();
        }

        if (! $user->is_active) {
            return $this->denyLogin();
        }

        if ($user->requiresEmailVerification() && ! $user->hasVerifiedEmail()) {
            return redirect()->route('login')->with(
                'error',
                'Akun LayanIT Anda belum menyelesaikan verifikasi email.'
            );
        }

        if (in_array($user->role, [User::ROLE_PADAL, User::ROLE_TEKNISI], true)
            && ! $user->isApprovalActive()) {
            return redirect()->route('login')->with(
                'error',
                $user->isApprovalRejected()
                    ? 'Pendaftaran akun Anda ditolak oleh Administrator.'
                    : 'Pendaftaran akun Anda masih menunggu persetujuan Administrator.'
            );
        }

        Auth::login($user);
        $request->session()->regenerate();

        return match ($user->role) {
            User::ROLE_ADMIN => redirect()->route('admin.dashboard'),
            User::ROLE_PADAL => redirect()->route('padal.dashboard'),
            User::ROLE_TEKNISI => redirect()->route('teknisi.dashboard'),
            User::ROLE_USER => redirect()->route('user.dashboard'),
        };
    }

    private function isConfigured(): bool
    {
        return filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'))
            && filled(config('services.google.redirect'));
    }

    private function denyLogin(): RedirectResponse
    {
        return redirect()->route('login')->with(
            'error',
            'Akun Google ini belum terdaftar atau belum memiliki akses ke LayanIT.'
        );
    }
}
