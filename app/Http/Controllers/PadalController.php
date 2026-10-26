<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Padal;
use App\Models\PadalAnggota;
use App\Models\Ticket;
use App\Models\TicketRating;
use App\Models\TicketStatusHistory;
use App\Models\TicketTechnicianAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PadalController extends Controller
{
    /**
     * Mendapatkan unit Padal yang dipimpin oleh user yang sedang login
     */
    private function getCurrentPadal(): Padal
    {
        $padal = Padal::where('user_id', Auth::id())->first();

        if (!$padal) {
            // Jika belum terdaftar di tabel padal, buat otomatis atau ambil unit pertama
            $padal = Padal::firstOrCreate(
                ['user_id' => Auth::id()],
                [
                    'nama_padal' => 'Regu Padal ' . Auth::user()->name,
                    'deskripsi' => 'Unit Perwira Pengendali ' . Auth::user()->name,
                    'status' => 'aktif',
                ]
            );
        }

        return $padal;
    }

    /**
     * Dashboard Perwira Pengendali (Padal)
     */
    public function dashboard()
    {
        $padal = $this->getCurrentPadal();

        $stats = [
            'total_ditugaskan' => Ticket::where('padal_id', $padal->id)->count(),
            'menunggu_konfirmasi' => Ticket::where('padal_id', $padal->id)->where('status', Ticket::STATUS_MENUNGGU_KONFIRMASI_PADAL)->count(),
            'dalam_penanganan' => Ticket::where('padal_id', $padal->id)->where('status', Ticket::STATUS_DALAM_PENANGANAN)->count(),
            'selesai' => Ticket::where('padal_id', $padal->id)->whereIn('status', [Ticket::STATUS_SELESAI, Ticket::STATUS_SELESAI_DINILAI])->count(),
            'ditolak' => Ticket::where('padal_id', $padal->id)->where('status', Ticket::STATUS_DITOLAK_PADAL)->count(),
            'total_anggota' => $padal->members()->count(),
            'avg_rating' => round((float) TicketRating::where('padal_id', $padal->id)->avg('rating'), 1) ?: 0,
            'total_ulasan' => TicketRating::where('padal_id', $padal->id)->count(),
        ];

        // Laporan menunggu konfirmasi Padal
        $pendingTickets = Ticket::with(['user', 'category'])
            ->where('padal_id', $padal->id)
            ->where('status', Ticket::STATUS_MENUNGGU_KONFIRMASI_PADAL)
            ->latest()
            ->take(5)
            ->get();

        // Laporan aktif dalam pengerjaan
        $activeTickets = Ticket::with(['user', 'category', 'technicians'])
            ->where('padal_id', $padal->id)
            ->where('status', Ticket::STATUS_DALAM_PENANGANAN)
            ->latest()
            ->take(5)
            ->get();

        $members = $padal->members()->orderBy('name')->get();

        return view('padal.dashboard', compact('stats', 'pendingTickets', 'activeTickets', 'padal', 'members'));
    }

    /**
     * Daftar seluruh tiket yang ditugaskan kepada Padal ini (Use Case: Lihat Tiket Ditugaskan)
     */
    public function tickets(Request $request)
    {
        $padal = $this->getCurrentPadal();

        $query = Ticket::with(['user', 'category', 'rating'])
            ->where('padal_id', $padal->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $tickets = $query->latest()->paginate(10)->withQueryString();
        $categories = Category::where('is_active', true)->get();

        return view('padal.tickets.index', compact('tickets', 'categories', 'padal'));
    }

    /**
     * Detail tiket untuk Padal
     */
    public function showTicket($id)
    {
        $padal = $this->getCurrentPadal();

        $ticket = Ticket::with([
            'user',
            'category',
            'diperiksaOleh',
            'ditugaskanOleh',
            'padal.members',
            'technicians',
            'rating',
            'histories.user',
        ])->where('padal_id', $padal->id)->findOrFail($id);

        $availableTechnicians = $padal->members()
            ->where('users.role', User::ROLE_TEKNISI)
            ->where('users.is_active', true)
            ->where('users.approval_status', User::APPROVAL_ACTIVE)
            ->where(function ($query) {
                $query->where('users.requires_email_verification', false)
                    ->orWhereNotNull('users.email_verified_at');
            })
            ->orderBy('users.name')
            ->get();

        return view('padal.tickets.show', compact('ticket', 'padal', 'availableTechnicians'));
    }

    /**
     * Terima Penugasan oleh Padal (Use Case: Menangani Tiket -> Terima Penugasan)
     */
    public function acceptAssignment(Request $request, $id)
    {
        $padal = $this->getCurrentPadal();
        $ticket = Ticket::where('padal_id', $padal->id)->findOrFail($id);

        if ($ticket->status !== Ticket::STATUS_MENUNGGU_KONFIRMASI_PADAL) {
            return back()->with('error', 'Hanya laporan dengan status Menunggu Konfirmasi Padal yang dapat diterima.');
        }

        $validated = $request->validate([
            'technician_ids' => ['required', 'array', 'min:1'],
            'technician_ids.*' => ['required', 'integer', 'distinct'],
            'padal_notes' => ['nullable', 'string', 'max:500'],
        ]);

        return DB::transaction(function () use ($ticket, $padal, $validated) {
            $ticket = Ticket::whereKey($ticket->id)->lockForUpdate()->firstOrFail();
            if ($ticket->status !== Ticket::STATUS_MENUNGGU_KONFIRMASI_PADAL) {
                return back()->with('error', 'Penugasan ini sudah diproses sebelumnya.');
            }

            $technicianIds = array_map('intval', $validated['technician_ids']);
            $eligibleIds = $padal->members()
                ->where('users.role', User::ROLE_TEKNISI)
                ->where('users.is_active', true)
                ->where('users.approval_status', User::APPROVAL_ACTIVE)
                ->where(function ($query) {
                    $query->where('users.requires_email_verification', false)
                        ->orWhereNotNull('users.email_verified_at');
                })
                ->whereIn('users.id', $technicianIds)
                ->pluck('users.id')
                ->map(fn ($id) => (int) $id)
                ->all();

            if (count($eligibleIds) !== count(array_unique($technicianIds))) {
                throw ValidationException::withMessages([
                    'technician_ids' => 'Pilih hanya teknisi aktif yang sudah menjadi anggota tim Padal Anda.',
                ]);
            }

            $oldStatus = $ticket->status;
            $this->updateTicketTechnicianAssignments($ticket, $technicianIds);

            $ticket->status = Ticket::STATUS_DALAM_PENANGANAN;
            $ticket->started_at = now();
            $ticket->save();

            TicketStatusHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => Auth::id(),
                'status_from' => $oldStatus,
                'status_to' => Ticket::STATUS_DALAM_PENANGANAN,
                'notes' => 'Penugasan DITERIMA oleh Perwira Pengendali (' . Auth::user()->name . ' - ' . $padal->nama_padal . '). Penanganan teknis dimulai.' . (!empty($validated['padal_notes']) ? ' Catatan: ' . $validated['padal_notes'] : ''),
            ]);

            return redirect()->route('padal.tickets.show', $ticket->id)
                ->with('success', 'Penugasan berhasil diterima. Status laporan kini Dalam Penanganan.');
        });
    }

    /**
     * Tolak Penugasan oleh Padal (Use Case: Menangani Tiket -> Tolak Penugasan)
     */
    public function rejectAssignment(Request $request, $id)
    {
        $padal = $this->getCurrentPadal();
        $ticket = Ticket::where('padal_id', $padal->id)->findOrFail($id);

        if ($ticket->status !== Ticket::STATUS_MENUNGGU_KONFIRMASI_PADAL) {
            return back()->with('error', 'Hanya laporan berstatus Menunggu Konfirmasi Padal yang dapat ditolak penugasannya.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:5', 'max:1000'],
        ], [
            'rejection_reason.required' => 'Alasan penolakan penugasan wajib diisi.',
            'rejection_reason.min' => 'Alasan penolakan minimal 5 karakter.',
        ]);

        return DB::transaction(function () use ($ticket, $padal, $validated) {
            $oldStatus = $ticket->status;

            $ticket->status = Ticket::STATUS_DITOLAK_PADAL;
            $ticket->rejection_reason = $validated['rejection_reason'];
            $ticket->save();

            TicketStatusHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => Auth::id(),
                'status_from' => $oldStatus,
                'status_to' => Ticket::STATUS_DITOLAK_PADAL,
                'notes' => 'Penugasan DITOLAK oleh Padal (' . Auth::user()->name . ' - ' . $padal->nama_padal . '). Alasan: ' . $validated['rejection_reason'] . '. Laporan dikembalikan ke Administrator untuk ditugaskan ulang.',
            ]);

            return redirect()->route('padal.tickets.show', $ticket->id)
                ->with('success', 'Penugasan ditolak. Laporan dikembalikan ke Administrator untuk penugasan ulang.');
        });
    }

    /**
     * Catat Riwayat Status / Catatan Perkembangan (Use Case: Menangani Tiket -> Catat Riwayat Status)
     */
    public function addHistoryNote(Request $request, $id)
    {
        $padal = $this->getCurrentPadal();
        $ticket = Ticket::where('padal_id', $padal->id)->findOrFail($id);

        $validated = $request->validate([
            'progress_note' => ['required', 'string', 'min:3', 'max:1000'],
        ], [
            'progress_note.required' => 'Catatan riwayat / perkembangan wajib diisi.',
        ]);

        TicketStatusHistory::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'status_from' => $ticket->status,
            'status_to' => $ticket->status,
            'notes' => '[Catatan Perkembangan oleh ' . Auth::user()->name . ']: ' . $validated['progress_note'],
        ]);

        return back()->with('success', 'Catatan riwayat perkembangan berhasil ditambahkan ke kronologi tiket.');
    }

    /**
     * Selesaikan Tiket Pekerjaan (Use Case: Menangani Tiket -> Selesaikan Tiket)
     */
    public function completeTicket(Request $request, $id)
    {
        $padal = $this->getCurrentPadal();
        $ticket = Ticket::where('padal_id', $padal->id)->findOrFail($id);

        if ($ticket->status !== Ticket::STATUS_DALAM_PENANGANAN) {
            return back()->with('error', 'Hanya laporan Dalam Penanganan yang dapat diselesaikan.');
        }

        $validated = $request->validate([
            'resolution_notes' => ['required', 'string', 'min:10', 'max:2000'],
            'resolution_attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ], [
            'resolution_notes.required' => 'Dokumentasi / catatan tindakan penyelesaian wajib diisi.',
            'resolution_notes.min' => 'Dokumentasi penyelesaian minimal 10 karakter.',
            'resolution_attachment.mimes' => 'Format berkas bukti harus JPG, PNG, atau PDF.',
        ]);

        return DB::transaction(function () use ($ticket, $padal, $request, $validated) {
            $oldStatus = $ticket->status;

            $attachmentPath = null;
            if ($request->hasFile('resolution_attachment')) {
                $attachmentPath = $request->file('resolution_attachment')->store('attachments/resolutions', 'public');
            }

            $ticket->status = Ticket::STATUS_SELESAI;
            $ticket->resolution_notes = $validated['resolution_notes'];
            $ticket->resolution_attachment = $attachmentPath ?? $ticket->resolution_attachment;
            $ticket->completed_at = now();
            $ticket->save();

            TicketStatusHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => Auth::id(),
                'status_from' => $oldStatus,
                'status_to' => Ticket::STATUS_SELESAI,
                'notes' => 'Pekerjaan dinyatakan SELESAI oleh Padal (' . Auth::user()->name . ' - ' . $padal->nama_padal . '). Catatan Tindakan: ' . $validated['resolution_notes'] . '. Tiket siap dinilai oleh pelapor.',
            ]);

            return redirect()->route('padal.tickets.show', $ticket->id)
                ->with('success', 'Pekerjaan penanganan laporan berhasil diselesaikan. Menunggu penilaian kepuasan dari pelapor.');
        });
    }

    // -------------------------------------------------------------
    // USE CASE: MENGELOLA ANGGOTA (Lihat Anggota, Tambah Anggota, Hapus Anggota)
    // -------------------------------------------------------------
    public function members()
    {
        $padal = $this->getCurrentPadal();
        $members = $padal->members()
            ->withPivot('id', 'tanggal_bergabung')
            ->orderBy('users.name')
            ->get();

        // Teknisi yang belum masuk ke dalam Padal ini
        $existingMemberIds = $padal->members()->pluck('users.id')->toArray();
        $availableTechnicians = $this->eligibleTechnicians()
            ->whereNotIn('id', $existingMemberIds)
            ->orderBy('name')
            ->get();

        return view('padal.members.index', compact('padal', 'members', 'availableTechnicians'));
    }

    public function addMember(Request $request)
    {
        $padal = $this->getCurrentPadal();

        $validated = $request->validate([
            'technician_ids' => ['required', 'array', 'min:1'],
            'technician_ids.*' => ['required', 'integer', 'distinct'],
        ], [
            'technician_ids.required' => 'Pilih minimal satu teknisi yang akan ditambahkan.',
            'technician_ids.min' => 'Pilih minimal satu teknisi yang akan ditambahkan.',
            'technician_ids.*.distinct' => 'Teknisi yang sama tidak boleh dipilih lebih dari sekali.',
        ]);

        $technicianIds = array_map('intval', $validated['technician_ids']);

        DB::transaction(function () use ($padal, $technicianIds) {
            Padal::whereKey($padal->id)->lockForUpdate()->firstOrFail();

            $technicians = $this->eligibleTechnicians()
                ->whereIn('id', $technicianIds)
                ->get();

            if ($technicians->count() !== count(array_unique($technicianIds))) {
                throw ValidationException::withMessages([
                    'technician_ids' => 'Pilih hanya akun Teknisi yang aktif dan memenuhi syarat.',
                ]);
            }

            $alreadyMembers = $padal->members()
                ->whereIn('users.id', $technicianIds)
                ->pluck('users.id')
                ->all();

            if ($alreadyMembers !== []) {
                throw ValidationException::withMessages([
                    'technician_ids' => 'Satu atau beberapa teknisi sudah menjadi anggota tim Padal ini.',
                ]);
            }

            foreach ($technicians as $technician) {
                PadalAnggota::create([
                    'padal_id' => $padal->id,
                    'user_id' => $technician->id,
                    'tanggal_bergabung' => now(),
                ]);
            }
        });

        return back()->with('success', 'Teknisi berhasil ditambahkan sebagai anggota tim ' . $padal->nama_padal . '.');
    }

    public function removeMember($userId)
    {
        $padal = $this->getCurrentPadal();
        $technician = $padal->members()
            ->where('users.role', User::ROLE_TEKNISI)
            ->whereKey($userId)
            ->firstOrFail();

        PadalAnggota::where('padal_id', $padal->id)->where('user_id', $userId)->delete();

        return back()->with('success', 'Teknisi ' . $technician->name . ' berhasil dikeluarkan dari tim ' . $padal->nama_padal . '.');
    }

    public function updateTicketTechnicians(Request $request, int $id)
    {
        $padal = $this->getCurrentPadal();
        $ticket = Ticket::where('padal_id', $padal->id)->findOrFail($id);

        if ($ticket->status !== Ticket::STATUS_DALAM_PENANGANAN) {
            return back()->with('error', 'Teknisi hanya dapat diperbarui untuk tiket yang sedang dalam penanganan.');
        }

        $validated = $request->validate([
            'technician_ids' => ['nullable', 'array'],
            'technician_ids.*' => ['required', 'integer', 'distinct'],
        ]);

        $technicianIds = array_map('intval', $validated['technician_ids'] ?? []);

        DB::transaction(function () use ($padal, $ticket, $technicianIds) {
            $ticket = Ticket::whereKey($ticket->id)->lockForUpdate()->firstOrFail();
            if ($ticket->status !== Ticket::STATUS_DALAM_PENANGANAN) {
                throw ValidationException::withMessages([
                    'technician_ids' => 'Status tiket berubah. Muat ulang halaman sebelum memperbarui teknisi.',
                ]);
            }

            $eligibleIds = $padal->members()
                ->where('users.role', User::ROLE_TEKNISI)
                ->where('users.is_active', true)
                ->where('users.approval_status', User::APPROVAL_ACTIVE)
                ->where(function ($query) {
                    $query->where('users.requires_email_verification', false)
                        ->orWhereNotNull('users.email_verified_at');
                })
                ->whereIn('users.id', $technicianIds)
                ->pluck('users.id')
                ->map(fn ($technicianId) => (int) $technicianId)
                ->all();

            if (count($eligibleIds) !== count(array_unique($technicianIds))) {
                throw ValidationException::withMessages([
                    'technician_ids' => 'Pilih hanya teknisi aktif yang sudah menjadi anggota tim Padal Anda.',
                ]);
            }

            $this->updateTicketTechnicianAssignments($ticket, $technicianIds);
        });

        return back()->with('success', 'Penugasan Teknisi pada tiket berhasil diperbarui. Riwayat penugasan sebelumnya tetap tersimpan.');
    }

    private function eligibleTechnicians(): Builder
    {
        return User::query()
            ->where('role', User::ROLE_TEKNISI)
            ->where('is_active', true)
            ->where('approval_status', User::APPROVAL_ACTIVE)
            ->where(function ($query) {
                $query->where('requires_email_verification', false)
                    ->orWhereNotNull('email_verified_at');
            });
    }

    private function updateTicketTechnicianAssignments(Ticket $ticket, array $technicianIds): void
    {
        $activeAssignments = TicketTechnicianAssignment::query()
            ->where('ticket_id', $ticket->id)
            ->whereNull('released_at');
        $currentIds = (clone $activeAssignments)->pluck('user_id')->map(fn ($id) => (int) $id)->all();
        $toRelease = array_diff($currentIds, $technicianIds);

        if ($toRelease !== []) {
            TicketTechnicianAssignment::where('ticket_id', $ticket->id)
                ->whereNull('released_at')
                ->whereIn('user_id', $toRelease)
                ->update(['released_at' => now()]);
        }

        foreach (array_diff($technicianIds, $currentIds) as $technicianId) {
            TicketTechnicianAssignment::create([
                'ticket_id' => $ticket->id,
                'user_id' => $technicianId,
                'assigned_by' => Auth::id(),
                'assigned_at' => now(),
            ]);
        }
    }

    /**
     * Riwayat pekerjaan & penilaian kepuasan pelayanan yang diterima
     */
    public function ratings()
    {
        $padal = $this->getCurrentPadal();

        $ratings = TicketRating::with(['ticket', 'user'])
            ->where('padal_id', $padal->id)
            ->latest()
            ->paginate(10);

        $avgRating = round((float) TicketRating::where('padal_id', $padal->id)->avg('rating'), 2) ?: 0;
        $totalRatings = TicketRating::where('padal_id', $padal->id)->count();

        return view('padal.ratings.index', compact('ratings', 'avgRating', 'totalRatings', 'padal'));
    }
}
