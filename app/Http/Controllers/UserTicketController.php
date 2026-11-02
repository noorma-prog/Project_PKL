<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Ticket;
use App\Models\TicketRating;
use App\Models\TicketStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserTicketController extends Controller
{
    /**
     * Dashboard Pelapor (User)
     */
    public function dashboard()
    {
        $user = Auth::user();

        // Cek apakah ada laporan selesai yang belum dinilai
        $unratedTickets = $user->getUnratedCompletedTickets();

        // Statistik laporan milik pelapor
        $stats = [
            'total' => $user->tickets()->count(),
            'menunggu_verifikasi' => $user->tickets()->where('status', Ticket::STATUS_MENUNGGU_VERIFIKASI)->count(),
            'menunggu_konfirmasi_padal' => $user->tickets()->where('status', Ticket::STATUS_MENUNGGU_KONFIRMASI_PADAL)->count(),
            'dalam_penanganan' => $user->tickets()->where('status', Ticket::STATUS_DALAM_PENANGANAN)->count(),
            'selesai' => $user->tickets()->whereIn('status', [Ticket::STATUS_SELESAI, Ticket::STATUS_SELESAI_DINILAI])->count(),
            'ditolak' => $user->tickets()->whereIn('status', [Ticket::STATUS_DITOLAK_ADMIN, Ticket::STATUS_DITOLAK_PADAL])->count(),
            'butuh_rating' => $unratedTickets->count(),
        ];

        $recentTickets = $user->tickets()
            ->with(['category', 'padal', 'rating'])
            ->orderBySubmission()
            ->take(5)
            ->get();

        $scheduleTickets = $user->tickets()
            ->with(['category', 'padal'])
            ->whereDate('event_date', '>=', now()->toDateString())
            ->orderBySchedule()
            ->take(5)
            ->get();

        return view('user.dashboard', compact('stats', 'recentTickets', 'scheduleTickets', 'unratedTickets'));
    }

    /**
     * Daftar seluruh laporan milik pelapor
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = $user->tickets()->with(['category', 'padal', 'rating']);

        // Filter status
        if ($request->input('status') === 'selesai_semua') {
            $query->whereIn('status', [Ticket::STATUS_SELESAI, Ticket::STATUS_SELESAI_DINILAI]);
        } elseif ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter kategori
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Pencarian keyword (nomor tiket atau judul)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $tickets = ($request->query('sort') === 'schedule'
            ? $query->orderBySchedule()
            : $query->orderBySubmission())
            ->paginate(10)
            ->withQueryString();
        $categories = Category::orderBy('name')->get();
        $unratedCount = $user->getUnratedCompletedTickets()->count();

        return view('user.tickets.index', compact('tickets', 'categories', 'unratedCount'));
    }

    /**
     * Form pembuatan laporan baru
     */
    public function create()
    {
        $user = Auth::user();

        // ATURAN PENTING: Periksa apakah user masih punya laporan selesai yang belum dinilai
        if ($user->hasUnratedCompletedTickets()) {
            return redirect()->route('user.tickets.index')->with(
                'error',
                'Anda memiliki laporan yang telah selesai namun belum diberi rating. Harap berikan penilaian pelayanan terlebih dahulu sebelum membuat laporan baru.'
            );
        }

        return view('user.tickets.create');
    }

    /**
     * Simpan laporan baru
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Validasi aturan: Tolak pembuatan laporan baru jika masih ada yang belum dinilai
        if ($user->hasUnratedCompletedTickets()) {
            return redirect()->route('user.tickets.index')->with(
                'error',
                'Pembuatan laporan ditolak. Anda masih mempunyai laporan selesai yang belum diberi rating dan ulasan.'
            );
        }

        $validated = $request->validate([
            'category' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'event_date' => ['required', 'date_format:Y-m-d'],
            'event_start_time' => ['required', 'date_format:H:i'],
            'event_end_time' => ['required', 'date_format:H:i', 'after:event_start_time'],
            'description' => ['required', 'string'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'], // Max 5MB
        ], [
            'category.required' => 'Kategori gangguan IT wajib diisi.',
            'title.required' => 'Judul laporan permasalahan wajib diisi.',
            'location.required' => 'Lokasi ruangan / satuan kerja wajib diisi.',
            'event_date.required' => 'Tanggal kegiatan wajib diisi.',
            'event_start_time.required' => 'Jam mulai kegiatan wajib diisi.',
            'event_end_time.required' => 'Jam selesai kegiatan wajib diisi.',
            'event_end_time.after' => 'Jam selesai kegiatan harus setelah jam mulai.',
            'description.required' => 'Deskripsi detail permasalahan wajib diisi.',
            'attachment.mimes' => 'Format berkas lampiran harus berupa JPG, PNG, atau PDF.',
            'attachment.max' => 'Ukuran berkas lampiran maksimal 5 MB.',
        ]);

        return DB::transaction(function () use ($request, $validated, $user) {
            $user = $user->newQuery()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            if ($user->hasUnratedCompletedTickets()) {
                return redirect()->route('user.tickets.index')->with(
                    'error',
                    'Pembuatan laporan ditolak. Anda masih mempunyai laporan selesai yang belum diberi rating dan ulasan.'
                );
            }

            // Generate nomor tiket unik: TKT-YYYYMMDD-XXXX
            $datePrefix = 'TKT-' . date('Ymd') . '-';
            do {
                $ticketNumber = $datePrefix . strtoupper(Str::random(4));
            } while (Ticket::where('ticket_number', $ticketNumber)->exists());

            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $attachmentPath = $request->file('attachment')->store('attachments/tickets', 'public');
            }

            $category = Category::firstOrCreate(
                ['name' => trim($validated['category'])],
                [
                    'slug' => Str::slug($validated['category']),
                    'description' => null,
                    'icon' => 'bi-tag',
                    'is_active' => true,
                ]
            );

            $ticket = Ticket::create([
                'ticket_number' => $ticketNumber,
                'user_id' => $user->id,
                'category_id' => $category->id,
                'title' => $validated['title'],
                'location' => $validated['location'],
                'event_date' => $validated['event_date'],
                'event_start_time' => $validated['event_start_time'],
                'event_end_time' => $validated['event_end_time'],
                'description' => $validated['description'],
                'attachment' => $attachmentPath,
                'status' => Ticket::STATUS_MENUNGGU_VERIFIKASI,
            ]);

            // Catat riwayat status pertama
            TicketStatusHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'status_from' => null,
                'status_to' => Ticket::STATUS_MENUNGGU_VERIFIKASI,
                'notes' => 'Laporan permasalahan baru berhasil dibuat oleh pelapor (' . $user->name . ').',
            ]);

            return redirect()->route('user.tickets.show', $ticket->id)
                ->with('success', 'Laporan berhasil diajukan dengan nomor tiket: ' . $ticket->ticket_number . '. Menunggu verifikasi dari Administrator TIK.');
        });
    }

    /**
     * Detail laporan milik pelapor
     */
    public function show($id)
    {
        $user = Auth::user();

        // User hanya boleh melihat tiket miliknya sendiri
        $ticket = Ticket::with(['category', 'padal.leader', 'padal.members', 'diperiksaOleh', 'ditugaskanOleh', 'rating', 'histories.user'])
            ->where('user_id', $user->id)
            ->findOrFail($id);

        return view('user.tickets.show', compact('ticket'));
    }

    /**
     * Berikan rating dan ulasan terhadap Padal setelah tiket selesai
     */
    public function rate(Request $request, $id)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'review' => ['nullable', 'string', 'max:1000'],
        ], [
            'rating.required' => 'Pilih nilai bintang kepuasan pelayanan (1 - 5).',
            'rating.between' => 'Nilai rating harus berada di antara 1 sampai 5 bintang.',
            'review.max' => 'Ulasan maksimal 1000 karakter.',
        ]);

        return DB::transaction(function () use ($id, $user, $validated) {
            $ticket = Ticket::where('user_id', $user->id)
                ->whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($ticket->status !== Ticket::STATUS_SELESAI) {
                return back()->with('error', 'Rating hanya dapat diberikan untuk laporan yang telah berstatus Selesai.');
            }

            if ($ticket->rating()->exists()) {
                return back()->with('error', 'Anda sudah memberikan penilaian untuk laporan ini.');
            }

            if (!$ticket->padal_id) {
                return back()->with('error', 'Rating tidak dapat dikirim karena tiket belum memiliki Padal penanggung jawab.');
            }

            TicketRating::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'padal_id' => $ticket->padal_id, // Menilai kinerja Padal penanggung jawab
                'rating' => $validated['rating'],
                'review' => $validated['review'] ?? null,
            ]);

            // Perbarui status tiket menjadi 'selesai_dinilai'
            $oldStatus = $ticket->status;
            $ticket->status = Ticket::STATUS_SELESAI_DINILAI;
            $ticket->save();

            // Log riwayat
            TicketStatusHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'status_from' => $oldStatus,
                'status_to' => Ticket::STATUS_SELESAI_DINILAI,
                'notes' => 'Pelapor memberikan penilaian pelayanan: ' . $validated['rating'] . ' Bintang. Ulasan: ' . ($validated['review'] ?: '-'),
            ]);

            return redirect()->route('user.tickets.show', $ticket->id)
                ->with('success', 'Terima kasih atas penilaian pelayanan Anda! Penilaian Anda telah tersimpan dan Anda kini dapat membuat laporan baru jika diperlukan.');
        });
    }
}
