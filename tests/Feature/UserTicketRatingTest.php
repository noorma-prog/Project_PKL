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

class UserTicketRatingTest extends TestCase
{
    protected User $user;

    protected User $otherUser;

    protected User $admin;

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

        $this->user = $this->createUser(User::ROLE_USER, 'reporter');
        $this->otherUser = $this->createUser(User::ROLE_USER, 'other-reporter');
        $this->admin = $this->createUser(User::ROLE_ADMIN, 'admin');
        $this->padalUser = $this->createUser(User::ROLE_PADAL, 'padal');
        $this->padal = Padal::create([
            'user_id' => $this->padalUser->id,
            'nama_padal' => 'Unit Padal Uji',
            'deskripsi' => 'Unit untuk pengujian rating',
            'status' => 'aktif',
        ]);
        $this->category = Category::create([
            'name' => 'Kategori Uji',
            'slug' => 'kategori-uji',
            'is_active' => true,
        ]);
    }

    public function test_rating_is_rejected_until_ticket_is_complete(): void
    {
        $ticket = $this->createTicket($this->user, Ticket::STATUS_DALAM_PENANGANAN);

        $this->actingAs($this->user)
            ->post(route('user.tickets.rate', $ticket->id), ['rating' => 5])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseCount('ticket_ratings', 0);
        $this->assertSame(Ticket::STATUS_DALAM_PENANGANAN, $ticket->fresh()->status);
        $this->get(route('user.tickets.show', $ticket->id))
            ->assertOk()
            ->assertDontSee('Beri Bintang Kepuasan Pelayanan');
    }

    public function test_user_can_rate_own_completed_ticket_once_and_admin_can_see_the_padal_rating(): void
    {
        $ticket = $this->createTicket($this->user, Ticket::STATUS_SELESAI);

        $this->actingAs($this->user)
            ->get(route('user.tickets.show', $ticket->id))
            ->assertOk()
            ->assertSee('Beri Bintang Kepuasan Pelayanan')
            ->assertSee('Kirim Penilaian Pelayanan');

        $this->post(route('user.tickets.rate', $ticket->id), [
            'rating' => 5,
            'review' => 'Penanganan cepat dan tuntas.',
        ])->assertRedirect(route('user.tickets.show', $ticket->id))
            ->assertSessionHas('success');

        $ticket->refresh();
        $rating = TicketRating::where('ticket_id', $ticket->id)->firstOrFail();
        $this->assertSame(Ticket::STATUS_SELESAI_DINILAI, $ticket->status);
        $this->assertSame($this->user->id, $rating->user_id);
        $this->assertSame($this->padal->id, $rating->padal_id);
        $this->assertSame(5, $rating->rating);
        $this->assertSame('Penanganan cepat dan tuntas.', $rating->review);

        $this->get(route('user.tickets.show', $ticket->id))
            ->assertOk()
            ->assertSee('Rating Diberikan')
            ->assertSee('5 / 5 Bintang')
            ->assertDontSee('Kirim Penilaian Pelayanan');

        $this->from(route('user.tickets.show', $ticket->id))
            ->post(route('user.tickets.rate', $ticket->id), [
                'rating' => 1,
                'review' => 'Rating duplikat',
            ])
            ->assertRedirect(route('user.tickets.show', $ticket->id))
            ->assertSessionHas('error');
        $this->assertDatabaseCount('ticket_ratings', 1);

        $this->actingAs($this->admin)
            ->get(route('admin.ratings.index'))
            ->assertOk()
            ->assertSee($ticket->ticket_number)
            ->assertSee($this->user->name)
            ->assertSee($this->padal->nama_padal)
            ->assertSee('Penanganan cepat dan tuntas.')
            ->assertSee('5.0 / 5.0');

        $this->actingAs($this->user)
            ->post(route('user.tickets.store'), [
                'category_id' => $this->category->id,
                'title' => 'Laporan setelah rating',
                'location' => 'Ruang uji',
                'description' => 'Laporan baru setelah rating sebelumnya berhasil disimpan.',
            ])
            ->assertRedirect();
        $this->assertSame(2, $this->user->tickets()->count());
    }

    public function test_user_cannot_rate_another_users_ticket_and_padal_cannot_submit_ratings(): void
    {
        $ticket = $this->createTicket($this->otherUser, Ticket::STATUS_SELESAI);

        $this->actingAs($this->user)
            ->post(route('user.tickets.rate', $ticket->id), ['rating' => 4])
            ->assertNotFound();

        $this->actingAs($this->padalUser)
            ->post(route('user.tickets.rate', $ticket->id), ['rating' => 4])
            ->assertForbidden();

        $this->assertDatabaseCount('ticket_ratings', 0);
        $this->assertSame(Ticket::STATUS_SELESAI, $ticket->fresh()->status);
    }

    public function test_unrated_completed_ticket_blocks_both_report_form_and_direct_submission(): void
    {
        $this->createTicket($this->user, Ticket::STATUS_SELESAI);

        $this->actingAs($this->user)
            ->get(route('user.tickets.create'))
            ->assertRedirect(route('user.tickets.index'))
            ->assertSessionHas('error');

        $this->post(route('user.tickets.store'), [
            'category_id' => $this->category->id,
            'title' => 'Laporan baru',
            'location' => 'Ruang uji',
            'description' => 'Permintaan baru yang seharusnya diblokir sampai rating diberikan.',
        ])->assertRedirect(route('user.tickets.index'))
            ->assertSessionHas('error');

        $this->assertSame(1, $this->user->tickets()->count());
    }

    public function test_dashboard_and_history_show_stored_rating_state(): void
    {
        $ticket = $this->createTicket($this->user, Ticket::STATUS_SELESAI);
        TicketRating::create([
            'ticket_id' => $ticket->id,
            'user_id' => $this->user->id,
            'padal_id' => $this->padal->id,
            'rating' => 4,
            'review' => 'Baik.',
        ]);
        $ticket->update(['status' => Ticket::STATUS_SELESAI_DINILAI]);

        $this->actingAs($this->user)
            ->get(route('user.dashboard'))
            ->assertOk()
            ->assertSee('Rating Diberikan')
            ->assertSee('Selesai & Telah Dinilai');
        $this->get(route('user.tickets.index'))
            ->assertOk()
            ->assertSee('Rating Diberikan (4/5)');
    }

    public function test_rating_value_is_validated_and_no_rating_is_saved_when_input_is_invalid(): void
    {
        $ticket = $this->createTicket($this->user, Ticket::STATUS_SELESAI);

        $this->actingAs($this->user)
            ->from(route('user.tickets.show', $ticket->id))
            ->post(route('user.tickets.rate', $ticket->id), ['rating' => 6])
            ->assertRedirect(route('user.tickets.show', $ticket->id))
            ->assertSessionHasErrors('rating');

        $this->assertDatabaseCount('ticket_ratings', 0);
        $this->assertSame(Ticket::STATUS_SELESAI, $ticket->fresh()->status);
    }

    private function createUser(string $role, string $emailPrefix): User
    {
        return User::factory()->create([
            'name' => ucfirst(str_replace('-', ' ', $emailPrefix)),
            'email' => $emailPrefix.'@example.test',
            'role' => $role,
            'is_active' => true,
            'approval_status' => User::APPROVAL_ACTIVE,
            'requires_email_verification' => false,
            'email_verified_at' => now(),
        ]);
    }

    private function createTicket(User $owner, string $status): Ticket
    {
        return Ticket::create([
            'ticket_number' => 'TKT-'.uniqid(),
            'user_id' => $owner->id,
            'category_id' => $this->category->id,
            'padal_id' => $this->padal->id,
            'title' => 'Tiket Uji Rating',
            'location' => 'Ruang Uji',
            'description' => 'Tiket untuk menguji alur rating User dan Padal.',
            'status' => $status,
        ]);
    }
}
