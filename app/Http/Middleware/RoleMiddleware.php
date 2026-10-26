<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): Response  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        /** @var User $user */
        $user = Auth::user();

        // Periksa apakah akun aktif
        if (!$user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'Akun Anda telah dinonaktifkan oleh Administrator.');
        }

        if (($user->role === User::ROLE_USER || $user->requiresEmailVerification())
            && !$user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice')
                ->with('error', 'Verifikasi alamat email Anda terlebih dahulu.');
        }

        if (in_array($user->role, ['padal', 'teknisi'], true)
            && !$user->isApprovalActive()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $message = $user->isApprovalRejected()
                ? 'Pendaftaran akun Anda ditolak oleh Administrator.'
                : 'Pendaftaran akun Anda masih menunggu persetujuan Administrator.';

            return redirect()->route('login')->with('error', $message);
        }

        // Jika tidak ada batasan role spesifik atau role pengguna cocok dengan salah satu role yang diizinkan
        if (empty($roles) || in_array($user->role, $roles, true)) {
            /** @var Response $response */
            $response = $next($request);
            return $response;
        }

        // Jika tidak berhak mengakses
        return response('Akses ditolak. Anda tidak memiliki izin untuk mengakses halaman ini.', 403);
    }
}
