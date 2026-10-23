<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            if (!$user->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->with('error', 'Akun Anda telah dinonaktifkan oleh Administrator. Hubungi Bid TIK.');
            }

            $request->session()->regenerate();

            return $this->redirectBasedOnRole($user)->with('success', 'Selamat datang kembali, ' . $user->name . '!');
        }

        return back()->withErrors([
            'email' => 'Email atau kata sandi yang Anda masukkan salah.',
        ])->onlyInput('email');
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
            'role' => User::ROLE_USER, // Hanya role User yang bisa register mandiri
            'is_active' => true,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('user.dashboard')->with('success', 'Pendaftaran akun LayanIT berhasil! Selamat datang.');
    }

    /**
     * Halaman profil pengguna
     */
    public function showProfile()
    {
        $user = Auth::user();
        return view('profile.show', compact('user'));
    }

    /**
     * Update profil pengguna
     */
    public function updateProfile(Request $request)
    {
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
            default => redirect()->route('user.dashboard'),
        };
    }
}
