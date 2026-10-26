<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\PadalAnggota;
use App\Models\Ticket;
use App\Models\TicketRating;
use App\Models\User;
use App\Notifications\AccountActivationNotification;
use App\Services\EmailVerificationSender;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Illuminate\Auth\Notifications\VerifyEmail;
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
        PadalAnggota::firstOrCreate(
            ['padal_id' => $this->padal->id, 'user_id' => $this->teknisi->id],
            ['tanggal_bergabung' => now()]
        );
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
