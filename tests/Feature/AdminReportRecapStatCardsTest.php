<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Padal;
use App\Models\Ticket;
use App\Models\TicketRating;
use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminReportRecapStatCardsTest extends TestCase
{
    protected User $admin;
    protected User $reporter;
    protected User $padalUser;
    protected Padal $padal;
    protected Category $categoryA;
    protected Category $categoryB;

    protected Ticket $ticketSelesaiDinilai;
    protected Ticket $ticketSelesai;
    protected Ticket $ticketPenanganan;
    protected Ticket $ticketVerifikasi;
    protected Ticket $ticketDitolak;

    public function createApplication(): Application
    {
        $app = parent::createApplication();
        $app['config']->set([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.foreign_key_constraints' => true,
        ]);

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        DB::purge('sqlite');
        Artisan::call('migrate', ['--force' => true, '--no-interaction' => true]);

        Carbon::setTestNow('2026-11-16 10:00:00');

        $this->admin = $this->createUser(User::ROLE_ADMIN, 'admin');
        $this->reporter = $this->createUser(User::ROLE_USER, 'reporter');
        $this->padalUser = $this->createUser(User::ROLE_PADAL, 'padal');

        $this->padal = Padal::create([
            'user_id' => $this->padalUser->id,
            'nama_padal' => 'Unit Piket TIK',
            'deskripsi' => 'Unit piket penanganan IT',
            'status' => 'aktif',
        ]);

        $this->categoryA = Category::create([
            'name' => 'Gangguan Jaringan Internet',
            'slug' => 'gangguan-jaringan-internet',
            'is_active' => true,
        ]);

        $this->categoryB = Category::create([
            'name' => 'Kerusakan Hardware PC',
            'slug' => 'kerusakan-hardware-pc',
            'is_active' => true,
        ]);

        // Buat 5 tiket pada bulan berjalan (November 2026)
        // 1. Selesai & Dinilai (Kategori A)
        $this->ticketSelesaiDinilai = $this->createTicket($this->categoryA, Ticket::STATUS_SELESAI_DINILAI, 'Koneksi LAN Putus', Carbon::parse('2026-11-02 08:00:00'));
        TicketRating::create([
            'ticket_id' => $this->ticketSelesaiDinilai->id,
            'user_id' => $this->reporter->id,
            'padal_id' => $this->padal->id,
            'rating' => 5,
            'review' => 'Cepat selesai',
        ]);

        // 2. Selesai tapi belum dinilai (Kategori A)
        $this->ticketSelesai = $this->createTicket($this->categoryA, Ticket::STATUS_SELESAI, 'Printer Macet', Carbon::parse('2026-11-05 09:00:00'));

        // 3. Dalam Penanganan (Kategori A)
        $this->ticketPenanganan = $this->createTicket($this->categoryA, Ticket::STATUS_DALAM_PENANGANAN, 'Switch Lantai 2 Mati', Carbon::parse('2026-11-10 10:00:00'));

        // 4. Menunggu Verifikasi (Kategori B)
        $this->ticketVerifikasi = $this->createTicket($this->categoryB, Ticket::STATUS_MENUNGGU_VERIFIKASI, 'Komputer SPKT Rusak', Carbon::parse('2026-11-12 11:00:00'));

        // 5. Ditolak Admin (Kategori B)
        $this->ticketDitolak = $this->createTicket($this->categoryB, Ticket::STATUS_DITOLAK_ADMIN, 'Pengajuan Non-IT', Carbon::parse('2026-11-14 12:00:00'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /**
     * Pengujian 1: Klik kartu Total -> seluruh laporan sesuai filter ditampilkan, kartu Total aktif.
     */
    public function test_click_total_card_shows_all_reports(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.reports.recap', [
            'date_from' => '2026-11-01',
            'date_to' => '2026-11-30',
        ]));

        $response->assertOk();
        // Kartu Total memiliki border aktif
        $response->assertSee('data-status="" title="Tampilkan seluruh laporan"', false);
        // Seluruh 5 laporan tampil di tabel
        $response->assertSee($this->ticketSelesaiDinilai->ticket_number);
        $response->assertSee($this->ticketSelesai->ticket_number);
        $response->assertSee($this->ticketPenanganan->ticket_number);
        $response->assertSee($this->ticketVerifikasi->ticket_number);
        $response->assertSee($this->ticketDitolak->ticket_number);

        // Angka pada kartu statistik
        $response->assertSee('<div class="stat-value">5</div>', false); // Total
        $response->assertSee('<div class="stat-value">2</div>', false); // Selesai
        $response->assertSee('<div class="stat-value">1</div>', false); // Penanganan & Verifikasi
    }

    /**
     * Pengujian 2: Klik kartu Selesai -> hanya laporan selesai yang ditampilkan.
     */
    public function test_click_selesai_card_shows_only_completed_reports(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.reports.recap', [
            'date_from' => '2026-11-01',
            'date_to' => '2026-11-30',
            'status' => 'selesai',
        ]));

        $response->assertOk();
        // Kartu Selesai memiliki kelas active
        $response->assertSee('data-status="selesai" title="Filter laporan selesai"', false);
        // Hanya laporan selesai & selesai_dinilai yang tampil
        $response->assertSee($this->ticketSelesaiDinilai->ticket_number);
        $response->assertSee($this->ticketSelesai->ticket_number);
        // Laporan dalam penanganan, verifikasi, dan ditolak TIDAK tampil di tabel
        $response->assertDontSee($this->ticketPenanganan->ticket_number);
        $response->assertDontSee($this->ticketVerifikasi->ticket_number);
        $response->assertDontSee($this->ticketDitolak->ticket_number);

        // Jumlah kartu statistik tetap merefleksikan total periode (Total: 5, Selesai: 2, Penanganan: 1, Verifikasi: 1)
        $response->assertSee('<div class="stat-value">5</div>', false);
        $response->assertSee('<div class="stat-value">2</div>', false);
    }

    /**
     * Pengujian 3: Klik kartu Penanganan -> hanya laporan dalam penanganan yang ditampilkan.
     */
    public function test_click_penanganan_card_shows_only_in_progress_reports(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.reports.recap', [
            'date_from' => '2026-11-01',
            'date_to' => '2026-11-30',
            'status' => 'dalam_penanganan',
        ]));

        $response->assertOk();
        // Hanya laporan dalam penanganan yang tampil
        $response->assertSee($this->ticketPenanganan->ticket_number);
        $response->assertDontSee($this->ticketSelesaiDinilai->ticket_number);
        $response->assertDontSee($this->ticketSelesai->ticket_number);
        $response->assertDontSee($this->ticketVerifikasi->ticket_number);
    }

    /**
     * Pengujian 4: Klik kartu Verifikasi -> hanya laporan menunggu verifikasi yang ditampilkan.
     */
    public function test_click_verifikasi_card_shows_only_pending_verification_reports(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.reports.recap', [
            'date_from' => '2026-11-01',
            'date_to' => '2026-11-30',
            'status' => 'menunggu_verifikasi',
        ]));

        $response->assertOk();
        // Hanya laporan menunggu verifikasi yang tampil
        $response->assertSee($this->ticketVerifikasi->ticket_number);
        $response->assertDontSee($this->ticketSelesaiDinilai->ticket_number);
        $response->assertDontSee($this->ticketPenanganan->ticket_number);
    }

    /**
     * Pengujian 5: Pindah dari satu kartu ke kartu lainnya -> daftar laporan ikut berubah.
     */
    public function test_switching_cards_changes_ticket_list_accordingly(): void
    {
        $this->actingAs($this->admin);

        // 1. Awalnya di kartu Penanganan
        $response1 = $this->get(route('admin.reports.recap', ['status' => 'dalam_penanganan']));
        $response1->assertSee($this->ticketPenanganan->ticket_number);
        $response1->assertDontSee($this->ticketVerifikasi->ticket_number);

        // 2. Berpindah ke kartu Verifikasi
        $response2 = $this->get(route('admin.reports.recap', ['status' => 'menunggu_verifikasi']));
        $response2->assertSee($this->ticketVerifikasi->ticket_number);
        $response2->assertDontSee($this->ticketPenanganan->ticket_number);

        // 3. Berpindah kembali ke kartu Total
        $response3 = $this->get(route('admin.reports.recap', ['status' => '']));
        $response3->assertSee($this->ticketPenanganan->ticket_number);
        $response3->assertSee($this->ticketVerifikasi->ticket_number);
    }

    /**
     * Pengujian 6: Ubah periode tanggal -> jumlah kartu dan daftar laporan diperbarui.
     */
    public function test_changing_date_range_updates_counts_and_tickets(): void
    {
        $this->actingAs($this->admin);

        // Filter hanya tanggal 1 - 3 November 2026 (hanya ada ticketSelesaiDinilai)
        $response = $this->get(route('admin.reports.recap', [
            'date_from' => '2026-11-01',
            'date_to' => '2026-11-03',
        ]));

        $response->assertOk();
        $response->assertSee($this->ticketSelesaiDinilai->ticket_number);
        $response->assertDontSee($this->ticketSelesai->ticket_number);
        $response->assertDontSee($this->ticketPenanganan->ticket_number);

        // Total harus 1, Selesai 1, Penanganan 0, Verifikasi 0
        $response->assertSee('<div class="stat-value">1</div>', false); // Total & Selesai
        $response->assertSee('<div class="stat-value">0</div>', false); // Penanganan & Verifikasi
    }

    /**
     * Pengujian 7: Pilih kategori tertentu -> fungsi kartu tetap mengikuti kategori tersebut.
     */
    public function test_category_filter_is_retained_when_filtering_by_cards(): void
    {
        $this->actingAs($this->admin);

        // Filter Kategori B (hanya ticketVerifikasi dan ticketDitolak)
        $response = $this->get(route('admin.reports.recap', [
            'category_id' => $this->categoryB->id,
            'status' => 'menunggu_verifikasi',
        ]));

        $response->assertOk();
        $response->assertSee($this->ticketVerifikasi->ticket_number);
        $response->assertDontSee($this->ticketDitolak->ticket_number);
        $response->assertDontSee($this->ticketPenanganan->ticket_number);

        // Jika filter kartu Selesai pada Kategori B (tidak ada yang selesai):
        $responseEmpty = $this->get(route('admin.reports.recap', [
            'category_id' => $this->categoryB->id,
            'status' => 'selesai',
        ]));
        $responseEmpty->assertOk();
        $responseEmpty->assertSee('Tidak ada laporan yang telah selesai pada periode dan kriteria filter yang dipilih.');
    }

    /**
     * Pengujian 8: Pastikan jumlah pada kartu sesuai dengan jumlah data hasil filter.
     */
    public function test_card_counts_match_table_record_counts(): void
    {
        $this->actingAs($this->admin);

        // Selesai: count 2
        $resSelesai = $this->get(route('admin.reports.recap', ['status' => 'selesai']));
        $resSelesai->assertOk();
        $viewDataSelesai = $resSelesai->viewData('tickets');
        $this->assertCount(2, $viewDataSelesai);

        // Penanganan: count 1
        $resPenanganan = $this->get(route('admin.reports.recap', ['status' => 'dalam_penanganan']));
        $viewDataPenanganan = $resPenanganan->viewData('tickets');
        $this->assertCount(1, $viewDataPenanganan);

        // Verifikasi: count 1
        $resVerifikasi = $this->get(route('admin.reports.recap', ['status' => 'menunggu_verifikasi']));
        $viewDataVerifikasi = $resVerifikasi->viewData('tickets');
        $this->assertCount(1, $viewDataVerifikasi);

        // Total: count 5
        $resTotal = $this->get(route('admin.reports.recap', ['status' => '']));
        $viewDataTotal = $resTotal->viewData('tickets');
        $this->assertCount(5, $viewDataTotal);
    }

    /**
     * Pengujian 9: Pastikan Preview PDF dan Unduh PDF tetap berfungsi normal.
     */
    public function test_preview_and_export_pdf_work_with_card_filters(): void
    {
        $this->actingAs($this->admin);

        // Preview PDF dengan filter status selesai
        $previewResponse = $this->get(route('admin.reports.recap.preview', [
            'date_from' => '2026-11-01',
            'date_to' => '2026-11-30',
            'status' => 'selesai',
        ]));
        $previewResponse->assertOk();
        $previewResponse->assertSee($this->ticketSelesaiDinilai->ticket_number);
        $previewResponse->assertDontSee($this->ticketPenanganan->ticket_number);

        // Unduh PDF
        $exportResponse = $this->get(route('admin.reports.recap.export', [
            'date_from' => '2026-11-01',
            'date_to' => '2026-11-30',
            'status' => 'selesai',
        ]));
        $exportResponse->assertOk();
        $this->assertStringContainsString('application/pdf', $exportResponse->headers->get('Content-Type'));
    }

    private function createUser(string $role, string $emailPrefix): User
    {
        return User::factory()->create([
            'name' => ucfirst($emailPrefix),
            'email' => $emailPrefix . '@example.test',
            'role' => $role,
            'is_active' => true,
            'approval_status' => User::APPROVAL_ACTIVE,
            'requires_email_verification' => false,
            'email_verified_at' => now(),
        ]);
    }

    private function createTicket(Category $category, string $status, string $title, Carbon $createdAt): Ticket
    {
        $ticket = Ticket::create([
            'ticket_number' => 'TKT-' . strtoupper(bin2hex(random_bytes(4))),
            'user_id' => $this->reporter->id,
            'category_id' => $category->id,
            'padal_id' => $this->padal->id,
            'title' => $title,
            'location' => 'Polda Sultra Ruang 1',
            'description' => 'Deskripsi tiket gangguan IT.',
            'status' => $status,
        ]);

        $ticket->created_at = $createdAt;
        $ticket->updated_at = $createdAt;
        $ticket->saveQuietly();

        return $ticket;
    }
}

