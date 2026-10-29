<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Padal;
use App\Models\PadalAnggota;
use App\Models\Ticket;
use App\Models\TicketRating;
use App\Models\TicketStatusHistory;
use App\Models\TicketTechnicianAssignment;
use App\Models\User;
use App\Notifications\AccountActivationNotification;
use App\Services\EmailVerificationSender;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class LayanItWorkflowTest extends TestCase
{
    protected User $admin;
    protected User $padal;
    protected User $teknisi;
    protected User $user;
    protected Padal $padalUnit;
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

        $this->admin = $this->createUser(User::ROLE_ADMIN, 'admin');
        $this->padal = $this->createUser(User::ROLE_PADAL, 'padal');
        $this->teknisi = $this->createUser(User::ROLE_TEKNISI, 'teknisi');
        $this->user = $this->createUser(User::ROLE_USER, 'reporter');
        $this->padalUnit = Padal::create([
            'user_id' => $this->padal->id,
            'nama_padal' => 'Unit Padal Uji',
            'deskripsi' => 'Unit Padal untuk pengujian workflow',
            'status' => 'aktif',
        ]);
        $this->category = Category::create([
            'name' => 'Kategori Uji',
            'slug' => 'kategori-uji',
            'is_active' => true,
        ]);
    }

    private function createUser(string $role, string $name): User
    {
        return User::factory()->create([
            'name' => ucfirst($name),
            'email' => $name.'@example.test',
            'role' => $role,
            'is_active' => true,
            'approval_status' => User::APPROVAL_ACTIVE,
            'requires_email_verification' => false,
            'email_verified_at' => now(),
        ]);
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
            'account_type' => User::ROLE_USER,
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('user.dashboard'));
        $this->assertAuthenticatedAs($this->user);
    }

    public function test_admin_can_login_and_redirects_to_admin_dashboard(): void
    {
        $response = $this->post(route('login.post'), [
            'account_type' => User::ROLE_ADMIN,
            'email' => $this->admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->admin);
    }

    public function test_wrong_password_is_rejected_without_disclosing_account_details(): void
    {
        $email = $this->user->email;
        RateLimiter::clear(strtolower($email).'|127.0.0.1');

        $response = $this->from(route('login'))->post(route('login.post'), [
            'account_type' => User::ROLE_USER,
            'email' => $email,
            'password' => 'password-yang-salah',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        $this->user->forceFill(['is_active' => false])->save();

        $response = $this->from(route('login'))->post(route('login.post'), [
            'account_type' => User::ROLE_USER,
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_padal_and_teknisi_login_redirect_to_their_verified_dashboards(): void
    {
        $padalResponse = $this->post(route('login.post'), [
            'account_type' => User::ROLE_PADAL,
            'email' => $this->padal->email,
            'password' => 'password',
        ]);
        $padalResponse->assertRedirect(route('padal.dashboard'));
        $this->assertAuthenticatedAs($this->padal);
        $this->post(route('logout'));

        $teknisiResponse = $this->post(route('login.post'), [
            'account_type' => User::ROLE_TEKNISI,
            'email' => $this->teknisi->email,
            'password' => 'password',
        ]);
        $teknisiResponse->assertRedirect(route('teknisi.dashboard'));
        $this->assertAuthenticatedAs($this->teknisi);
    }

    public function test_selected_account_type_must_match_database_role(): void
    {
        $response = $this->from(route('login'))->post(route('login.post'), [
            'account_type' => User::ROLE_ADMIN,
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('account_type');
        $this->assertGuest();
    }

    public function test_admin_can_create_and_manage_staff_accounts_with_activation_required(): void
    {
        $this->actingAs($this->admin);
        Notification::fake();

        $createResponse = $this->post(route('admin.users.store'), [
            'name' => 'Teknisi CRUD Test',
            'email' => 'crud-teknisi@polda.go.id',
            'role' => User::ROLE_TEKNISI,
            'nrp_nip' => '99112233',
            'satuan_kerja' => 'Bid TIK Test',
            'phone_number' => '081234599999',
            'alamat' => 'Bid TIK POLDA',
        ]);
        $createResponse->assertRedirect(route('admin.users.index'))->assertSessionHas('success');

        $managedUser = User::where('email', 'crud-teknisi@polda.go.id')->firstOrFail();
        $this->assertSame(User::ROLE_TEKNISI, $managedUser->role);
        $this->assertFalse($managedUser->is_active);
        $this->assertNull($managedUser->email_verified_at);
        $this->assertTrue($managedUser->requiresEmailVerification());
        $this->assertSame(User::APPROVAL_ACTIVE, $managedUser->approval_status);
        Notification::assertSentTo($managedUser, AccountActivationNotification::class);

        $searchResponse = $this->get(route('admin.users.index', ['search' => 'CRUD Test', 'role' => User::ROLE_TEKNISI]));
        $searchResponse->assertOk();
        $searchResponse->assertSee('Teknisi CRUD Test');

        $blockedToggleResponse = $this->patch(route('admin.users.toggle-status', $managedUser->id));
        $blockedToggleResponse->assertSessionHas('error');
        $this->assertFalse($managedUser->fresh()->is_active);

        $updateResponse = $this->put(route('admin.users.update', $managedUser->id), [
            'name' => 'Teknisi CRUD Diperbarui',
            'email' => 'crud-teknisi@polda.go.id',
            'role' => User::ROLE_TEKNISI,
            'nrp_nip' => '99112233',
            'satuan_kerja' => 'Bid TIK Test',
            'phone_number' => '081234599998',
            'alamat' => 'Bid TIK POLDA',
            'is_active' => 0,
        ]);
        $updateResponse->assertRedirect(route('admin.users.index'));

        $managedUser->refresh();
        $this->assertSame(User::ROLE_TEKNISI, $managedUser->role);
        $this->assertSame('Teknisi CRUD Diperbarui', $managedUser->name);

        $managedUser->forceFill([
            'email_verified_at' => now(),
            'requires_email_verification' => false,
        ])->save();

        $toggleResponse = $this->patch(route('admin.users.toggle-status', $managedUser->id));
        $toggleResponse->assertSessionHas('success');
        $managedUser->refresh();
        $this->assertTrue($managedUser->is_active);

        $deactivateResponse = $this->patch(route('admin.users.toggle-status', $managedUser->id));
        $deactivateResponse->assertSessionHas('success');
        $managedUser->refresh();
        $this->assertFalse($managedUser->is_active);
    }

    public function test_admin_can_manage_categories_and_delete_unused_category(): void
    {
        $this->actingAs($this->admin);

        $this->post(route('admin.categories.store'), [
            'name' => 'Gangguan Jaringan Uji',
            'icon' => 'bi-wifi',
            'description' => 'Gangguan jaringan untuk pengujian.',
        ])->assertRedirect()
            ->assertSessionHas('success');

        $category = Category::where('name', 'Gangguan Jaringan Uji')->firstOrFail();
        $this->assertSame('bi-wifi', $category->icon);
        $this->assertSame('gangguan-jaringan-uji', $category->slug);
        $this->assertTrue($category->is_active);

        $this->get(route('admin.categories.index'))
            ->assertOk()
            ->assertSee('Gangguan Jaringan Uji')
            ->assertSee('Gangguan jaringan untuk pengujian.')
            ->assertSee('0 Laporan')
            ->assertSee('Hapus Gangguan Jaringan Uji')
            ->assertSee('2 Kategori');

        $this->put(route('admin.categories.update', $category->id), [
            'name' => 'Gangguan Jaringan Gedung',
            'icon' => 'bi-router',
            'description' => 'Deskripsi diperbarui.',
            'is_active' => 1,
        ])->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Gangguan Jaringan Gedung',
            'slug' => 'gangguan-jaringan-gedung',
            'icon' => 'bi-router',
            'description' => 'Deskripsi diperbarui.',
            'is_active' => true,
        ]);

        $this->delete(route('admin.categories.destroy', $category->id))
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
        $this->get(route('admin.categories.index'))
            ->assertOk()
            ->assertDontSee('Gangguan Jaringan Gedung')
            ->assertSee('1 Kategori');
    }

    public function test_category_delete_is_blocked_without_changing_ticket_history_or_rating(): void
    {
        $ticket = Ticket::create([
            'ticket_number' => 'TKT-' . date('Ymd') . '-CATEGORY',
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'title' => 'Tiket mempertahankan kategori',
            'location' => 'Ruang Uji',
            'description' => 'Data relasi tidak boleh ikut terhapus.',
            'status' => Ticket::STATUS_SELESAI_DINILAI,
        ]);
        $history = TicketStatusHistory::create([
            'ticket_id' => $ticket->id,
            'user_id' => $this->admin->id,
            'status_from' => Ticket::STATUS_DALAM_PENANGANAN,
            'status_to' => Ticket::STATUS_SELESAI_DINILAI,
            'notes' => 'Riwayat untuk pengujian.',
        ]);
        $rating = TicketRating::create([
            'ticket_id' => $ticket->id,
            'user_id' => $this->user->id,
            'padal_id' => $this->padalUnit->id,
            'rating' => 5,
            'review' => 'Penanganan baik.',
        ]);

        $this->actingAs($this->admin)
            ->delete(route('admin.categories.destroy', $this->category->id))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('categories', ['id' => $this->category->id]);
        $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'category_id' => $this->category->id]);
        $this->assertDatabaseHas('ticket_status_histories', ['id' => $history->id, 'ticket_id' => $ticket->id]);
        $this->assertDatabaseHas('ticket_ratings', ['id' => $rating->id, 'ticket_id' => $ticket->id]);
    }

    public function test_only_admin_can_delete_categories(): void
    {
        foreach ([$this->user, $this->padal, $this->teknisi] as $actor) {
            $this->actingAs($actor)
                ->delete(route('admin.categories.destroy', $this->category->id))
                ->assertForbidden();
        }

        $this->assertDatabaseHas('categories', ['id' => $this->category->id]);
    }

    public function test_inactive_category_cannot_be_used_for_new_ticket(): void
    {
        $category = Category::create([
            'name' => 'Kategori Nonaktif Uji',
            'slug' => 'kategori-nonaktif-uji',
            'description' => 'Tidak boleh dipakai untuk laporan baru.',
            'icon' => 'bi-tag',
            'is_active' => false,
        ]);

        $this->actingAs($this->user)
            ->post(route('user.tickets.store'), [
                'category_id' => $category->id,
                'title' => 'Laporan kategori nonaktif',
                'location' => 'Ruang Uji',
                'description' => 'Request langsung mencoba memakai kategori nonaktif.',
            ])
            ->assertSessionHasErrors('category_id');

        $this->assertDatabaseMissing('tickets', ['title' => 'Laporan kategori nonaktif']);
    }

    public function test_dashboard_stat_cards_link_to_role_scoped_lists_and_apply_status_filters(): void
    {
        $makeTicket = function (string $suffix, string $status, ?int $reporterId = null, ?int $padalId = null): Ticket {
            return Ticket::create([
                'ticket_number' => 'TKT-' . date('Ymd') . '-STAT-' . $suffix,
                'user_id' => $reporterId ?? $this->user->id,
                'category_id' => $this->category->id,
                'padal_id' => $padalId ?? $this->padalUnit->id,
                'title' => 'Statistik ' . $suffix,
                'location' => 'Ruang Uji',
                'description' => 'Tiket untuk menguji navigasi kartu statistik.',
                'status' => $status,
            ]);
        };

        $waiting = $makeTicket('WAIT', Ticket::STATUS_MENUNGGU_VERIFIKASI);
        $pendingPadal = $makeTicket('PENDING', Ticket::STATUS_MENUNGGU_KONFIRMASI_PADAL);
        $inProgress = $makeTicket('PROGRESS', Ticket::STATUS_DALAM_PENANGANAN);
        $completed = $makeTicket('DONE', Ticket::STATUS_SELESAI);
        $rated = $makeTicket('RATED', Ticket::STATUS_SELESAI_DINILAI);
        $otherReporter = $this->createUser(User::ROLE_USER, 'other-reporter');
        $otherReport = $makeTicket('OTHER-USER', Ticket::STATUS_MENUNGGU_VERIFIKASI, $otherReporter->id);
        $otherPadal = $this->createUser(User::ROLE_PADAL, 'stats-other-padal');
        $otherPadalUnit = Padal::create([
            'user_id' => $otherPadal->id,
            'nama_padal' => 'Unit Padal Statistik Lain',
            'deskripsi' => 'Unit lain untuk uji isolasi statistik.',
            'status' => 'aktif',
        ]);
        $otherPadalTicket = $makeTicket('OTHER-PADAL', Ticket::STATUS_DALAM_PENANGANAN, $this->user->id, $otherPadalUnit->id);
        $otherTechnician = $this->createUser(User::ROLE_TEKNISI, 'stats-other-technician');

        foreach ([$pendingPadal, $inProgress, $completed, $rated] as $ticket) {
            TicketTechnicianAssignment::create([
                'ticket_id' => $ticket->id,
                'user_id' => $this->teknisi->id,
                'assigned_by' => $this->padal->id,
                'assigned_at' => now(),
            ]);
        }
        TicketTechnicianAssignment::create([
            'ticket_id' => $otherPadalTicket->id,
            'user_id' => $otherTechnician->id,
            'assigned_by' => $otherPadal->id,
            'assigned_at' => now(),
        ]);

        $adminDashboard = $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertOk();
        foreach ([
            route('admin.tickets.index'),
            route('admin.tickets.index', ['status' => 'menunggu_verifikasi']),
            route('admin.tickets.index', ['status' => 'dalam_penanganan']),
            route('admin.tickets.index', ['status' => 'selesai_semua']),
            route('admin.padals.index'),
            route('admin.users.index', ['role' => User::ROLE_TEKNISI]),
            route('admin.users.index', ['role' => User::ROLE_USER]),
            route('admin.ratings.index'),
        ] as $target) {
            $adminDashboard->assertSee($target);
        }
        $this->get(route('admin.tickets.index'))
            ->assertOk()
            ->assertSee($waiting->ticket_number)
            ->assertSee($inProgress->ticket_number)
            ->assertSee($completed->ticket_number);
        $this->get(route('admin.padals.index'))->assertOk();
        $this->get(route('admin.ratings.index'))->assertOk();
        $this->get(route('admin.tickets.index', ['status' => 'menunggu_verifikasi']))
            ->assertOk()->assertSee($waiting->ticket_number)->assertDontSee($inProgress->ticket_number);
        $this->get(route('admin.tickets.index', ['status' => 'selesai_semua']))
            ->assertOk()->assertSee($completed->ticket_number)->assertSee($rated->ticket_number)
            ->assertDontSee($waiting->ticket_number);
        $this->get(route('admin.users.index', ['role' => User::ROLE_USER]))
            ->assertOk()->assertSee($this->user->email)->assertDontSee($otherTechnician->email);
        $this->get(route('admin.users.index', ['role' => User::ROLE_TEKNISI]))
            ->assertOk()->assertSee($this->teknisi->email)->assertDontSee($this->user->email);

        $padalDashboard = $this->actingAs($this->padal)->get(route('padal.dashboard'))->assertOk();
        foreach ([
            route('padal.tickets.index'),
            route('padal.tickets.index', ['status' => 'menunggu_konfirmasi_padal']),
            route('padal.tickets.index', ['status' => 'dalam_penanganan']),
            route('padal.tickets.index', ['status' => 'selesai_semua']),
        ] as $target) {
            $padalDashboard->assertSee($target);
        }
        $this->get(route('padal.tickets.index'))
            ->assertOk()
            ->assertSee($pendingPadal->ticket_number)
            ->assertSee($inProgress->ticket_number)
            ->assertDontSee($otherPadalTicket->ticket_number);
        $this->get(route('padal.members.index'))->assertOk();
        $this->get(route('padal.tickets.index', ['status' => 'menunggu_konfirmasi_padal']))
            ->assertOk()->assertSee($pendingPadal->ticket_number)->assertDontSee($waiting->ticket_number);
        $this->get(route('padal.tickets.index', ['status' => 'selesai_semua']))
            ->assertOk()->assertSee($completed->ticket_number)->assertSee($rated->ticket_number)
            ->assertDontSee($otherPadalTicket->ticket_number);
        $this->get(route('padal.tickets.index'))
            ->assertOk()->assertDontSee($otherPadalTicket->ticket_number);

        $technicianDashboard = $this->actingAs($this->teknisi)->get(route('teknisi.dashboard'))->assertOk();
        foreach ([
            route('teknisi.tickets.index'),
            route('teknisi.tickets.index', ['status' => 'dalam_penanganan']),
            route('teknisi.tickets.index', ['status' => 'selesai_semua']),
        ] as $target) {
            $technicianDashboard->assertSee($target);
        }
        $this->get(route('teknisi.tickets.index'))
            ->assertOk()
            ->assertSee($inProgress->ticket_number)
            ->assertSee($completed->ticket_number)
            ->assertDontSee($otherPadalTicket->ticket_number);
        $this->get(route('teknisi.tickets.index', ['status' => 'dalam_penanganan']))
            ->assertOk()->assertSee($inProgress->ticket_number)->assertDontSee($otherPadalTicket->ticket_number);
        $this->get(route('teknisi.tickets.index', ['status' => 'selesai_semua']))
            ->assertOk()->assertSee($completed->ticket_number)->assertSee($rated->ticket_number)
            ->assertDontSee($waiting->ticket_number);
        $this->get(route('teknisi.tickets.index'))
            ->assertOk()->assertDontSee($otherPadalTicket->ticket_number);

        $userDashboard = $this->actingAs($this->user)->get(route('user.dashboard'))->assertOk();
        foreach ([
            route('user.tickets.index'),
            route('user.tickets.index', ['status' => 'menunggu_verifikasi']),
            route('user.tickets.index', ['status' => 'dalam_penanganan']),
            route('user.tickets.index', ['status' => 'selesai_semua']),
        ] as $target) {
            $userDashboard->assertSee($target);
        }
        $this->get(route('user.tickets.index'))
            ->assertOk()
            ->assertSee($waiting->ticket_number)
            ->assertSee($inProgress->ticket_number)
            ->assertDontSee($otherReport->ticket_number);
        $this->get(route('user.tickets.index', ['status' => 'menunggu_verifikasi']))
            ->assertOk()->assertSee($waiting->ticket_number)->assertDontSee($inProgress->ticket_number)
            ->assertDontSee($otherReport->ticket_number);
        $this->get(route('user.tickets.index', ['status' => 'selesai_semua']))
            ->assertOk()->assertSee($completed->ticket_number)->assertSee($rated->ticket_number)
            ->assertDontSee($otherReport->ticket_number);
    }

    public function test_empty_dashboard_stat_cards_remain_navigable_and_role_boundaries_hold(): void
    {
        $this->actingAs($this->admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(route('admin.tickets.index', ['status' => 'menunggu_verifikasi']))
            ->assertSee(route('admin.users.index', ['role' => User::ROLE_USER]));

        $this->actingAs($this->padal)->get(route('padal.dashboard'))
            ->assertOk()
            ->assertSee(route('padal.tickets.index', ['status' => 'dalam_penanganan']))
            ->assertSee(route('padal.members.index'));

        $this->actingAs($this->teknisi)->get(route('teknisi.dashboard'))
            ->assertOk()
            ->assertSee(route('teknisi.tickets.index', ['status' => 'selesai_semua']));

        $this->actingAs($this->user)->get(route('user.dashboard'))
            ->assertOk()
            ->assertSee(route('user.tickets.index', ['status' => 'selesai_semua']));

        $this->actingAs($this->user)->get(route('admin.tickets.index'))->assertForbidden();
        $this->actingAs($this->padal)->get(route('teknisi.tickets.index'))->assertForbidden();
        $this->actingAs($this->teknisi)->get(route('user.tickets.index'))->assertForbidden();
    }

    public function test_padal_dashboard_creates_a_default_unit_when_account_has_none(): void
    {
        Padal::where('user_id', $this->padal->id)->delete();

        $this->actingAs($this->padal)
            ->get(route('padal.dashboard'))
            ->assertOk();

        $this->assertDatabaseHas('padals', [
            'user_id' => $this->padal->id,
            'nama_padal' => 'Unit Padal - '.$this->padal->name,
            'status' => 'aktif',
        ]);
    }

    public function test_padal_with_an_inactive_unit_receives_an_explanation_instead_of_not_found(): void
    {
        $this->padalUnit->update(['status' => 'nonaktif']);

        $this->actingAs($this->padal)
            ->get(route('padal.dashboard'))
            ->assertForbidden()
            ->assertSee('Unit Padal Anda tidak aktif.');

        $this->assertDatabaseCount('padals', 1);
    }

    public function test_admin_can_change_own_email_and_profile_then_verify_new_address(): void
    {
        Notification::fake();
        $originalId = $this->admin->id;

        $this->actingAs($this->admin)
            ->get(route('profile.show'))
            ->assertOk()
            ->assertSee('name="email"', false)
            ->assertSee('name="current_password"', false);

        $this->put(route('profile.update'), [
            'name' => 'Administrator Profil Diperbarui',
            'email' => 'admin-updated@example.test',
            'nrp_nip' => '123456789',
            'phone_number' => '081234567890',
            'satuan_kerja' => 'Bid TIK POLDA',
            'current_password' => 'password',
            'new_password' => 'password-baru-123',
            'new_password_confirmation' => 'password-baru-123',
            'role' => User::ROLE_USER,
        ])->assertRedirect(route('profile.show'))
            ->assertSessionHas('success');

        $this->admin->refresh();
        $this->assertSame($originalId, $this->admin->id);
        $this->assertSame(User::ROLE_ADMIN, $this->admin->role);
        $this->assertSame('Administrator Profil Diperbarui', $this->admin->name);
        $this->assertSame('admin-updated@example.test', $this->admin->email);
        $this->assertSame('123456789', $this->admin->nrp_nip);
        $this->assertSame('081234567890', $this->admin->phone_number);
        $this->assertSame('Bid TIK POLDA', $this->admin->satuan_kerja);
        $this->assertNull($this->admin->email_verified_at);
        $this->assertTrue($this->admin->requiresEmailVerification());
        $this->assertTrue(Hash::check('password-baru-123', $this->admin->password));
        $this->assertDatabaseCount('users', 4);
        Notification::assertSentTo($this->admin, VerifyEmail::class);

        $this->get(route('profile.show'))
            ->assertOk()
            ->assertSee('admin-updated@example.test')
            ->assertSee('Kirim Ulang Tautan Verifikasi');
        $this->post(route('verification.send'))
            ->assertRedirect()
            ->assertSessionHas('success');
        Notification::assertSentTo($this->admin, VerifyEmail::class);
        $this->get(route('admin.dashboard'))->assertRedirect(route('verification.notice'));

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(30),
            ['id' => $this->admin->id, 'hash' => sha1($this->admin->getEmailForVerification())]
        );
        $this->get($verificationUrl)->assertRedirect(route('admin.dashboard'));

        $this->admin->refresh();
        $this->assertTrue($this->admin->hasVerifiedEmail());
        $this->assertFalse($this->admin->requiresEmailVerification());

        $this->post(route('logout'));
        $this->post(route('login.post'), [
            'account_type' => User::ROLE_ADMIN,
            'email' => 'admin-updated@example.test',
            'password' => 'password-baru-123',
        ])->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->admin);
    }

    public function test_admin_email_change_requires_current_password_and_rejects_invalid_or_duplicate_email(): void
    {
        $this->actingAs($this->admin);
        $originalEmail = $this->admin->email;

        $this->from(route('profile.show'))
            ->put(route('profile.update'), [
                'name' => $this->admin->name,
                'email' => $originalEmail,
            ])->assertRedirect(route('profile.show'))
            ->assertSessionHas('success');

        $this->from(route('profile.show'))
            ->put(route('profile.update'), [
                'name' => $this->admin->name,
                'email' => 'new-admin@example.test',
            ])
            ->assertRedirect(route('profile.show'))
            ->assertSessionHasErrors('current_password');

        $this->from(route('profile.show'))
            ->put(route('profile.update'), [
                'name' => $this->admin->name,
                'email' => $this->user->email,
                'current_password' => 'password',
            ])
            ->assertRedirect(route('profile.show'))
            ->assertSessionHasErrors('email');

        $this->from(route('profile.show'))
            ->put(route('profile.update'), [
                'name' => $this->admin->name,
                'email' => 'bukan-email',
                'current_password' => 'password',
            ])
            ->assertRedirect(route('profile.show'))
            ->assertSessionHasErrors('email');

        $this->from(route('profile.show'))
            ->put(route('profile.update'), [
                'name' => $this->admin->name,
                'email' => 'new-admin@example.test',
                'current_password' => 'kata-sandi-salah',
            ])
            ->assertRedirect(route('profile.show'))
            ->assertSessionHasErrors('current_password');

        $this->assertSame($originalEmail, $this->admin->fresh()->email);
        $this->assertSame(User::ROLE_ADMIN, $this->admin->fresh()->role);
        $this->assertDatabaseCount('users', 4);
    }

    public function test_non_admin_profile_cannot_change_email(): void
    {
        $originalEmail = $this->user->email;

        $this->actingAs($this->user)
            ->put(route('profile.update'), [
                'name' => $this->user->name,
                'email' => 'changed-user@example.test',
            ])
            ->assertSessionHasErrors('email');

        $this->assertSame($originalEmail, $this->user->fresh()->email);
    }

    public function test_admin_email_change_reports_verification_delivery_failure(): void
    {
        $sender = \Mockery::mock(EmailVerificationSender::class);
        $sender->shouldReceive('send')->once()->andReturn(false);
        $this->app->instance(EmailVerificationSender::class, $sender);

        $this->actingAs($this->admin)
            ->from(route('profile.show'))
            ->put(route('profile.update'), [
                'name' => $this->admin->name,
                'email' => 'admin-pending@example.test',
                'current_password' => 'password',
            ])
            ->assertRedirect(route('profile.show'))
            ->assertSessionHas('error');

        $this->admin->refresh();
        $this->assertSame('admin-pending@example.test', $this->admin->email);
        $this->assertNull($this->admin->email_verified_at);
        $this->assertTrue($this->admin->requiresEmailVerification());
    }

    public function test_admin_user_management_rejects_duplicate_email_and_role_change_for_self(): void
    {
        $this->actingAs($this->admin);

        $duplicateResponse = $this->from(route('admin.users.create'))->post(route('admin.users.store'), [
            'name' => 'Duplikat Email',
            'email' => $this->user->email,
            'role' => User::ROLE_PADAL,
            'nrp_nip' => '99112234',
            'satuan_kerja' => 'SPKT Test',
        ]);
        $duplicateResponse->assertRedirect(route('admin.users.create'));
        $duplicateResponse->assertSessionHasErrors('email');

        $roleResponse = $this->from(route('admin.users.edit', $this->admin->id))->put(route('admin.users.update', $this->admin->id), [
            'name' => $this->admin->name,
            'email' => $this->admin->email,
            'role' => User::ROLE_USER,
            'nrp_nip' => $this->admin->nrp_nip,
            'satuan_kerja' => $this->admin->satuan_kerja,
            'phone_number' => $this->admin->phone_number,
            'alamat' => $this->admin->alamat,
            'is_active' => 1,
        ]);
        $roleResponse->assertRedirect(route('admin.users.edit', $this->admin->id));
        $roleResponse->assertSessionHas('error');
        $this->assertSame(User::ROLE_ADMIN, $this->admin->fresh()->role);
    }

    public function test_non_admin_cannot_open_admin_user_management(): void
    {
        foreach ([$this->user, $this->padal, $this->teknisi] as $actor) {
            $this->actingAs($actor)->get(route('admin.users.index'))->assertStatus(403);
            Auth::logout();
        }
    }

    public function test_public_registration_creates_unverified_user_without_admin_approval(): void
    {
        Notification::fake();
        $email = 'register-user-verify@polda.go.id';

        $response = $this->post(route('register.post'), [
            'name' => 'Pendaftar Pelapor',
            'email' => $email,
            'nrp_nip' => '99010',
            'satuan_kerja' => 'Bid TIK',
            'phone_number' => '0812345000',
            'alamat' => 'Bid TIK POLDA',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('verification.notice'));
        $user = User::where('email', $email)->firstOrFail();
        $this->assertSame(User::ROLE_USER, $user->role);
        $this->assertSame(User::APPROVAL_ACTIVE, $user->approval_status);
        $this->assertTrue($user->is_active);
        $this->assertNull($user->email_verified_at);
        $this->assertTrue($user->requiresEmailVerification());
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_verification_notice_shows_the_account_email_destination(): void
    {
        $unverifiedUser = User::factory()->create([
            'email' => 'pending-verification@example.test',
            'role' => User::ROLE_USER,
            'email_verified_at' => null,
            'requires_email_verification' => true,
        ]);

        $this->actingAs($unverifiedUser)
            ->get(route('verification.notice'))
            ->assertOk()
            ->assertSee('pending-verification@example.test')
            ->assertSee('folder Spam atau Promosi');
    }

    public function test_public_registration_rejects_any_submitted_role(): void
    {
        foreach ([User::ROLE_ADMIN, User::ROLE_PADAL, User::ROLE_TEKNISI] as $role) {
            $email = 'role-spoof-'.$role.'@polda.go.id';
            $response = $this->from(route('register'))->post(route('register.post'), [
                'role' => $role,
                'name' => 'Pendaftar Tidak Sah',
                'email' => $email,
                'nrp_nip' => '99011',
                'satuan_kerja' => 'Bid TIK',
                'phone_number' => '0812345001',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

            $response->assertRedirect(route('register'))->assertSessionHasErrors('role');
            $this->assertDatabaseMissing('users', ['email' => $email]);
        }
    }

    public function test_signed_verification_link_verifies_email_and_clears_requirement(): void
    {
        $user = User::factory()->create([
            'email' => 'signed-verification@polda.go.id',
            'role' => User::ROLE_USER,
            'email_verified_at' => null,
            'requires_email_verification' => true,
            'approval_status' => User::APPROVAL_ACTIVE,
        ]);

        $this->actingAs($user);
        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addHour(),
            ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())]
        );

        $response = $this->get($url);
        $response->assertRedirect(route('user.dashboard'));

        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
        $this->assertFalse((bool) $user->requires_email_verification);
        $this->assertSame(User::APPROVAL_ACTIVE, $user->approval_status);
    }

    public function test_verification_link_rejects_invalid_and_expired_signatures(): void
    {
        $user = User::factory()->create([
            'email' => 'expired-verification@polda.go.id',
            'requires_email_verification' => true,
            'email_verified_at' => null,
        ]);
        $parameters = [
            'id' => $user->id,
            'hash' => sha1($user->getEmailForVerification()),
        ];

        $unsignedUrl = route('verification.verify', $parameters);
        $this->get($unsignedUrl)->assertForbidden();

        $expiredUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->subMinute(),
            $parameters
        );
        $this->get($expiredUrl)->assertForbidden();

        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_unverified_user_cannot_access_reporting_features(): void
    {
        $user = User::factory()->create([
            'email' => 'blocked-report@polda.go.id',
            'role' => User::ROLE_USER,
            'is_active' => true,
            'requires_email_verification' => true,
            'email_verified_at' => null,
        ]);

        $this->actingAs($user)
            ->get(route('user.dashboard'))
            ->assertRedirect(route('verification.notice'));
    }

    public function test_resend_sends_verification_notification_only_on_success(): void
    {
        Notification::fake();
        $user = User::factory()->create([
            'email' => 'resend-verification@polda.go.id',
            'requires_email_verification' => true,
            'email_verified_at' => null,
        ]);

        $this->actingAs($user)
            ->post(route('verification.send'))
            ->assertRedirect()
            ->assertSessionHas('success');

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_resend_failure_shows_friendly_message_without_success_flash(): void
    {
        $user = User::factory()->create([
            'email' => 'failed-resend@polda.go.id',
            'requires_email_verification' => true,
            'email_verified_at' => null,
        ]);
        $sender = \Mockery::mock(EmailVerificationSender::class);
        $sender->shouldReceive('send')->once()->withArgs(fn (User $sentUser) => $sentUser->is($user))->andReturnFalse();
        $this->app->instance(EmailVerificationSender::class, $sender);

        $this->actingAs($user)
            ->post(route('verification.send'))
            ->assertRedirect()
            ->assertSessionHas('error', EmailVerificationSender::FAILURE_MESSAGE)
            ->assertSessionMissing('success');
    }

    public function test_unverified_user_and_unapproved_padal_cannot_login(): void
    {
        $unverified = User::factory()->create([
            'email' => 'unverified-login@polda.go.id',
            'role' => User::ROLE_USER,
            'is_active' => true,
            'approval_status' => User::APPROVAL_ACTIVE,
            'requires_email_verification' => true,
            'email_verified_at' => null,
        ]);

        $response = $this->from(route('login'))->post(route('login.post'), [
            'account_type' => User::ROLE_USER,
            'email' => $unverified->email,
            'password' => 'password',
        ]);
        $response->assertRedirect(route('verification.notice'));
        $this->assertAuthenticatedAs($unverified);
        Auth::logout();

        $pendingPadal = User::factory()->create([
            'email' => 'pending-padal@polda.go.id',
            'role' => User::ROLE_PADAL,
            'is_active' => true,
            'approval_status' => User::APPROVAL_PENDING,
            'requires_email_verification' => false,
            'email_verified_at' => now(),
        ]);

        $pendingResponse = $this->from(route('login'))->post(route('login.post'), [
            'account_type' => User::ROLE_PADAL,
            'email' => $pendingPadal->email,
            'password' => 'password',
        ]);
        $pendingResponse->assertRedirect(route('login'));
        $pendingResponse->assertSessionHasErrors('email');
    }

    public function test_admin_can_approve_reject_and_reset_verification_on_email_change(): void
    {
        $pendingPadal = User::factory()->create([
            'role' => User::ROLE_PADAL,
            'nrp_nip' => '99112233',
            'satuan_kerja' => 'Bid TIK',
            'email' => 'approve-padal@polda.go.id',
            'email_verified_at' => now(),
            'is_active' => true,
            'approval_status' => User::APPROVAL_PENDING,
            'requires_email_verification' => false,
        ]);

        $this->actingAs($this->admin);

        $approveResponse = $this->patch(route('admin.users.approve', $pendingPadal->id));
        $approveResponse->assertSessionHas('success');
        $this->assertSame(User::APPROVAL_ACTIVE, $pendingPadal->fresh()->approval_status);

        $rejectResponse = $this->patch(route('admin.users.reject', $pendingPadal->id));
        $rejectResponse->assertSessionHas('success');
        $pendingPadal->refresh();
        $this->assertSame(User::APPROVAL_REJECTED, $pendingPadal->approval_status);
        $this->assertFalse($pendingPadal->is_active);

        $emailChangeResponse = $this->put(route('admin.users.update', $pendingPadal->id), [
            'name' => $pendingPadal->name,
            'email' => 'verified-change@polda.go.id',
            'role' => User::ROLE_PADAL,
            'nrp_nip' => $pendingPadal->nrp_nip,
            'satuan_kerja' => $pendingPadal->satuan_kerja,
            'phone_number' => $pendingPadal->phone_number,
            'alamat' => $pendingPadal->alamat,
            'password' => '',
            'password_confirmation' => '',
            'is_active' => 0,
        ]);
        $emailChangeResponse->assertRedirect(route('admin.users.index'));

        $pendingPadal->refresh();
        $this->assertNull($pendingPadal->email_verified_at);
        $this->assertTrue((bool) $pendingPadal->requires_email_verification);
    }

    public function test_staff_activation_token_sets_password_and_activates_account(): void
    {
        $staff = User::factory()->create([
            'role' => User::ROLE_TEKNISI,
            'email' => 'activation-test@polda.go.id',
            'is_active' => false,
            'approval_status' => User::APPROVAL_ACTIVE,
            'requires_email_verification' => true,
            'email_verified_at' => null,
        ]);
        $token = Password::createToken($staff);

        $this->get(route('password.reset', ['token' => $token, 'email' => $staff->email]))
            ->assertOk();

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $staff->email,
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ])->assertRedirect(route('login'))->assertSessionHas('success');

        $staff->refresh();
        $this->assertTrue($staff->is_active);
        $this->assertTrue($staff->hasVerifiedEmail());
        $this->assertFalse($staff->requiresEmailVerification());
        $this->assertTrue(Hash::check('new-secure-password', $staff->password));
    }

    public function test_public_registration_creates_only_user_role_with_hashed_password(): void
    {
        Notification::fake();
        $email = 'pelapor-baru@polda.go.id';

        $response = $this->post(route('register.post'), [
            'name' => 'Pelapor Baru',
            'email' => $email,
            'nrp_nip' => '99010001',
            'satuan_kerja' => 'SPKT',
            'phone_number' => '081234500001',
            'alamat' => 'SPKT POLDA',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('verification.notice'));
        $created = User::where('email', $email)->firstOrFail();
        $this->assertSame(User::ROLE_USER, $created->role);
        $this->assertNull($created->email_verified_at);
        $this->assertTrue((bool) $created->requires_email_verification);
        $this->assertTrue(Hash::check('password123', $created->password));
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

    public function test_padal_only_sees_tickets_for_the_padal_record_linked_to_their_account(): void
    {
        $ticket = Ticket::create([
            'ticket_number' => 'TKT-' . date('Ymd') . '-PADAL',
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'padal_id' => $this->padalUnit->id,
            'title' => 'Tiket untuk Padal yang ditugaskan',
            'location' => 'Ruang Uji',
            'description' => 'Tiket ini hanya boleh terlihat oleh Padal yang ditugaskan.',
            'status' => Ticket::STATUS_MENUNGGU_KONFIRMASI_PADAL,
        ]);
        $otherPadal = $this->createUser(User::ROLE_PADAL, 'other-padal');
        Padal::create([
            'user_id' => $otherPadal->id,
            'nama_padal' => 'Unit Padal Lain',
            'deskripsi' => 'Unit lain untuk uji isolasi',
            'status' => 'aktif',
        ]);

        $this->actingAs($this->padal)
            ->get(route('padal.tickets.index'))
            ->assertOk()
            ->assertSee($ticket->ticket_number);

        $this->actingAs($otherPadal)
            ->get(route('padal.tickets.index'))
            ->assertOk()
            ->assertDontSee($ticket->ticket_number);
        $this->get(route('padal.tickets.show', $ticket->id))
            ->assertNotFound();
        $this->assertDatabaseCount('padals', 2);

        $unlinkedPadal = $this->createUser(User::ROLE_PADAL, 'unlinked-padal');
        $this->actingAs($unlinkedPadal)
            ->get(route('padal.tickets.index'))
            ->assertOk()
            ->assertDontSee($ticket->ticket_number);
        $this->assertDatabaseHas('padals', [
            'user_id' => $unlinkedPadal->id,
            'nama_padal' => 'Unit Padal - '.$unlinkedPadal->name,
            'status' => 'aktif',
        ]);
        $this->assertDatabaseCount('padals', 3);
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
            'padal_id' => $this->padalUnit->id,
            'admin_notes' => 'Tolong segera dicek siang ini.',
        ]);
        $verifyResponse->assertRedirect(route('admin.tickets.show', $ticket->id));

        $ticket->refresh();
        $this->assertEquals(Ticket::STATUS_MENUNGGU_KONFIRMASI_PADAL, $ticket->status);
        $this->assertEquals($this->padalUnit->id, $ticket->padal_id);
        $this->assertDatabaseHas('ticket_status_histories', [
            'ticket_id' => $ticket->id,
            'status_to' => Ticket::STATUS_MENUNGGU_KONFIRMASI_PADAL,
        ]);

        $this->actingAs($this->padal)
            ->get(route('padal.tickets.index'))
            ->assertOk()
            ->assertSee($ticket->ticket_number);

        // 3. Padal menerima penugasan dan memilih anggota teknisi
        PadalAnggota::firstOrCreate(
            ['padal_id' => $this->padalUnit->id, 'user_id' => $this->teknisi->id],
            ['tanggal_bergabung' => now()]
        );
        $acceptResponse = $this->actingAs($this->padal)->post(route('padal.tickets.accept', $ticket->id), [
            'padal_notes' => 'Bawa roller cleaner ke lokasi.',
        ]);
        $acceptResponse->assertRedirect(route('padal.tickets.show', $ticket->id));

        $ticket->refresh();
        $this->assertEquals(Ticket::STATUS_DALAM_PENANGANAN, $ticket->status);

        $this->post(route('padal.tickets.technicians', $ticket->id), [
            'technician_ids' => [$this->teknisi->id],
        ])->assertRedirect();
        $this->assertTrue($ticket->technicians->contains('id', $this->teknisi->id));

        $this->actingAs($this->teknisi)
            ->get(route('teknisi.tickets.index'))
            ->assertOk()
            ->assertSee($ticket->ticket_number);
        $this->get(route('teknisi.dashboard'))
            ->assertOk()
            ->assertSee($ticket->ticket_number)
            ->assertViewHas('stats', fn (array $stats) => $stats['total_penugasan'] === 1
                && $stats['dalam_penanganan'] === 1
                && $stats['selesai'] === 0);
        $this->get(route('teknisi.tickets.show', $ticket->id))
            ->assertOk()
            ->assertSee($ticket->title)
            ->assertSee($this->padalUnit->nama_padal);
        $this->post('/teknisi/tickets/'.$ticket->id.'/complete', [
            'resolution_notes' => 'Teknisi tidak berhak mengubah status tiket.',
        ])->assertNotFound();
        $this->assertSame(Ticket::STATUS_DALAM_PENANGANAN, $ticket->fresh()->status);

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
            'padal_id' => $this->padalUnit->id,
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
            'padal_id' => $this->padalUnit->id,
        ]);

        $response = $this->actingAs($this->user)->post(route('user.tickets.rate', $ticket->id), [
            'rating' => 5,
            'review' => 'Coba rating saat tiket belum selesai.',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('ticket_ratings', ['ticket_id' => $ticket->id]);
    }
}
