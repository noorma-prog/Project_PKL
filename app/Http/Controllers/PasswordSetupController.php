<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class PasswordSetupController extends Controller
{
    public function show(Request $request, string $token)
    {
        $user = User::where('email', $request->query('email'))->first();

        if (! $this->isPendingStaffAccount($user)
            || ! Password::tokenExists($user, $token)) {
            return redirect()->route('login')->with(
                'error',
                'Tautan aktivasi tidak valid atau sudah kedaluwarsa. Hubungi Administrator untuk meminta tautan baru.'
            );
        }

        return view('auth.password-setup', [
            'token' => $token,
            'email' => $user->email,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ], [
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $this->isPendingStaffAccount($user)
            || ! Password::tokenExists($user, $validated['token'])) {
            return back()->withInput($request->only('email'))->with(
                'error',
                'Tautan aktivasi tidak valid atau sudah kedaluwarsa. Hubungi Administrator untuk meminta tautan baru.'
            );
        }

        $status = Password::reset($validated, function (User $user, string $password): void {
            $user->forceFill([
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'requires_email_verification' => false,
                'is_active' => true,
                'remember_token' => Str::random(60),
            ])->save();
        });

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withInput($request->only('email'))->with(
                'error',
                'Tautan aktivasi tidak valid atau sudah kedaluwarsa. Hubungi Administrator untuk meminta tautan baru.'
            );
        }

        return redirect()->route('login')->with(
            'success',
            'Kata sandi berhasil diatur dan akun diaktifkan. Silakan masuk.'
        );
    }

    private function isPendingStaffAccount(?User $user): bool
    {
        return $user !== null
            && in_array($user->role, [User::ROLE_PADAL, User::ROLE_TEKNISI], true)
            && ! $user->is_active
            && $user->requiresEmailVerification()
            && $user->isApprovalActive();
    }
}
