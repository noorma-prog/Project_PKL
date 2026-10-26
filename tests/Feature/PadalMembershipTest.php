<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Padal;
use App\Models\PadalAnggota;
use App\Models\Ticket;
use App\Models\TicketTechnicianAssignment;
use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PadalMembershipTest extends TestCase
{
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

        $this->padalUser = $this->createUser(User::ROLE_PADAL, 'padal');
        $this->padal = Padal::create([
            'user_id' => $this->padalUser->id,
            'nama_padal' => 'Regu Uji',
            'deskripsi' => 'Tim pengujian',
            'status' => 'aktif',
        ]);
        $this->category = Category::create([
            'name' => 'Kategori Uji',
            'slug' => 'kategori-uji',
            'is_active' => true,
        ]);
    }

    public function test_padal_can_add_multiple_eligible_technicians_and_duplicate_members_are_rejected(): void
    {
        $first = $this->createUser(User::ROLE_TEKNISI, 'teknisi-satu');
        $second = $this->createUser(User::ROLE_TEKNISI, 'teknisi-dua');
        $inactive = $this->createUser(User::ROLE_TEKNISI, 'teknisi-nonaktif', ['is_active' => false]);
        $pelapor = $this->createUser(User::ROLE_USER, 'pelapor');

        $this->actingAs($this->padalUser)
            ->get(route('padal.members.index'))
            ->assertOk()
            ->assertSee($first->name)
            ->assertSee($second->name)
            ->assertDontSee($inactive->name)
            ->assertDontSee($pelapor->name);

        $this->post(route('padal.members.store'), [
            'technician_ids' => [$first->id, $second->id],
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseCount('padal_anggota', 2);
        $this->get(route('padal.members.index'))
            ->assertOk()
            ->assertSee($first->name)
            ->assertSee($second->name);
        $this->get(route('padal.dashboard'))
            ->assertOk()
            ->assertSee('Tambah Anggota')
            ->assertSee($first->name)
            ->assertSee($second->name);

        $this->from(route('padal.members.index'))
            ->post(route('padal.members.store'), ['technician_ids' => []])
            ->assertRedirect(route('padal.members.index'))
            ->assertSessionHasErrors('technician_ids');

        $this->from(route('padal.members.index'))
            ->post(route('padal.members.store'), ['technician_ids' => [$first->id]])
            ->assertRedirect(route('padal.members.index'))
            ->assertSessionHasErrors('technician_ids');

        $this->assertDatabaseCount('padal_anggota', 2);
    }

    public function test_invalid_roles_inactive_technicians_and_unauthorized_requests_are_rejected(): void
    {
        $inactive = $this->createUser(User::ROLE_TEKNISI, 'teknisi-mati', ['is_active' => false]);
        $pelapor = $this->createUser(User::ROLE_USER, 'pelapor-tidak-valid');

        $this->actingAs($this->padalUser)
            ->from(route('padal.members.index'))
            ->post(route('padal.members.store'), ['technician_ids' => [$inactive->id]])
            ->assertRedirect(route('padal.members.index'))
            ->assertSessionHasErrors('technician_ids');

        $this->from(route('padal.members.index'))
            ->post(route('padal.members.store'), ['technician_ids' => 'not-an-array'])
            ->assertRedirect(route('padal.members.index'))
            ->assertSessionHasErrors('technician_ids');
        $this->get(route('padal.members.index'))->assertOk();

        $this->post(route('padal.members.store'), ['technician_ids' => [$pelapor->id]])
            ->assertSessionHasErrors('technician_ids');

        $admin = $this->createUser(User::ROLE_ADMIN, 'admin');
        $this->actingAs($admin)
            ->post(route('padal.members.store'), ['technician_ids' => [$inactive->id]])
            ->assertForbidden();

        $this->assertDatabaseCount('padal_anggota', 0);
    }

    public function test_accepting_assignment_stores_technician_assignment_and_ticket_history(): void
    {
        $technician = $this->createUser(User::ROLE_TEKNISI, 'teknisi-ditugaskan');
        PadalAnggota::create([
            'padal_id' => $this->padal->id,
            'user_id' => $technician->id,
            'tanggal_bergabung' => now(),
        ]);
        $ticket = $this->createTicket(Ticket::STATUS_MENUNGGU_KONFIRMASI_PADAL);

        $this->actingAs($this->padalUser)
            ->post(route('padal.tickets.accept', $ticket->id), [
                'technician_ids' => [$technician->id],
                'padal_notes' => 'Periksa koneksi jaringan.',
            ])
            ->assertRedirect(route('padal.tickets.show', $ticket->id))
            ->assertSessionHas('success');

        $ticket->refresh();
        $this->assertSame(Ticket::STATUS_DALAM_PENANGANAN, $ticket->status);
        $this->assertTrue($ticket->technicians->contains('id', $technician->id));
        $this->assertDatabaseHas('ticket_technician_assignments', [
            'ticket_id' => $ticket->id,
            'user_id' => $technician->id,
            'released_at' => null,
        ]);
        $this->assertDatabaseHas('ticket_status_histories', [
            'ticket_id' => $ticket->id,
            'status_to' => Ticket::STATUS_DALAM_PENANGANAN,
        ]);

        $replacement = $this->createUser(User::ROLE_TEKNISI, 'teknisi-pengganti');
        PadalAnggota::create([
            'padal_id' => $this->padal->id,
            'user_id' => $replacement->id,
            'tanggal_bergabung' => now(),
        ]);
        $this->post(route('padal.tickets.technicians', $ticket->id), [
            'technician_ids' => [$replacement->id],
        ])->assertRedirect()->assertSessionHas('success');

        $releasedAssignment = TicketTechnicianAssignment::where('ticket_id', $ticket->id)
            ->where('user_id', $technician->id)
            ->firstOrFail();
        $this->assertNotNull($releasedAssignment->released_at);
        $this->assertDatabaseHas('ticket_technician_assignments', [
            'ticket_id' => $ticket->id,
            'user_id' => $replacement->id,
            'released_at' => null,
        ]);
        $this->assertSame(Ticket::STATUS_DALAM_PENANGANAN, $ticket->fresh()->status);

        $admin = $this->createUser(User::ROLE_ADMIN, 'admin-pantau-tiket');
        $this->actingAs($admin)
            ->get(route('admin.tickets.index'))
            ->assertOk()
            ->assertSee($ticket->ticket_number)
            ->assertSee('1 Teknisi');
        $this->get(route('admin.tickets.show', $ticket->id))
            ->assertOk()
            ->assertSee($replacement->name);
    }

    public function test_technician_only_sees_tickets_assigned_to_their_account_and_membership_removal_preserves_assignment_history(): void
    {
        $technician = $this->createUser(User::ROLE_TEKNISI, 'teknisi-lihat');
        PadalAnggota::create([
            'padal_id' => $this->padal->id,
            'user_id' => $technician->id,
            'tanggal_bergabung' => now(),
        ]);
        $assignedTicket = $this->createTicket(Ticket::STATUS_DALAM_PENANGANAN);
        $unassignedTicket = $this->createTicket(Ticket::STATUS_DALAM_PENANGANAN);
        TicketTechnicianAssignment::create([
            'ticket_id' => $assignedTicket->id,
            'user_id' => $technician->id,
            'assigned_by' => $this->padalUser->id,
            'assigned_at' => now(),
        ]);

        $this->actingAs($technician)
            ->get(route('teknisi.tickets.show', $assignedTicket->id))
            ->assertOk();
        $this->get(route('teknisi.tickets.show', $unassignedTicket->id))->assertNotFound();

        $this->actingAs($this->padalUser)
            ->delete(route('padal.members.destroy', $technician->id))
            ->assertRedirect()
            ->assertSessionHas('success');

        $admin = $this->createUser(User::ROLE_ADMIN, 'admin-penghapus-teknisi');
        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $technician->id))
            ->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $technician->id]);

        $this->assertDatabaseMissing('padal_anggota', [
            'padal_id' => $this->padal->id,
            'user_id' => $technician->id,
        ]);
        $this->assertDatabaseHas('ticket_technician_assignments', [
            'ticket_id' => $assignedTicket->id,
            'user_id' => $technician->id,
            'released_at' => null,
        ]);

        $this->actingAs($technician)
            ->get(route('teknisi.tickets.index'))
            ->assertOk()
            ->assertSee($assignedTicket->ticket_number)
            ->assertDontSee($unassignedTicket->ticket_number);
        $this->get(route('teknisi.dashboard'))
            ->assertOk()
            ->assertSee($assignedTicket->ticket_number)
            ->assertDontSee($unassignedTicket->ticket_number);
    }

    private function createUser(string $role, string $prefix, array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'name' => ucfirst(str_replace('-', ' ', $prefix)),
            'email' => $prefix.'@example.test',
            'role' => $role,
            'is_active' => true,
            'approval_status' => User::APPROVAL_ACTIVE,
            'requires_email_verification' => false,
            'email_verified_at' => now(),
        ], $attributes));
    }

    private function createTicket(string $status): Ticket
    {
        return Ticket::create([
            'ticket_number' => 'TKT-'.uniqid(),
            'user_id' => $this->createUser(User::ROLE_USER, 'reporter-'.uniqid())->id,
            'category_id' => $this->category->id,
            'padal_id' => $this->padal->id,
            'title' => 'Tiket Uji',
            'location' => 'Ruang Uji',
            'description' => 'Tiket untuk menguji alur assignment teknisi.',
            'status' => $status,
        ]);
    }
}
