<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Padal;
use App\Models\Ticket;
use App\Models\TicketRating;
use App\Models\TicketStatusHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminController extends Controller
{
    /**
     * Dashboard Administrator
     */
    public function dashboard()
    {
        $stats = [
            'total_tickets' => Ticket::count(),
            'menunggu_verifikasi' => Ticket::where('status', Ticket::STATUS_MENUNGGU_VERIFIKASI)->count(),
            'menunggu_konfirmasi_padal' => Ticket::where('status', Ticket::STATUS_MENUNGGU_KONFIRMASI_PADAL)->count(),
            'dalam_penanganan' => Ticket::where('status', Ticket::STATUS_DALAM_PENANGANAN)->count(),
            'selesai' => Ticket::whereIn('status', [Ticket::STATUS_SELESAI, Ticket::STATUS_SELESAI_DINILAI])->count(),
            'ditolak' => Ticket::whereIn('status', [Ticket::STATUS_DITOLAK_ADMIN, Ticket::STATUS_DITOLAK_PADAL])->count(),
            'total_users' => User::where('role', User::ROLE_USER)->count(),
            'total_padal' => Padal::count(),
            'total_teknisi' => User::where('role', User::ROLE_TEKNISI)->count(),
            'avg_rating' => round((float) TicketRating::avg('rating'), 1) ?: 0,
        ];

        // Laporan masuk yang membutuhkan verifikasi segera
        $pendingVerificationTickets = Ticket::with(['user', 'category'])
            ->where('status', Ticket::STATUS_MENUNGGU_VERIFIKASI)
            ->latest()
            ->take(5)
            ->get();

        // Laporan terbaru
        $recentTickets = Ticket::with(['user', 'category', 'padal.leader'])
            ->latest()
            ->take(8)
            ->get();

        return view('admin.dashboard', compact('stats', 'pendingVerificationTickets', 'recentTickets'));
    }

    /**
     * Daftar seluruh tiket (Use Case: Memantau Tiket -> Lihat Daftar Tiket)
     */
    public function tickets(Request $request)
    {
        $query = Ticket::with(['user', 'category', 'padal.leader', 'rating']);

        if ($request->filled('status')) {
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
        $padals = Padal::where('status', 'aktif')->with('leader')->get();

        return view('admin.tickets.index', compact('tickets', 'categories', 'padals'));
    }

    /**
     * Detail tiket untuk Admin (Use Case: Memeriksa Laporan -> Lihat Detail Laporan & Lihat Riwayat Status)
     */
    public function showTicket($id)
    {
        $ticket = Ticket::with([
            'user', 
            'category', 
            'diperiksaOleh', 
            'ditugaskanOleh', 
            'padal.leader', 
            'padal.members', 
            'rating', 
            'histories.user'
        ])->findOrFail($id);

        $padals = Padal::where('status', 'aktif')->with('leader')->get();

        return view('admin.tickets.show', compact('ticket', 'padals'));
    }

    /**
     * Konfirmasi Laporan & Pilih Padal (Use Case: Memeriksa Laporan -> Konfirmasi Laporan & Menugaskan Padal -> Pilih Padal)
     */
    public function verifyAndAssign(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        if (!in_array($ticket->status, [Ticket::STATUS_MENUNGGU_VERIFIKASI, Ticket::STATUS_DITOLAK_PADAL])) {
            return back()->with('error', 'Status tiket saat ini tidak memungkinkan untuk verifikasi/penugasan.');
        }

        $validated = $request->validate([
            'padal_id' => ['required', 'exists:padals,id'],
            'admin_notes' => ['nullable', 'string', 'max:500'],
        ], [
            'padal_id.required' => 'Pilih unit Perwira Pengendali (Padal) yang akan ditugaskan.',
            'padal_id.exists' => 'Data Padal tidak valid.',
        ]);

        $padal = Padal::with('leader')->findOrFail($validated['padal_id']);

        return DB::transaction(function () use ($ticket, $padal, $validated) {
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
    public function reassignTicket(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        $validated = $request->validate([
            'padal_id' => ['required', 'exists:padals,id'],
            'admin_notes' => ['nullable', 'string', 'max:500'],
        ], [
            'padal_id.required' => 'Pilih unit Padal baru yang akan ditugaskan.',
        ]);

        $padal = Padal::with('leader')->findOrFail($validated['padal_id']);

        return DB::transaction(function () use ($ticket, $padal, $validated) {
            $oldStatus = $ticket->status;
            $oldPadalName = $ticket->padal ? $ticket->padal->nama_padal : 'Padal Sebelumnya';

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
    public function rejectTicket(Request $request, $id)
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

        return view('admin.users.index', compact('users'));
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
            'role' => ['required', 'in:user,admin,padal,teknisi'],
            'nrp_nip' => ['required', 'string', 'max:50'],
            'satuan_kerja' => ['required', 'string', 'max:100'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'alamat' => ['nullable', 'string', 'max:255'],
            'password' => ['required', Password::min(6)],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.unique' => 'Email tersebut sudah terdaftar.',
            'role.required' => 'Pilih role pengguna.',
            'nrp_nip.required' => 'NRP atau NIP wajib diisi.',
            'satuan_kerja.required' => 'Satuan Kerja / Bagian wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'nrp_nip' => $validated['nrp_nip'],
            'satuan_kerja' => $validated['satuan_kerja'],
            'phone_number' => $validated['phone_number'] ?? null,
            'alamat' => $validated['alamat'] ?? null,
            'password' => Hash::make($validated['password']),
            'is_active' => true,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Pengguna baru berhasil ditambahkan.');
    }

    public function showUser($id)
    {
        $user = User::withCount(['tickets', 'padalTeams'])->findOrFail($id);
        return view('admin.users.show', compact('user'));
    }

    public function editUser($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'role' => ['required', 'in:user,admin,padal,teknisi'],
            'nrp_nip' => ['required', 'string', 'max:50'],
            'satuan_kerja' => ['required', 'string', 'max:100'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'alamat' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', Password::min(6)],
            'is_active' => ['required', 'boolean'],
        ]);

        if ($user->id === Auth::id() && !$validated['is_active']) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $user->nrp_nip = $validated['nrp_nip'];
        $user->satuan_kerja = $validated['satuan_kerja'];
        $user->phone_number = $validated['phone_number'] ?? null;
        $user->alamat = $validated['alamat'] ?? null;
        $user->is_active = (bool) $validated['is_active'];

        if ($request->filled('password')) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroyUser($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        if ($user->tickets()->exists()) {
            return back()->with('error', 'Akun tidak dapat dihapus karena memiliki riwayat laporan tiket.');
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

    public function updateCategory(Request $request, $id)
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

    public function destroyCategory($id)
    {
        $category = Category::findOrFail($id);

        if ($category->tickets()->exists()) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena digunakan pada data laporan tiket.');
        }

        $category->delete();
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

    public function updatePadal(Request $request, $id)
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

    public function destroyPadal($id)
    {
        $padal = Padal::findOrFail($id);

        if ($padal->tickets()->whereIn('status', [Ticket::STATUS_MENUNGGU_KONFIRMASI_PADAL, Ticket::STATUS_DALAM_PENANGANAN])->exists()) {
            return back()->with('error', 'Unit Padal tidak dapat dihapus karena masih menangani tiket yang aktif.');
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

        $ratings = $query->latest()->paginate(10)->withQueryString();
        $padals = Padal::all();
        $avgRating = round((float) TicketRating::avg('rating'), 2);

        return view('admin.ratings.index', compact('ratings', 'padals', 'avgRating'));
    }

    // -------------------------------------------------------------
    // USE CASE: REKAP LAPORAN (Filter Periode & Cetak Laporan)
    // -------------------------------------------------------------
    public function reportRecap(Request $request)
    {
        $query = Ticket::with(['user', 'category', 'padal.leader', 'rating']);

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

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
        $categories = Category::all();
        $padals = Padal::all();

        return view('admin.reports.recap', compact('tickets', 'categories', 'padals'));
    }
}
