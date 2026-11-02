<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordSetupController;
use App\Http\Controllers\PadalController;
use App\Http\Controllers\TeknisiController;
use App\Http\Controllers\UserTicketController;
use App\Models\User;
use App\Services\EmailVerificationSender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - LayanIT (Layanan IT POLDA)
| Sesuai ERD & Use Case Diagram
|--------------------------------------------------------------------------
*/

// Halaman Utama: diarahkan ke login
Route::get('/', function () {
    return redirect()->route('login');
});

// Autentikasi Pengguna (Guest) - Use Case: Login & Registrasi Akun
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

Route::post('/email/verification-resend', [AuthController::class, 'resendVerificationEmail'])
    ->middleware('throttle:6,1')
    ->name('verification.resend.public');

Route::get('/password/reset/{token}', [PasswordSetupController::class, 'show'])
    ->name('password.reset');
Route::post('/password/reset', [PasswordSetupController::class, 'update'])
    ->middleware('throttle:6,1')
    ->name('password.update');

Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    $user = User::find($id);

    if (!$user || !hash_equals(sha1($user->getEmailForVerification()), $hash)) {
        return redirect()->route('login')->with(
            'error',
            'Tautan verifikasi tidak valid. Minta tautan baru melalui formulir di halaman ini.'
        );
    }

    $alreadyVerified = $user->hasVerifiedEmail();

    if (!$alreadyVerified) {
        $user->markEmailAsVerified();
    }

    if ($user->requiresEmailVerification()) {
        $user->forceFill(['requires_email_verification' => false])->save();
    }

    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login')->with(
        'success',
        $alreadyVerified
            ? 'Email ini sudah terverifikasi. Silakan masuk menggunakan email dan kata sandi Anda.'
            : 'Email berhasil diverifikasi. Silakan masuk menggunakan email dan kata sandi yang telah Anda daftarkan.'
    );
})->middleware(['signed', 'throttle:6,1'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    /** @var User $user */
    $user = $request->user();

    if ($user->hasVerifiedEmail()
        || (!$user->requiresEmailVerification() && $user->role !== User::ROLE_USER)) {
        return back()->with('error', 'Alamat email akun ini tidak memerlukan verifikasi ulang.');
    }

    if (!app(EmailVerificationSender::class)->send($user)) {
        return back()->with('error', EmailVerificationSender::FAILURE_MESSAGE);
    }

    return back()->with('success', 'Tautan verifikasi baru telah dikirim.');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// Logout & Profil Umum (Semua Role yang Login) - Use Case: Logout
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile', [AuthController::class, 'showProfile'])->name('profile.show');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
});

// -------------------------------------------------------------
// Hak Akses: USER (Pelapor Gangguan IT)
// Use Case: Mengelola Laporan, Melacak Tiket, Memberi Rating
// -------------------------------------------------------------
Route::middleware(['auth', 'role:user'])->prefix('user')->name('user.')->group(function () {
    Route::get('/dashboard', [UserTicketController::class, 'dashboard'])->name('dashboard');
    Route::get('/tickets', [UserTicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/create', [UserTicketController::class, 'create'])->name('tickets.create');
    Route::post('/tickets', [UserTicketController::class, 'store'])->name('tickets.store');
    Route::get('/tickets/{id}', [UserTicketController::class, 'show'])->name('tickets.show');
    Route::post('/tickets/{id}/rate', [UserTicketController::class, 'rate'])->name('tickets.rate');
});

// -------------------------------------------------------------
// Hak Akses: ADMIN (Administrator TIK POLDA)
// Use Case: Mengelola Akun, Mengelola Kategori, Mengelola Padal,
//           Memeriksa Laporan, Menugaskan Padal, Memantau Tiket, Rekap Laporan
// -------------------------------------------------------------
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Tiket, Verifikasi & Penugasan
    Route::get('/tickets', [AdminController::class, 'tickets'])->name('tickets.index');
    Route::get('/tickets/{id}', [AdminController::class, 'showTicket'])->name('tickets.show');
    Route::post('/tickets/{id}/verify-assign', [AdminController::class, 'verifyAndAssign'])->name('tickets.verify-assign');
    Route::post('/tickets/{id}/reassign', [AdminController::class, 'reassignTicket'])->name('tickets.reassign');
    Route::post('/tickets/{id}/reject', [AdminController::class, 'rejectTicket'])->name('tickets.reject');

    // Mengelola Akun (CRUD)
    Route::get('/users', [AdminController::class, 'users'])->name('users.index');
    Route::get('/users/create', [AdminController::class, 'createUser'])->name('users.create');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
    Route::post('/users/{id}/activation-invitation', [AdminController::class, 'sendActivationInvitation'])
        ->name('users.activation-invitation');
    Route::get('/users/{id}', [AdminController::class, 'showUser'])->name('users.show');
    Route::get('/users/{id}/edit', [AdminController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::patch('/users/{id}/approve', [AdminController::class, 'approveUser'])->name('users.approve');
    Route::patch('/users/{id}/reject', [AdminController::class, 'rejectUser'])->name('users.reject');
    Route::patch('/users/{id}/status', [AdminController::class, 'toggleUserStatus'])->name('users.toggle-status');
    Route::delete('/users/{id}', [AdminController::class, 'destroyUser'])->name('users.destroy');


    // Mengelola Padal (CRUD)
    Route::get('/padals', [AdminController::class, 'padals'])->name('padals.index');
    Route::post('/padals', [AdminController::class, 'storePadal'])->name('padals.store');
    Route::put('/padals/{id}', [AdminController::class, 'updatePadal'])->name('padals.update');
    Route::delete('/padals/{id}', [AdminController::class, 'destroyPadal'])->name('padals.destroy');

    // Memantau Tiket: Rating & Ulasan Pelayanan
    Route::get('/ratings', [AdminController::class, 'ratings'])->name('ratings.index');

    // Rekapitulasi Laporan Penanganan
    Route::get('/reports/recap', [AdminController::class, 'reportRecap'])->name('reports.recap');
    Route::get('/reports/recap/preview', [AdminController::class, 'previewReportRecap'])->name('reports.recap.preview');
    Route::get('/reports/recap/export', [AdminController::class, 'exportReportRecap'])->name('reports.recap.export');
});

// -------------------------------------------------------------
// Hak Akses: PADAL (Perwira Pengendali)
// Use Case: Menangani Tiket, Mengelola Anggota
// -------------------------------------------------------------
Route::middleware(['auth', 'role:padal'])->prefix('padal')->name('padal.')->group(function () {
    Route::get('/dashboard', [PadalController::class, 'dashboard'])->name('dashboard');
    Route::get('/tickets', [PadalController::class, 'tickets'])->name('tickets.index');
    Route::get('/tickets/{id}', [PadalController::class, 'showTicket'])->name('tickets.show');
    Route::post('/tickets/{id}/accept', [PadalController::class, 'acceptAssignment'])->name('tickets.accept');
    Route::post('/tickets/{id}/reject', [PadalController::class, 'rejectAssignment'])->name('tickets.reject');
    Route::post('/tickets/{id}/technicians', [PadalController::class, 'updateTicketTechnicians'])->name('tickets.technicians');
    Route::post('/tickets/{id}/history-note', [PadalController::class, 'addHistoryNote'])->name('tickets.history-note');
    Route::post('/tickets/{id}/complete', [PadalController::class, 'completeTicket'])->name('tickets.complete');
    
    // Mengelola Anggota (Lihat Anggota, Tambah Anggota, Hapus Anggota)
    Route::get('/members', [PadalController::class, 'members'])->name('members.index');
    Route::post('/members', [PadalController::class, 'addMember'])->name('members.store');
    Route::delete('/members/{userId}', [PadalController::class, 'removeMember'])->name('members.destroy');

    Route::get('/ratings', [PadalController::class, 'ratings'])->name('ratings.index');
});

// -------------------------------------------------------------
// Hak Akses: TEKNISI (Read-Only)
// Use Case: Melihat Detail Tiket
// -------------------------------------------------------------
Route::middleware(['auth', 'role:teknisi'])->prefix('teknisi')->name('teknisi.')->group(function () {
    Route::get('/dashboard', [TeknisiController::class, 'dashboard'])->name('dashboard');
    Route::get('/tickets', [TeknisiController::class, 'tickets'])->name('tickets.index');
    Route::get('/tickets/{id}', [TeknisiController::class, 'showTicket'])->name('tickets.show');
});
