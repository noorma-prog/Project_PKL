<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Ticket;
use App\Models\TicketRating;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class LayanItWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected User $padal;
    protected User $teknisi;
    protected User $user;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::where('role', User::ROLE_ADMIN)->firstOrFail();
        $this->padal = User::where('role', User::ROLE_PADAL)->firstOrFail();
        $this->teknisi = User::where('role', User::ROLE_TEKNISI)->firstOrFail();
        $this->user = User::where('role', User::ROLE_USER)->firstOrFail();
        $this->category = Category::firstOrFail();
    }

    /**
     * 1. Halaman utama redirect ke login
     */
    public function test_home_redirects_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect(route('login'));
    }

    /**
     * 2. Login dan redirect sesuai role
     */
    public function test_user_can_login_and_redirects_to_user_dashboard(): void
    {
        $response = $this->post(route('login.post'), [
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('user.dashboard'));
        $this->assertAuthenticatedAs($this->user);
    }

    public function test_admin_can_login_and_redirects_to_admin_dashboard(): void
    {
        $response = $this->post(route('login.post'), [
            'email' => $this->admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->admin);
    }

    /**
     * 3. RoleMiddleware membatasi hak akses
     */
    public function test_user_cannot_access_admin_dashboard(): void
    {
        $response = $this->actingAs($this->user)->get(route('admin.dashboard'));
        $response->assertStatus(403);
    }

    public function test_padal_cannot_access_admin_dashboard(): void
    {
        $response = $this->actingAs($this->padal)->get(route('admin.dashboard'));
        $response->assertStatus(403);
    }

    /**
     * 4. User membuat tiket laporan baru
     */
    public function test_user_can_create_ticket(): void
    {
        $response = $this->actingAs($this->user)->post(route('user.tickets.store'), [
            'category_id' => $this->category->id,
            'title' => 'Komputer PC SPKT Tidak Bisa Nyala',
            'location' => 'Ruang Pelayanan SPKT Lantai 1',
            'description' => 'Saat tombol power ditekan, lampu indikator mati dan kipas tidak berputar.',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('tickets', [
            'user_id' => $this->user->id,
            'title' => 'Komputer PC SPKT Tidak Bisa Nyala',
            'status' => Ticket::STATUS_MENUNGGU_VERIFIKASI,
        ]);
    }

    /**
     * 5. Alur lengkap: Buat tiket -> Verifikasi Admin -> Konfirmasi Padal & pilih Teknisi -> Selesai -> Rating -> Pembuatan tiket baru
     */
    public function test_complete_business_flow_and_rating_constraint(): void
    {
        // 1. User membuat tiket
        $ticket = Ticket::create([
            'ticket_number' => 'TKT-' . date('Ymd') . '-TEST1',
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'title' => 'Printer SPKT Paper Jam',
            'location' => 'Ruang SPKT',
            'description' => 'Kertas tersangkut di roller printer dinas.',
            'status' => Ticket::STATUS_MENUNGGU_VERIFIKASI,
        ]);

        // 2. Admin memverifikasi dan menugaskan ke Padal
        $verifyResponse = $this->actingAs($this->admin)->post(route('admin.tickets.verify-assign', $ticket->id), [
            'padal_id' => $this->padal->id,
            'admin_notes' => 'Tolong segera dicek siang ini.',
        ]);
        $verifyResponse->assertRedirect(route('admin.tickets.show', $ticket->id));

        $ticket->refresh();
        $this->assertEquals(Ticket::STATUS_MENUNGGU_KONFIRMASI_PADAL, $ticket->status);
        $this->assertEquals($this->padal->id, $ticket->padal_id);

        // 3. Padal menerima penugasan dan memilih anggota teknisi
        $acceptResponse = $this->actingAs($this->padal)->post(route('padal.tickets.accept', $ticket->id), [
            'technician_ids' => [$this->teknisi->id],
            'padal_notes' => 'Bawa roller cleaner ke lokasi.',
        ]);
        $acceptResponse->assertRedirect(route('padal.tickets.show', $ticket->id));

        $ticket->refresh();
        $this->assertEquals(Ticket::STATUS_DALAM_PENANGANAN, $ticket->status);
        $this->assertTrue($ticket->technicians->contains('id', $this->teknisi->id));

        // 4. Padal menyelesaikan tiket
        $completeResponse = $this->actingAs($this->padal)->post(route('padal.tickets.complete', $ticket->id), [
            'resolution_notes' => 'Roller printer telah dibersihkan dan kertas yang macet dikeluarkan. Hasil cetak normal.',
        ]);
        $completeResponse->assertRedirect(route('padal.tickets.show', $ticket->id));

        $ticket->refresh();
        $this->assertEquals(Ticket::STATUS_SELESAI, $ticket->status);

        // 5. ATURAN PENTING: User tidak boleh membuat tiket baru saat masih ada tiket selesai yang belum dinilai!
        $this->assertTrue($this->user->hasUnratedCompletedTickets());

        // Coba buka form create: harus diredirect
        $createPageResponse = $this->actingAs($this->user)->get(route('user.tickets.create'));
        $createPageResponse->assertRedirect(route('user.tickets.index'));
        $createPageResponse->assertSessionHas('error');

        // Coba post tiket baru: harus ditolak
        $storeBlockedResponse = $this->actingAs($this->user)->post(route('user.tickets.store'), [
            'category_id' => $this->category->id,
            'title' => 'Coba Buat Laporan Baru Saat Belum Rating',
            'location' => 'Ruang SPKT',
            'description' => 'Ini seharusnya ditolak sistem.',
        ]);
        $storeBlockedResponse->assertRedirect(route('user.tickets.index'));
        $storeBlockedResponse->assertSessionHas('error');

        // 6. User memberikan rating pada tiket yang selesai
        $rateResponse = $this->actingAs($this->user)->post(route('user.tickets.rate', $ticket->id), [
            'rating' => 5,
            'review' => 'Pelayanan cepat dan ramah, terima kasih tim TIK POLDA.',
        ]);
        $rateResponse->assertSessionHas('success');

        $ticket->refresh();
        $this->assertEquals(Ticket::STATUS_SELESAI_DINILAI, $ticket->status);
        $this->assertDatabaseHas('ticket_ratings', [
            'ticket_id' => $ticket->id,
            'user_id' => $this->user->id,
            'padal_id' => $this->padal->id,
            'rating' => 5,
        ]);

        // 7. ATURAN PENTING: Rating tidak boleh diberikan dua kali untuk laporan yang sama!
        $rateAgainResponse = $this->actingAs($this->user)->post(route('user.tickets.rate', $ticket->id), [
            'rating' => 4,
            'review' => 'Mencoba memberi rating kedua kali.',
        ]);
        $rateAgainResponse->assertSessionHas('error');

        // 8. ATURAN PENTING: Setelah rating berhasil, User KINI DAPAT membuat laporan baru
        $this->assertFalse($this->user->hasUnratedCompletedTickets());

        $newTicketResponse = $this->actingAs($this->user)->post(route('user.tickets.store'), [
            'category_id' => $this->category->id,
            'title' => 'Laporan Baru Setelah Rating Selesai',
            'location' => 'Ruang SPKT Lantai 1',
            'description' => 'Kini pelapor dapat membuat laporan baru dengan sukses.',
        ]);
        $newTicketResponse->assertSessionHas('success');
    }

    /**
     * 6. User tidak boleh memberi rating sebelum tiket selesai
     */
    public function test_user_cannot_rate_ticket_before_it_is_completed(): void
    {
        $ticket = Ticket::create([
            'ticket_number' => 'TKT-' . date('Ymd') . '-TEST2',
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'title' => 'Tiket Masih Dalam Penanganan',
            'location' => 'Ruang Ditlantas',
            'description' => 'Gangguan sinyal radio.',
            'status' => Ticket::STATUS_DALAM_PENANGANAN,
            'padal_id' => $this->padal->id,
        ]);

        $response = $this->actingAs($this->user)->post(route('user.tickets.rate', $ticket->id), [
            'rating' => 5,
            'review' => 'Coba rating saat tiket belum selesai.',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('ticket_ratings', ['ticket_id' => $ticket->id]);
    }
}
