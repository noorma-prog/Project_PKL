<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Padal;
use App\Models\PadalAnggota;
use App\Models\Ticket;
use App\Models\TicketRating;
use App\Models\TicketStatusHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        $activeTickets = Ticket::with(['user', 'category'])
            ->where('padal_id', $padal->id)
            ->where('status', Ticket::STATUS_DALAM_PENANGANAN)
            ->latest()
            ->take(5)
            ->get();

        return view('padal.dashboard', compact('stats', 'pendingTickets', 'activeTickets', 'padal'));
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
            'rating', 
            'histories.user'
        ])->where('padal_id', $padal->id)->findOrFail($id);

        return view('padal.tickets.show', compact('ticket', 'padal'));
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

        $request->validate([
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        return DB::transaction(function () use ($ticket, $padal, $request) {
            $oldStatus = $ticket->status;

            $ticket->status = Ticket::STATUS_DALAM_PENANGANAN;
            $ticket->started_at = now();
            $ticket->save();

            TicketStatusHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => Auth::id(),
                'status_from' => $oldStatus,
                'status_to' => Ticket::STATUS_DALAM_PENANGANAN,
                'notes' => 'Penugasan DITERIMA oleh Perwira Pengendali (' . Auth::user()->name . ' - ' . $padal->nama_padal . '). Penanganan teknis dimulai.' . ($request->filled('notes') ? ' Catatan: ' . $request->notes : ''),
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
        $members = $padal->members()->withPivot('id', 'tanggal_bergabung')->get();

        // Teknisi yang belum masuk ke dalam Padal ini
        $existingMemberIds = $padal->members()->pluck('users.id')->toArray();
        $availableTechnicians = User::where('role', User::ROLE_TEKNISI)
            ->where('is_active', true)
            ->whereNotIn('id', $existingMemberIds)
            ->get();

        return view('padal.members.index', compact('padal', 'members', 'availableTechnicians'));
    }

    public function addMember(Request $request)
    {
        $padal = $this->getCurrentPadal();

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ], [
            'user_id.required' => 'Pilih teknisi yang akan ditambahkan sebagai anggota.',
        ]);

        $technician = User::where('role', User::ROLE_TEKNISI)->findOrFail($validated['user_id']);

        PadalAnggota::updateOrCreate(
            ['padal_id' => $padal->id, 'user_id' => $technician->id],
            ['tanggal_bergabung' => now()]
        );

        return back()->with('success', 'Teknisi ' . $technician->name . ' berhasil ditambahkan sebagai anggota tim ' . $padal->nama_padal . '.');
    }

    public function removeMember($userId)
    {
        $padal = $this->getCurrentPadal();
        $technician = User::findOrFail($userId);

        PadalAnggota::where('padal_id', $padal->id)->where('user_id', $userId)->delete();

        return back()->with('success', 'Teknisi ' . $technician->name . ' berhasil dikeluarkan dari tim ' . $padal->nama_padal . '.');
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
