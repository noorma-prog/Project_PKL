<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\EmailVerificationSender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Tampilkan form login
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.login');
    }

    /**
     * Proses login
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'account_type' => ['required', 'string', 'in:user,admin,padal,teknisi'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'account_type.required' => 'Pilih jenis akun terlebih dahulu.',
            'account_type.in' => 'Jenis akun tidak valid.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $credentials = [
            'email' => $validated['email'],
            'password' => $validated['password'],
        ];

        $throttleKey = strtolower((string) $validated['email']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'email' => 'Terlalu banyak percobaan login. Silakan coba lagi dalam '.$seconds.' detik.',
            ]);
        }

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            RateLimiter::clear($throttleKey);
            /** @var User|null $user */
            $user = Auth::user();

            if (!$user || !$user->is_active) {
                $activationPending = $user
                    && in_array($user->role, [User::ROLE_PADAL, User::ROLE_TEKNISI], true)
                    && !$user->is_active
                    && $user->requiresEmailVerification();

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => $activationPending
                        ? 'Akun belum diaktifkan. Selesaikan pengaturan kata sandi melalui undangan email dari Administrator.'
                        : 'Email atau kata sandi yang Anda masukkan salah.',
                ]);
            }

            if ($user->requiresEmailVerification() && !$user->hasVerifiedEmail()) {
                $request->session()->regenerate();

                return redirect()->route('verification.notice')->with(
                    'error',
                    'Verifikasi alamat email Anda terlebih dahulu melalui tautan yang dikirimkan.'
                );
            }

            if (in_array($user->role, [User::ROLE_PADAL, User::ROLE_TEKNISI], true)
                && !$user->isApprovalActive()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $message = $user->isApprovalRejected()
                    ? 'Pendaftaran akun Anda ditolak oleh Administrator.'
                    : 'Pendaftaran akun Anda masih menunggu persetujuan Administrator.';

                return back()->withErrors(['email' => $message]);
            }

            if ($validated['account_type'] !== $user->role) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'account_type' => 'Jenis akun yang dipilih tidak sesuai dengan akun yang terdaftar.',
                ]);
            }

            $request->session()->regenerate();

            return $this->redirectBasedOnRole($user)->with('success', 'Selamat datang kembali, ' . $user->name . '!');
        }

        RateLimiter::hit($throttleKey, 60);

        return back()->withErrors([
            'email' => 'Email atau kata sandi yang Anda masukkan salah.',
        ]);
    }

    /**
     * Tampilkan form registrasi pelapor (User)
     */
    public function showRegisterForm()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.register');
    }

    /**
     * Proses registrasi akun pelapor baru (Role User)
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'role' => ['prohibited'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'nrp_nip' => ['required', 'string', 'max:50'],
            'satuan_kerja' => ['required', 'string', 'max:100'],
            'phone_number' => ['required', 'string', 'max:20'],
            'alamat' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Password::min(6)],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.unique' => 'Email tersebut sudah terdaftar.',
            'nrp_nip.required' => 'NRP atau NIP wajib diisi.',
            'satuan_kerja.required' => 'Satuan Kerja / Bagian wajib diisi.',
            'phone_number.required' => 'Nomor telepon/WhatsApp aktif wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'nrp_nip' => $validated['nrp_nip'],
            'satuan_kerja' => $validated['satuan_kerja'],
            'phone_number' => $validated['phone_number'],
            'alamat' => $validated['alamat'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => User::ROLE_USER,
            'is_active' => true,
            'approval_status' => User::APPROVAL_ACTIVE,
            'requires_email_verification' => true,
        ]);

        $verificationSent = app(EmailVerificationSender::class)->send($user);
        Auth::login($user);
        $request->session()->regenerate();

        if (!$verificationSent) {
            return redirect()->route('verification.notice')->with(
                'error',
                'Pendaftaran berhasil, tetapi email verifikasi belum dapat dikirim. Silakan coba kirim ulang beberapa saat lagi.'
            );
        }

        return redirect()->route('verification.notice')->with(
            'success',
            'Pendaftaran berhasil. Verifikasi email melalui tautan yang dikirim sebelum menggunakan akun.'
        );
    }

    /**
     * Halaman profil pengguna
     */
    public function showProfile()
    {
        /** @var User $user */
        $user = Auth::user();
        return view('profile.show', compact('user'));
    }

    /**
     * Update profil pengguna
     */
    public function updateProfile(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'alamat' => ['nullable', 'string', 'max:255'],
            'satuan_kerja' => ['nullable', 'string', 'max:100'],
            'nrp_nip' => ['nullable', 'string', 'max:50'],
            'current_password' => ['nullable', 'required_with:new_password'],
            'new_password' => ['nullable', Password::min(6), 'confirmed'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'current_password.required_with' => 'Masukkan kata sandi lama untuk mengubah kata sandi baru.',
            'new_password.min' => 'Kata sandi baru minimal 6 karakter.',
            'new_password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        // Cek ganti password jika diisi
        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Kata sandi lama yang Anda masukkan salah.']);
            }
            $user->password = Hash::make($validated['new_password']);
        }

        $user->name = $validated['name'];
        $user->phone_number = $validated['phone_number'] ?? $user->phone_number;
        $user->alamat = $validated['alamat'] ?? $user->alamat;
        $user->satuan_kerja = $validated['satuan_kerja'] ?? $user->satuan_kerja;
        $user->nrp_nip = $validated['nrp_nip'] ?? $user->nrp_nip;
        $user->save();

        return back()->with('success', 'Profil Anda berhasil diperbarui.');
    }

    /**
     * Logout pengguna
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar sistem.');
    }

    /**
     * Helper pengalihan halaman berdasarkan role
     */
    private function redirectBasedOnRole(User $user)
    {
        return match ($user->role) {
            User::ROLE_ADMIN => redirect()->route('admin.dashboard'),
            User::ROLE_PADAL => redirect()->route('padal.dashboard'),
            User::ROLE_TEKNISI => redirect()->route('teknisi.dashboard'),
            User::ROLE_USER => redirect()->route('user.dashboard'),
            default => redirect()->route('login')->withErrors([
                'account_type' => 'Role akun tidak dikenali. Hubungi Administrator.',
            ]),
        };
    }
}
