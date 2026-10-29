<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Padal;
use App\Models\Ticket;
use App\Models\TicketRating;
use App\Models\TicketStatusHistory;
use App\Models\TicketTechnicianAssignment;
use App\Models\User;
use App\Services\AccountActivationSender;
use App\Services\EmailVerificationSender;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    private function availablePadals(): Builder
    {
        return Padal::query()
            ->where('status', 'aktif')
            ->whereHas('leader', function (Builder $query) {
                $query->where('role', User::ROLE_PADAL)
                    ->where('is_active', true)
                    ->where('approval_status', User::APPROVAL_ACTIVE)
                    ->where(function (Builder $query) {
                        $query->where('requires_email_verification', false)
                            ->orWhereNotNull('email_verified_at');
                    });
            });
    }

    /**
     * Dashboard Administrator
     */
    public function dashboard()
    {
        $completedStatuses = [
            Ticket::STATUS_SELESAI,
            Ticket::STATUS_SELESAI_DINILAI,
        ];

        $stats = [
            'total_tickets' => Ticket::count(),
            'menunggu_verifikasi' => Ticket::where('status', Ticket::STATUS_MENUNGGU_VERIFIKASI)->count(),
            'menunggu_konfirmasi_padal' => Ticket::where('status', Ticket::STATUS_MENUNGGU_KONFIRMASI_PADAL)->count(),
            'dalam_penanganan' => Ticket::where('status', Ticket::STATUS_DALAM_PENANGANAN)->count(),
            'selesai' => Ticket::whereIn('status', $completedStatuses)->count(),
            'ditolak' => Ticket::whereIn('status', [Ticket::STATUS_DITOLAK_ADMIN, Ticket::STATUS_DITOLAK_PADAL])->count(),
            'total_users' => User::where('role', User::ROLE_USER)->count(),
            'total_padal' => Padal::count(),
            'total_teknisi' => User::where('role', User::ROLE_TEKNISI)->count(),
            'avg_rating' => number_format((float) TicketRating::avg('rating'), 1, '.', ''),
        ];

        // Laporan masuk yang membutuhkan verifikasi segera
        $pendingVerificationCount = $stats['menunggu_verifikasi'];
        $pendingVerificationTickets = Ticket::with(['user', 'category'])
            ->where('status', Ticket::STATUS_MENUNGGU_VERIFIKASI)
            ->latest()
            ->take(5)
            ->get();

        // Laporan terbaru
        $recentTickets = Ticket::with(['user', 'category', 'padal.leader', 'technicians'])
            ->latest()
            ->take(8)
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'pendingVerificationCount',
            'pendingVerificationTickets',
            'recentTickets'
        ));
    }

    /**
     * Daftar seluruh tiket (Use Case: Memantau Tiket -> Lihat Daftar Tiket)
     */
    public function tickets(Request $request)
    {
        $query = Ticket::with(['user', 'category', 'padal.leader', 'technicians', 'rating']);

        if ($request->input('status') === 'selesai_semua') {
            $query->whereIn('status', [Ticket::STATUS_SELESAI, Ticket::STATUS_SELESAI_DINILAI]);
        } elseif ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('padal_id')) {
            $query->where('padal_id', $request->padal_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('nrp_nip', 'like', "%{$search}%");
                  });
            });
        }

        $tickets = $query->latest()->paginate(10)->withQueryString();
        $categories = Category::where('is_active', true)->get();
        $padals = $this->availablePadals()->with('leader')->get();

        return view('admin.tickets.index', compact('tickets', 'categories', 'padals'));
    }

    /**
     * Detail tiket untuk Admin (Use Case: Memeriksa Laporan -> Lihat Detail Laporan & Lihat Riwayat Status)
     */
    public function showTicket(int $id)
    {
        $ticket = Ticket::with([
            'user', 
            'category', 
            'diperiksaOleh', 
            'ditugaskanOleh', 
            'padal.leader', 
            'padal.members', 
            'technicians',
            'rating', 
            'histories.user'
        ])->findOrFail($id);

        $padals = $this->availablePadals()->with('leader')->get();

        return view('admin.tickets.show', compact('ticket', 'padals'));
    }

    /**
     * Konfirmasi Laporan & Pilih Padal (Use Case: Memeriksa Laporan -> Konfirmasi Laporan & Menugaskan Padal -> Pilih Padal)
     */
    public function verifyAndAssign(Request $request, int $id)
    {
        $validated = $request->validate([
            'padal_id' => ['required', 'exists:padals,id'],
            'admin_notes' => ['nullable', 'string', 'max:500'],
        ], [
            'padal_id.required' => 'Pilih unit Perwira Pengendali (Padal) yang akan ditugaskan.',
            'padal_id.exists' => 'Data Padal tidak valid.',
        ]);

        return DB::transaction(function () use ($id, $validated) {
            $ticket = Ticket::whereKey($id)->lockForUpdate()->firstOrFail();
            if (!in_array($ticket->status, [Ticket::STATUS_MENUNGGU_VERIFIKASI, Ticket::STATUS_DITOLAK_PADAL], true)) {
                return back()->with('error', 'Status tiket saat ini tidak memungkinkan untuk verifikasi/penugasan.');
            }

            $padal = $this->availablePadals()
                ->with('leader')
                ->find($validated['padal_id']);

            if (!$padal) {
                throw ValidationException::withMessages([
                    'padal_id' => 'Pilih unit Padal aktif yang terhubung dengan akun Padal aktif dan disetujui.',
                ]);
            }

            $oldStatus = $ticket->status;

            $ticket->status = Ticket::STATUS_MENUNGGU_KONFIRMASI_PADAL;
            $ticket->diperiksa_oleh = Auth::id(); // USER memeriksa(laporan) TIKET
            $ticket->ditugaskan_oleh = Auth::id(); // USER menugaskan(padal) TIKET
            $ticket->padal_id = $padal->id;
            $ticket->admin_notes = $validated['admin_notes'] ?? null;
            $ticket->verified_at = now();
            $ticket->assigned_at = now();
            $ticket->rejection_reason = null; // Reset alasan jika sebelumnya ditolak padal
            $ticket->save();

            TicketStatusHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => Auth::id(),
                'status_from' => $oldStatus,
                'status_to' => Ticket::STATUS_MENUNGGU_KONFIRMASI_PADAL,
                'notes' => 'Laporan dikonfirmasi oleh Administrator (' . Auth::user()->name . ') dan ditugaskan kepada ' . $padal->nama_padal . ' (Ketua: ' . ($padal->leader ? $padal->leader->name : '-') . ').' . ($ticket->admin_notes ? ' Catatan: ' . $ticket->admin_notes : ''),
            ]);

            return redirect()->route('admin.tickets.show', $ticket->id)
                ->with('success', 'Laporan berhasil dikonfirmasi dan penugasan telah diteruskan ke ' . $padal->nama_padal . '.');
        });
    }

    /**
     * Ubah Penugasan Padal (Use Case: Menugaskan Padal -> Ubah Penugasan)
     */
    public function reassignTicket(Request $request, int $id)
    {
        $validated = $request->validate([
            'padal_id' => ['required', 'exists:padals,id'],
            'admin_notes' => ['nullable', 'string', 'max:500'],
        ], [
            'padal_id.required' => 'Pilih unit Padal baru yang akan ditugaskan.',
        ]);

        return DB::transaction(function () use ($id, $validated) {
            $ticket = Ticket::with('padal')->whereKey($id)->lockForUpdate()->firstOrFail();
            $padal = $this->availablePadals()
                ->with('leader')
                ->find($validated['padal_id']);

            if (!$padal) {
                throw ValidationException::withMessages([
                    'padal_id' => 'Pilih unit Padal aktif yang terhubung dengan akun Padal aktif dan disetujui.',
                ]);
            }

            $oldStatus = $ticket->status;
            $oldPadalName = $ticket->padal ? $ticket->padal->nama_padal : 'Padal Sebelumnya';

            if ((int) $ticket->padal_id !== (int) $padal->id) {
                TicketTechnicianAssignment::where('ticket_id', $ticket->id)
                    ->whereNull('released_at')
                    ->update(['released_at' => now()]);
            }

            $ticket->status = Ticket::STATUS_MENUNGGU_KONFIRMASI_PADAL;
            $ticket->ditugaskan_oleh = Auth::id();
            $ticket->padal_id = $padal->id;
            $ticket->admin_notes = $validated['admin_notes'] ?? $ticket->admin_notes;
            $ticket->assigned_at = now();
            $ticket->rejection_reason = null;
            $ticket->save();

            TicketStatusHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => Auth::id(),
                'status_from' => $oldStatus,
                'status_to' => Ticket::STATUS_MENUNGGU_KONFIRMASI_PADAL,
                'notes' => 'Administrator mengubah penugasan tiket dari ' . $oldPadalName . ' menjadi ' . $padal->nama_padal . '.' . ($validated['admin_notes'] ? ' Alasan/Catatan: ' . $validated['admin_notes'] : ''),
            ]);

            return redirect()->route('admin.tickets.show', $ticket->id)
                ->with('success', 'Penugasan berhasil diperbarui ke ' . $padal->nama_padal . '.');
        });
    }

    /**
     * Tolak Laporan Masuk (Use Case: Memeriksa Laporan -> Tolak Laporan)
     */
    public function rejectTicket(Request $request, int $id)
    {
        $ticket = Ticket::findOrFail($id);

        if ($ticket->status !== Ticket::STATUS_MENUNGGU_VERIFIKASI) {
            return back()->with('error', 'Hanya laporan yang berstatus Menunggu Verifikasi yang dapat ditolak oleh Administrator.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:5', 'max:1000'],
        ], [
            'rejection_reason.required' => 'Alasan penolakan laporan wajib diisi.',
            'rejection_reason.min' => 'Alasan penolakan minimal 5 karakter.',
        ]);

        return DB::transaction(function () use ($ticket, $validated) {
            $oldStatus = $ticket->status;

            $ticket->status = Ticket::STATUS_DITOLAK_ADMIN;
            $ticket->diperiksa_oleh = Auth::id();
            $ticket->rejection_reason = $validated['rejection_reason'];
            $ticket->verified_at = now();
            $ticket->save();

            TicketStatusHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => Auth::id(),
                'status_from' => $oldStatus,
                'status_to' => Ticket::STATUS_DITOLAK_ADMIN,
                'notes' => 'Laporan ditolak oleh Administrator (' . Auth::user()->name . '). Alasan: ' . $validated['rejection_reason'],
            ]);

            return redirect()->route('admin.tickets.show', $ticket->id)
                ->with('success', 'Laporan berhasil ditolak dengan alasan yang tercatat.');
        });
    }

    // -------------------------------------------------------------
    // USE CASE: MENGELOLA AKUN (Tambah, Ubah, Lihat, Hapus Akun)
    // -------------------------------------------------------------
    public function users(Request $request)
    {
        $query = User::query();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('approval_status')) {
            $query->where('approval_status', $request->approval_status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nrp_nip', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%")
                  ->orWhere('satuan_kerja', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(10)->withQueryString();
        $activeAdminCount = User::where('role', User::ROLE_ADMIN)
            ->where('is_active', true)
            ->count();

        return view('admin.users.index', compact('users', 'activeAdminCount'));
    }

    public function createUser()
    {
        return view('admin.users.create');
    }

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:padal,teknisi'],
            'nrp_nip' => ['required', 'string', 'max:50'],
            'satuan_kerja' => ['required', 'string', 'max:100'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'alamat' => ['nullable', 'string', 'max:255'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.unique' => 'Email tersebut sudah terdaftar.',
            'role.required' => 'Pilih role Padal atau Teknisi.',
            'role.in' => 'Admin hanya dapat membuat akun Padal atau Teknisi.',
            'nrp_nip.required' => 'NRP atau NIP wajib diisi.',
            'satuan_kerja.required' => 'Satuan Kerja / Bagian wajib diisi.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'nrp_nip' => $validated['nrp_nip'],
            'satuan_kerja' => $validated['satuan_kerja'],
            'phone_number' => $validated['phone_number'] ?? null,
            'alamat' => $validated['alamat'] ?? null,
            'password' => Str::random(64),
            'email_verified_at' => null,
            'is_active' => false,
            'approval_status' => User::APPROVAL_ACTIVE,
            'requires_email_verification' => true,
        ]);

        if (!app(AccountActivationSender::class)->send($user)) {
            return redirect()->route('admin.users.index')->with(
                'error',
                'Akun berhasil dibuat dalam status menunggu aktivasi, tetapi undangan email belum dapat dikirim. Periksa konfigurasi email lalu kirim ulang undangan.'
            );
        }

        return redirect()->route('admin.users.index')->with(
            'success',
            'Akun berhasil dibuat. Undangan untuk mengatur kata sandi telah dikirim ke email pengguna.'
        );
    }

    public function showUser(int $id)
    {
        $user = User::withCount(['tickets', 'padalTeams'])->findOrFail($id);
        return view('admin.users.show', compact('user'));
    }

    public function editUser(int $id)
    {
        $user = User::findOrFail($id);

        if ($user->role === User::ROLE_ADMIN) {
            return redirect()->route('admin.users.index')->with(
                'error',
                'Akun Admin tidak dapat diubah melalui Manajemen Pengguna.'
            );
        }

        return view('admin.users.edit', compact('user'));
    }

    public function updateUser(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        if ($user->role === User::ROLE_ADMIN) {
            return back()->with('error', 'Akun Admin tidak dapat diubah melalui Manajemen Pengguna.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'role' => ['required', 'in:user,padal,teknisi'],
            'nrp_nip' => ['required', 'string', 'max:50'],
            'satuan_kerja' => ['required', 'string', 'max:100'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'alamat' => ['nullable', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ]);

        if ($user->id === Auth::id() && !$validated['is_active']) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        if ($user->id === Auth::id() && $validated['role'] !== $user->role) {
            return back()->with('error', 'Anda tidak dapat mengubah role akun Anda sendiri.');
        }

        $removingLastActiveAdmin = $user->role === User::ROLE_ADMIN
            && $user->is_active
            && ($validated['role'] !== User::ROLE_ADMIN || !$validated['is_active'])
            && User::where('role', User::ROLE_ADMIN)->where('is_active', true)->count() <= 1;

        if ($removingLastActiveAdmin) {
            return back()->with('error', 'Admin aktif terakhir tidak dapat dinonaktifkan atau diubah rolenya.');
        }

        $emailChanged = $user->email !== $validated['email'];
        $isStaffAccount = in_array($validated['role'], [User::ROLE_PADAL, User::ROLE_TEKNISI], true);

        if ($isStaffAccount
            && !$user->is_active
            && (bool) $validated['is_active']
            && (!$user->hasVerifiedEmail() || !$user->isApprovalActive())) {
            return back()->with(
                'error',
                'Akun Padal/Teknisi harus menyelesaikan aktivasi email sebelum dapat diaktifkan.'
            );
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        if ($emailChanged) {
            $user->email_verified_at = null;
            $user->requires_email_verification = true;
        }

        if (in_array($validated['role'], [User::ROLE_PADAL, User::ROLE_TEKNISI], true)) {
            if ($user->role !== $validated['role']) {
                $user->approval_status = User::APPROVAL_ACTIVE;
            }
        } else {
            $user->approval_status = User::APPROVAL_ACTIVE;
        }

        $user->is_active = (bool) $validated['is_active'];

        $user->role = $validated['role'];
        $user->nrp_nip = $validated['nrp_nip'];
        $user->satuan_kerja = $validated['satuan_kerja'];
        $user->phone_number = $validated['phone_number'] ?? null;
        $user->alamat = $validated['alamat'] ?? null;

        $user->save();

        if ($emailChanged && $isStaffAccount && !$user->is_active) {
            if (!app(AccountActivationSender::class)->send($user)) {
                return redirect()->route('admin.users.index')->with(
                    'error',
                    'Data akun diperbarui, tetapi undangan aktivasi belum dapat dikirim. Silakan kirim ulang undangan setelah email diperiksa.'
                );
            }
        } elseif ($emailChanged) {
            if (!app(EmailVerificationSender::class)->send($user)) {
                return redirect()->route('admin.users.index')->with(
                    'error',
                    'Data pengguna diperbarui, tetapi email verifikasi belum dapat dikirim. Silakan kirim ulang setelah konfigurasi email diperiksa.'
                );
            }
        }

        return redirect()->route('admin.users.index')->with(
            'success',
            $emailChanged
                ? 'Data pengguna diperbarui. Email baru harus menjalani verifikasi ulang.'
                : 'Data pengguna berhasil diperbarui.'
        );
    }

    public function sendActivationInvitation(int $id)
    {
        $user = User::findOrFail($id);

        if (!in_array($user->role, [User::ROLE_PADAL, User::ROLE_TEKNISI], true)
            || $user->is_active
            || !$user->requiresEmailVerification()
            || $user->hasVerifiedEmail()
            || !$user->isApprovalActive()) {
            return back()->with(
                'error',
                'Undangan aktivasi hanya dapat dikirim untuk akun Padal/Teknisi yang masih menunggu aktivasi.'
            );
        }

        if (!app(AccountActivationSender::class)->send($user)) {
            return back()->with(
                'error',
                'Undangan aktivasi belum dapat dikirim. Periksa konfigurasi email dan coba lagi.'
            );
        }

        return back()->with('success', 'Undangan aktivasi berhasil dikirim ulang.');
    }

    public function approveUser(int $id)
    {
        $user = User::findOrFail($id);

        if (!in_array($user->role, [User::ROLE_PADAL, User::ROLE_TEKNISI], true)) {
            return back()->with('error', 'Hanya akun Padal atau Teknisi yang dapat diproses persetujuannya.');
        }

        if (!$user->hasVerifiedEmail()) {
            return back()->with('error', 'Akun belum dapat disetujui sampai alamat email diverifikasi.');
        }

        $user->approval_status = User::APPROVAL_ACTIVE;
        $user->is_active = true;
        $user->save();

        return back()->with('success', 'Pendaftaran akun berhasil disetujui.');
    }

    public function rejectUser(int $id)
    {
        $user = User::findOrFail($id);

        if (!in_array($user->role, [User::ROLE_PADAL, User::ROLE_TEKNISI], true)) {
            return back()->with('error', 'Hanya akun Padal atau Teknisi yang dapat ditolak pendaftarannya.');
        }

        $user->approval_status = User::APPROVAL_REJECTED;
        $user->is_active = false;
        $user->save();

        return back()->with('success', 'Pendaftaran akun berhasil ditolak dan akun dinonaktifkan.');
    }

    public function toggleUserStatus(int $id)
    {
        $user = User::findOrFail($id);

        if ($user->role === User::ROLE_ADMIN) {
            return back()->with('error', 'Status akun Admin tidak dapat diubah melalui Manajemen Pengguna.');
        }

        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat mengubah status akun Anda sendiri.');
        }

        if (!$user->is_active
            && in_array($user->role, [User::ROLE_PADAL, User::ROLE_TEKNISI], true)
            && (!$user->hasVerifiedEmail() || !$user->isApprovalActive())) {
            return back()->with(
                'error',
                'Akun Padal/Teknisi harus menyelesaikan aktivasi email sebelum dapat diaktifkan.'
            );
        }

        $user->is_active = !$user->is_active;
        $user->save();

        return back()->with(
            'success',
            $user->is_active
                ? 'Akun pengguna berhasil diaktifkan kembali.'
                : 'Akun pengguna berhasil dinonaktifkan.'
        );
    }

    public function destroyUser(int $id)
    {
        $user = User::findOrFail($id);

        if ($user->role === User::ROLE_ADMIN) {
            return back()->with('error', 'Akun Admin tidak dapat dihapus melalui Manajemen Pengguna.');
        }

        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        if ($user->tickets()->exists()) {
            return back()->with('error', 'Akun tidak dapat dihapus karena memiliki riwayat laporan tiket.');
        }

        if ($user->ticketTechnicianAssignments()->exists()) {
            return back()->with('error', 'Akun Teknisi tidak dapat dihapus karena masih memiliki riwayat penugasan tiket.');
        }

        if ($user->padalTeams()->exists()) {
            return back()->with('error', 'Akun tidak dapat dihapus karena masih tercatat sebagai anggota unit Padal.');
        }

        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Akun pengguna berhasil dihapus.');
    }

    // -------------------------------------------------------------
    // USE CASE: MENGELOLA KATEGORI GANGGUAN (Tambah, Ubah, Lihat, Hapus)
    // -------------------------------------------------------------
    public function categories()
    {
        $categories = Category::withCount('tickets')->latest()->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
            'description' => ['nullable', 'string', 'max:500'],
            'icon' => ['nullable', 'string', 'max:50'],
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique' => 'Kategori dengan nama tersebut sudah ada.',
        ]);

        Category::create([
            'name' => $validated['name'],
            'slug' => \Illuminate\Support\Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'icon' => $validated['icon'] ?? 'bi-tag',
            'is_active' => true,
        ]);

        return back()->with('success', 'Kategori permasalahan berhasil ditambahkan.');
    }

    public function updateCategory(Request $request, int $id)
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name,' . $category->id],
            'description' => ['nullable', 'string', 'max:500'],
            'icon' => ['nullable', 'string', 'max:50'],
            'is_active' => ['required', 'boolean'],
        ]);

        $category->name = $validated['name'];
        $category->slug = \Illuminate\Support\Str::slug($validated['name']);
        $category->description = $validated['description'] ?? null;
        $category->icon = $validated['icon'] ?? 'bi-tag';
        $category->is_active = (bool) $validated['is_active'];
        $category->save();

        return back()->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroyCategory(int $id)
    {
        $deleted = DB::transaction(function () use ($id): bool {
            $category = Category::whereKey($id)->lockForUpdate()->firstOrFail();

            if ($category->tickets()->exists()) {
                return false;
            }

            $category->delete();
            return true;
        });

        if (!$deleted) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh tiket. Nonaktifkan kategori jika tidak ingin dipilih untuk laporan baru.');
        }

        return back()->with('success', 'Kategori permasalahan berhasil dihapus.');
    }

    // -------------------------------------------------------------
    // USE CASE: MENGELOLA PADAL (Tambah, Ubah, Lihat, Hapus Padal)
    // -------------------------------------------------------------
    public function padals()
    {
        $padals = Padal::with(['leader', 'members'])
            ->withCount(['tickets', 'members'])
            ->latest()
            ->paginate(10);

        // List calon pemimpin Padal (Users dengan role Padal)
        $padalOfficers = User::where('role', User::ROLE_PADAL)->where('is_active', true)->get();

        return view('admin.padals.index', compact('padals', 'padalOfficers'));
    }

    public function storePadal(Request $request)
    {
        $validated = $request->validate([
            'nama_padal' => ['required', 'string', 'max:255', 'unique:padals,nama_padal'],
            'user_id' => ['required', 'exists:users,id'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'in:aktif,nonaktif'],
        ], [
            'nama_padal.required' => 'Nama unit Padal wajib diisi.',
            'nama_padal.unique' => 'Nama unit Padal tersebut sudah ada.',
            'user_id.required' => 'Pilih Perwira Pengendali (Ketua/Pemimpin).',
        ]);

        Padal::create([
            'nama_padal' => $validated['nama_padal'],
            'user_id' => $validated['user_id'],
            'deskripsi' => $validated['deskripsi'] ?? null,
            'status' => $validated['status'],
        ]);

        return back()->with('success', 'Unit Padal baru berhasil ditambahkan.');
    }

    public function updatePadal(Request $request, int $id)
    {
        $padal = Padal::findOrFail($id);

        $validated = $request->validate([
            'nama_padal' => ['required', 'string', 'max:255', 'unique:padals,nama_padal,' . $padal->id],
            'user_id' => ['required', 'exists:users,id'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'in:aktif,nonaktif'],
        ]);

        $padal->nama_padal = $validated['nama_padal'];
        $padal->user_id = $validated['user_id'];
        $padal->deskripsi = $validated['deskripsi'] ?? null;
        $padal->status = $validated['status'];
        $padal->save();

        return back()->with('success', 'Data unit Padal berhasil diperbarui.');
    }

    public function destroyPadal(int $id)
    {
        $padal = Padal::findOrFail($id);

        if ($padal->tickets()->exists() || $padal->ratings()->exists()) {
            return back()->with('error', 'Unit Padal tidak dapat dihapus karena masih memiliki riwayat tiket atau penilaian. Nonaktifkan unit jika tidak digunakan lagi.');
        }

        $padal->delete();
        return back()->with('success', 'Unit Padal berhasil dihapus.');
    }

    // -------------------------------------------------------------
    // USE CASE: MEMANTAU TIKET -> Lihat Rating Padal
    // -------------------------------------------------------------
    public function ratings(Request $request)
    {
        $query = TicketRating::with(['ticket', 'user', 'padal.leader']);

        if ($request->filled('padal_id')) {
            $query->where('padal_id', $request->padal_id);
        }

        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        $avgRating = round((float) (clone $query)->avg('rating'), 2);
        $ratings = $query->with(['ticket', 'user', 'padal.leader'])->latest()->paginate(10)->withQueryString();
        $padals = Padal::withCount('ratings')
            ->withAvg('ratings', 'rating')
            ->orderBy('nama_padal')
            ->get();

        return view('admin.ratings.index', compact('ratings', 'padals', 'avgRating'));
    }

    // -------------------------------------------------------------
    // USE CASE: REKAP LAPORAN (Filter Periode & Cetak Laporan)
    // -------------------------------------------------------------
    private function resolveRecapFilters(Request $request): array
    {
        $dateFrom = $request->filled('date_from') ? Carbon::parse($request->date_from)->startOfDay() : Carbon::now()->startOfMonth();
        $dateTo = $request->filled('date_to') ? Carbon::parse($request->date_to)->endOfDay() : Carbon::now()->endOfMonth();

        if ($request->filled('date_from') && $request->filled('date_to') && $request->date_from > $request->date_to) {
            [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
        }

        Carbon::setLocale('id');

        $query = Ticket::with(['user', 'category', 'padal.leader', 'rating'])
            ->whereBetween('created_at', [$dateFrom, $dateTo]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('padal_id')) {
            $query->where('padal_id', $request->padal_id);
        }

        $tickets = $query->latest()->get();

        return [
            'tickets' => $tickets,
            'categories' => Category::all(),
            'padals' => Padal::all(),
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ];
    }

    public function reportRecap(Request $request)
    {
        $data = $this->resolveRecapFilters($request);

        return view('admin.reports.recap', $data);
    }

    public function previewReportRecap(Request $request)
    {
        $data = $this->resolveRecapFilters($request);
        $reportStart = $data['dateFrom'];
        $reportEnd = $data['dateTo'];

        return view('admin.reports.preview', [
            'tickets' => $data['tickets'],
            'reportStart' => $reportStart,
            'reportEnd' => $reportEnd,
            'createdAt' => now(),
            'isEmpty' => $data['tickets']->isEmpty(),
        ] + $request->query());
    }

    public function exportReportRecap(Request $request)
    {
        $data = $this->resolveRecapFilters($request);
        $reportStart = $data['dateFrom'];
        $reportEnd = $data['dateTo'];
        $monthLabel = $reportStart->translatedFormat('F Y');
        $fileName = 'Rekapitulasi_Laporan_LayanIT_' . $reportStart->translatedFormat('F') . '_' . $reportStart->format('Y') . '.pdf';

        $pdf = Pdf::loadView('admin.reports.pdf', [
            'tickets' => $data['tickets'],
            'reportStart' => $reportStart,
            'reportEnd' => $reportEnd,
            'createdAt' => now(),
            'monthLabel' => $monthLabel,
            'isEmpty' => $data['tickets']->isEmpty(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download($fileName);
    }
}
