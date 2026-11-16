<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Padal;
use App\Models\Ticket;
use App\Models\TicketRating;
use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserCreateTicketValidationTest extends TestCase
{
    protected const BLOCKING_MESSAGE = 'Anda masih memiliki laporan yang belum selesai ditangani atau belum diberikan penilaian. Silakan selesaikan proses laporan sebelumnya terlebih dahulu sebelum membuat laporan baru.';

    protected User $user;
    protected User $otherUser;
    protected User $padalUser;
    protected Padal $padal;
    protected Category $category;

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

        $this->user = $this->createUser(User::ROLE_USER, 'pelapor1');
        $this->otherUser = $this->createUser(User::ROLE_USER, 'pelapor2');
        $this->padalUser = $this->createUser(User::ROLE_PADAL, 'padal-user');

        $this->padal = Padal::create([
            'user_id' => $this->padalUser->id,
            'nama_padal' => 'Unit Piket TIK',
            'deskripsi' => 'Unit piket penanganan IT',
            'status' => 'aktif',
        ]);

        $this->category = Category::create([
            'name' => 'Gangguan Jaringan Internet',
            'slug' => 'gangguan-jaringan-internet',
            'is_active' => true,
        ]);
    }

    /**
     * Kondisi 1: User memiliki tiket Menunggu Verifikasi Admin -> tidak boleh membuat laporan baru.
     */
    public function test_user_with_ticket_menunggu_verifikasi_cannot_create_new_report(): void
    {
        $this->createTicket($this->user, Ticket::STATUS_MENUNGGU_VERIFIKASI);

        $this->assertFalse($this->user->canCreateNewTicket());

        // Cek Dashboard User
        $dashboardResponse = $this->actingAs($this->user)->get(route('user.dashboard'));
        $dashboardResponse->assertOk();
        $dashboardResponse->assertSee(self::BLOCKING_MESSAGE);
        $dashboardResponse->assertSee('btn-disabled');
        $dashboardResponse->assertSee('Lihat Laporan Saya');

        // Buka form create: diblokir
        $this->get(route('user.tickets.create'))
            ->assertRedirect(route('user.tickets.index'))
            ->assertSessionHas('error', self::BLOCKING_MESSAGE);

        // Submit via POST: ditolak
        $this->post(route('user.tickets.store'), $this->ticketPayload())
            ->assertRedirect(route('user.tickets.index'))
            ->assertSessionHas('error', self::BLOCKING_MESSAGE);

        $this->assertSame(1, $this->user->tickets()->count());
    }

    /**
     * Kondisi 2: User memiliki tiket Dalam Penanganan -> tidak boleh membuat laporan baru.
     */
    public function test_user_with_ticket_dalam_penanganan_cannot_create_new_report(): void
    {
        $this->createTicket($this->user, Ticket::STATUS_DALAM_PENANGANAN);

        $this->assertFalse($this->user->canCreateNewTicket());

        // Cek Dashboard User
        $dashboardResponse = $this->actingAs($this->user)->get(route('user.dashboard'));
        $dashboardResponse->assertOk();
        $dashboardResponse->assertSee(self::BLOCKING_MESSAGE);
        $dashboardResponse->assertSee('btn-disabled');

        // Buka form create: diblokir
        $this->get(route('user.tickets.create'))
            ->assertRedirect(route('user.tickets.index'))
            ->assertSessionHas('error', self::BLOCKING_MESSAGE);

        // Submit via POST: ditolak
        $this->post(route('user.tickets.store'), $this->ticketPayload())
            ->assertRedirect(route('user.tickets.index'))
            ->assertSessionHas('error', self::BLOCKING_MESSAGE);

        $this->assertSame(1, $this->user->tickets()->count());
    }

    /**
     * Kondisi 3: User memiliki tiket Selesai tetapi belum di-rating -> tidak boleh membuat laporan baru.
     */
    public function test_user_with_ticket_selesai_unrated_cannot_create_new_report(): void
    {
        $ticket = $this->createTicket($this->user, Ticket::STATUS_SELESAI);

        $this->assertFalse($this->user->canCreateNewTicket());
        $this->assertTrue($this->user->hasUnratedCompletedTickets());

        // Cek Dashboard User
        $dashboardResponse = $this->actingAs($this->user)->get(route('user.dashboard'));
        $dashboardResponse->assertOk();
        $dashboardResponse->assertSee(self::BLOCKING_MESSAGE);
        $dashboardResponse->assertSee('btn-disabled');
        $dashboardResponse->assertSee('Beri Penilaian #' . $ticket->ticket_number);

        // Buka form create: diblokir
        $this->get(route('user.tickets.create'))
            ->assertRedirect(route('user.tickets.index'))
            ->assertSessionHas('error', self::BLOCKING_MESSAGE);

        // Submit via POST: ditolak
        $this->post(route('user.tickets.store'), $this->ticketPayload())
            ->assertRedirect(route('user.tickets.index'))
            ->assertSessionHas('error', self::BLOCKING_MESSAGE);

        $this->assertSame(1, $this->user->tickets()->count());
    }

    /**
     * Kondisi 4: User memiliki beberapa tiket dan salah satunya masih belum selesai -> tidak boleh membuat laporan baru.
     */
    public function test_user_with_multiple_tickets_one_unfinished_cannot_create_new_report(): void
    {
        // Tiket 1 sudah selesai dan sudah dinilai
        $ticket1 = $this->createTicket($this->user, Ticket::STATUS_SELESAI);
        TicketRating::create([
            'ticket_id' => $ticket1->id,
            'user_id' => $this->user->id,
            'padal_id' => $this->padal->id,
            'rating' => 5,
            'review' => 'Pelayanan memuaskan',
        ]);
        $ticket1->update(['status' => Ticket::STATUS_SELESAI_DINILAI]);

        // Tiket 2 masih dalam penanganan
        $this->createTicket($this->user, Ticket::STATUS_DALAM_PENANGANAN);

        $this->assertFalse($this->user->canCreateNewTicket());

        // Cek Dashboard User
        $dashboardResponse = $this->actingAs($this->user)->get(route('user.dashboard'));
        $dashboardResponse->assertOk();
        $dashboardResponse->assertSee(self::BLOCKING_MESSAGE);
        $dashboardResponse->assertSee('btn-disabled');

        // Buka form create: diblokir
        $this->get(route('user.tickets.create'))
            ->assertRedirect(route('user.tickets.index'))
            ->assertSessionHas('error', self::BLOCKING_MESSAGE);

        // Submit via POST: ditolak
        $this->post(route('user.tickets.store'), $this->ticketPayload())
            ->assertRedirect(route('user.tickets.index'))
            ->assertSessionHas('error', self::BLOCKING_MESSAGE);

        $this->assertSame(2, $this->user->tickets()->count());
    }

    /**
     * Kondisi 5: Semua tiket user sudah selesai dan sudah diberikan rating -> boleh membuat laporan baru.
     */
    public function test_user_with_all_tickets_completed_and_rated_can_create_new_report(): void
    {
        $ticket1 = $this->createTicket($this->user, Ticket::STATUS_SELESAI);
        TicketRating::create([
            'ticket_id' => $ticket1->id,
            'user_id' => $this->user->id,
            'padal_id' => $this->padal->id,
            'rating' => 5,
            'review' => 'Sangat baik',
        ]);
        $ticket1->update(['status' => Ticket::STATUS_SELESAI_DINILAI]);

        $ticket2 = $this->createTicket($this->user, Ticket::STATUS_SELESAI);
        TicketRating::create([
            'ticket_id' => $ticket2->id,
            'user_id' => $this->user->id,
            'padal_id' => $this->padal->id,
            'rating' => 4,
            'review' => 'Baik',
        ]);
        $ticket2->update(['status' => Ticket::STATUS_SELESAI_DINILAI]);

        $this->assertTrue($this->user->canCreateNewTicket());

        // Cek Dashboard User: tidak ada pesan peringatan, tombol aktif
        $dashboardResponse = $this->actingAs($this->user)->get(route('user.dashboard'));
        $dashboardResponse->assertOk();
        $dashboardResponse->assertDontSee(self::BLOCKING_MESSAGE);
        $dashboardResponse->assertDontSee('btn-disabled');
        $dashboardResponse->assertSee(route('user.tickets.create'));

        // Akses create form: berhasil
        $this->get(route('user.tickets.create'))->assertOk();

        // Submit tiket baru: berhasil
        $this->post(route('user.tickets.store'), $this->ticketPayload())
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(3, $this->user->tickets()->count());
    }

    /**
     * Kondisi 6: User lain yang seluruh tiketnya sudah selesai dan sudah di-rating -> tetap boleh membuat laporan baru.
     */
    public function test_user_b_is_not_blocked_by_user_a_pending_tickets(): void
    {
        // User A memiliki tiket dalam penanganan (terblokir)
        $this->createTicket($this->user, Ticket::STATUS_DALAM_PENANGANAN);
        $this->assertFalse($this->user->canCreateNewTicket());

        // User B memiliki tiket yang sudah selesai & dirating
        $ticketB = $this->createTicket($this->otherUser, Ticket::STATUS_SELESAI);
        TicketRating::create([
            'ticket_id' => $ticketB->id,
            'user_id' => $this->otherUser->id,
            'padal_id' => $this->padal->id,
            'rating' => 5,
            'review' => 'User B puas',
        ]);
        $ticketB->update(['status' => Ticket::STATUS_SELESAI_DINILAI]);

        $this->assertTrue($this->otherUser->canCreateNewTicket());

        // User B login dan membuat tiket baru: harus berhasil
        $this->actingAs($this->otherUser);
        $this->get(route('user.tickets.create'))->assertOk();
        $this->post(route('user.tickets.store'), $this->ticketPayload())
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(2, $this->otherUser->tickets()->count());

        // User A tetap diblokir
        $this->actingAs($this->user);
        $this->get(route('user.tickets.create'))
            ->assertRedirect(route('user.tickets.index'))
            ->assertSessionHas('error', self::BLOCKING_MESSAGE);
    }

    /**
     * Kondisi 7: User mencoba membuka URL pembuatan laporan secara langsung ketika masih memiliki tiket yang belum memenuhi ketentuan -> tetap diblokir.
     */
    public function test_direct_url_access_is_blocked_when_tickets_not_cleared(): void
    {
        $this->createTicket($this->user, Ticket::STATUS_MENUNGGU_VERIFIKASI);

        $this->actingAs($this->user)
            ->get('/user/tickets/create')
            ->assertRedirect(route('user.tickets.index'))
            ->assertSessionHas('error', self::BLOCKING_MESSAGE);

        $this->assertSame(1, $this->user->tickets()->count());
    }

    /**
     * Kondisi Spesifik Screenshot: 1 Verifikasi, 2 Penanganan, 0 Selesai -> tombol disabled & create diblokir.
     */
    public function test_exact_screenshot_scenario_blocks_new_report(): void
    {
        // Sesuai screenshot:
        // 1 tiket Menunggu Verifikasi Admin
        $this->createTicket($this->user, Ticket::STATUS_MENUNGGU_VERIFIKASI, 'mjhj', 'reskrim');
        // 2 tiket Dalam Penanganan
        $this->createTicket($this->user, Ticket::STATUS_DALAM_PENANGANAN, 'bsnsnsnanak', 'reskrim');
        $this->createTicket($this->user, Ticket::STATUS_DALAM_PENANGANAN, 'lcd rusak', 'reskrim');

        $this->assertFalse($this->user->canCreateNewTicket());

        $response = $this->actingAs($this->user)->get(route('user.dashboard'));
        $response->assertOk();
        // Memastikan pesan pemberitahuan muncul
        $response->assertSee(self::BLOCKING_MESSAGE);
        // Memastikan tautan ke Laporan Saya ada
        $response->assertSee(route('user.tickets.index'));
        // Memastikan tombol Buat Laporan Baru disabled dan tidak bisa dipakai
        $response->assertSee('btn-disabled');
        $response->assertSee('disabled');
        $response->assertDontSee('<a href="' . route('user.tickets.create') . '" class="btn btn-primary">', false);

        // Akses langsung juga tetap diblokir
        $this->get(route('user.tickets.create'))
            ->assertRedirect(route('user.tickets.index'))
            ->assertSessionHas('error', self::BLOCKING_MESSAGE);
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

    private function createTicket(User $owner, string $status, string $title = 'Tiket Gangguan', string $location = 'Ruang IT'): Ticket
    {
        return Ticket::create([
            'ticket_number' => 'TKT-' . strtoupper(bin2hex(random_bytes(4))),
            'user_id' => $owner->id,
            'category_id' => $this->category->id,
            'padal_id' => $this->padal->id,
            'title' => $title,
            'location' => $location,
            'description' => 'Deskripsi gangguan IT.',
            'status' => $status,
        ]);
    }

    private function ticketPayload(): array
    {
        return [
            'category' => 'Gangguan Jaringan Internet',
            'title' => 'Koneksi switch mati total',
            'location' => 'Gedung A Ruang Server',
            'event_date' => now()->addDays(2)->toDateString(),
            'event_start_time' => '08:00',
            'event_end_time' => '10:00',
            'description' => 'Switch unmanaged mati mendadak.',
        ];
    }
}

