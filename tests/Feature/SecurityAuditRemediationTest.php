<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Padal;
use App\Models\Ticket;
use App\Models\TicketTechnicianAssignment;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Foundation\Application;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityAuditRemediationTest extends TestCase
{
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
        Storage::fake('local');
        Storage::fake('public');
    }

    // =========================================================================
    // TEMUAN 7: Netralitas Pesan Kesalahan Login (Cegah Enumerasi Akun)
    // =========================================================================

    public function test_login_returns_identical_error_message_for_wrong_password(): void
    {
        $user = User::factory()->create([
            'email' => 'user.auth@polda.go.id',
            'password' => Hash::make('ValidPassword123!'),
            'role' => User::ROLE_USER,
            'is_active' => true,
            'requires_email_verification' => false,
        ]);

        $response = $this->post(route('login.post'), [
            'account_type' => User::ROLE_USER,
            'email' => 'user.auth@polda.go.id',
            'password' => 'WrongPassword999!',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'Email, kata sandi, atau jenis akun salah.',
        ]);
        $this->assertGuest();
    }

    public function test_login_returns_identical_error_message_for_non_existent_email(): void
    {
        $response = $this->post(route('login.post'), [
            'account_type' => User::ROLE_USER,
            'email' => 'tidak.terdaftar@polda.go.id',
            'password' => 'AnyPassword123!',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'Email, kata sandi, atau jenis akun salah.',
        ]);
        $this->assertGuest();
    }

    public function test_login_returns_identical_error_message_when_role_mismatch_occurs(): void
    {
        // Pengguna terdaftar sebagai User/Pelapor
        $user = User::factory()->create([
            'email' => 'pelapor.asli@polda.go.id',
            'password' => Hash::make('ValidPassword123!'),
            'role' => User::ROLE_USER,
            'is_active' => true,
            'requires_email_verification' => false,
        ]);

        // Penyerang mencoba login dengan email valid tapi memilih jenis akun "admin"
        $response = $this->post(route('login.post'), [
            'account_type' => User::ROLE_ADMIN,
            'email' => 'pelapor.asli@polda.go.id',
            'password' => 'ValidPassword123!',
        ]);

        // Sistem TIDAK BOLEH membocorkan bahwa akun tersebut ada
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors([
            'email' => 'Email, kata sandi, atau jenis akun salah.',
        ]);
        $this->assertGuest();
    }

    // =========================================================================
    // TEMUAN 5: Kompleksitas Kata Sandi (Min 10 Karakter, MixedCase, Numbers)
    // =========================================================================

    public function test_registration_rejects_passwords_failing_complexity_rules(): void
    {
        $payload = [
            'name' => 'Bripda Andi',
            'email' => 'bripda.andi@polda.go.id',
            'nrp_nip' => '98012345',
            'satuan_kerja' => 'Ditlantas',
            'phone_number' => '081234567890',
        ];

        // 1. Kurang dari 10 karakter
        $response = $this->post(route('register.post'), array_merge($payload, [
            'password' => 'Short1!',
            'password_confirmation' => 'Short1!',
        ]));
        $response->assertSessionHasErrors('password');

        // 2. Tidak memuat huruf besar
        $response = $this->post(route('register.post'), array_merge($payload, [
            'password' => 'lowercaseonly123',
            'password_confirmation' => 'lowercaseonly123',
        ]));
        $response->assertSessionHasErrors('password');

        // 3. Tidak memuat angka
        $response = $this->post(route('register.post'), array_merge($payload, [
            'password' => 'NoNumbersAtAllHere',
            'password_confirmation' => 'NoNumbersAtAllHere',
        ]));
        $response->assertSessionHasErrors('password');

        // 4. Valid (>= 10 karakter, ada huruf besar, huruf kecil, dan angka)
        $response = $this->post(route('register.post'), array_merge($payload, [
            'password' => 'KombinasiKuat123',
            'password_confirmation' => 'KombinasiKuat123',
        ]));
        $response->assertRedirect(route('verification.notice'));
        $this->assertDatabaseHas('users', ['email' => 'bripda.andi@polda.go.id']);
    }

    // =========================================================================
    // TEMUAN 4: Penerapan Security Headers
    // =========================================================================

    public function test_security_headers_are_present_on_web_responses(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertNotNull($csp);
        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("cdn.jsdelivr.net", $csp);
        $this->assertStringContainsString("fonts.googleapis.com", $csp);
        $this->assertStringContainsString("frame-ancestors 'none'", $csp);
    }

    // =========================================================================
    // TEMUAN 6: Batasi Endpoint Health /up
    // =========================================================================

    public function test_health_up_endpoint_returns_simple_plain_text(): void
    {
        $response = $this->get('/up');

        $response->assertOk();
        $this->assertSame('OK', $response->getContent());
        $this->assertStringContainsString('text/plain', (string) $response->headers->get('Content-Type'));
        $this->assertStringNotContainsString('Laravel', $response->getContent());
    }

    // =========================================================================
    // TEMUAN 3: Akses Lampiran Tiket Privat & Otorisasi
    // =========================================================================

    public function test_unauthenticated_user_cannot_access_ticket_attachment(): void
    {
        $ticket = $this->createDummyTicketWithAttachment('attachments/tickets/sample.pdf');

        $response = $this->get(route('tickets.attachment', $ticket));
        $response->assertRedirect(route('login'));
    }

    public function test_pelapor_can_access_own_ticket_attachment_but_not_others(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_USER]);
        $otherUser = User::factory()->create(['role' => User::ROLE_USER]);

        $ticket = $this->createDummyTicketWithAttachment('attachments/tickets/sample.pdf', $owner);

        // Akses oleh pelapor pemilik -> 200 OK
        $this->actingAs($owner);
        $response = $this->get(route('tickets.attachment', $ticket));
        $response->assertOk();

        // Akses oleh pelapor lain -> 403 Forbidden
        $this->actingAs($otherUser);
        $response = $this->get(route('tickets.attachment', $ticket));
        $response->assertForbidden();
    }

    public function test_admin_can_access_any_ticket_attachment(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $owner = User::factory()->create(['role' => User::ROLE_USER]);
        $ticket = $this->createDummyTicketWithAttachment('attachments/tickets/sample.pdf', $owner);

        $this->actingAs($admin);
        $response = $this->get(route('tickets.attachment', $ticket));
        $response->assertOk();
    }

    public function test_padal_and_teknisi_authorization_for_ticket_attachments(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_USER]);

        $padalUserAssigned = User::factory()->create(['role' => User::ROLE_PADAL]);
        $padalAssigned = Padal::create([
            'user_id' => $padalUserAssigned->id,
            'nama_padal' => 'Unit Assigned',
            'deskripsi' => 'Deskripsi Unit',
            'status' => 'aktif',
        ]);

        $padalUserOther = User::factory()->create(['role' => User::ROLE_PADAL]);
        Padal::create([
            'user_id' => $padalUserOther->id,
            'nama_padal' => 'Unit Lain',
            'deskripsi' => 'Deskripsi Unit Lain',
            'status' => 'aktif',
        ]);

        $teknisiAssigned = User::factory()->create(['role' => User::ROLE_TEKNISI]);
        $teknisiOther = User::factory()->create(['role' => User::ROLE_TEKNISI]);

        $ticket = $this->createDummyTicketWithAttachment('attachments/tickets/sample.pdf', $owner);
        $ticket->padal_id = $padalAssigned->id;
        $ticket->save();

        TicketTechnicianAssignment::create([
            'ticket_id' => $ticket->id,
            'user_id' => $teknisiAssigned->id,
            'assigned_by' => $padalUserAssigned->id,
            'assigned_at' => now(),
        ]);

        // Padal assigned -> OK
        $this->actingAs($padalUserAssigned);
        $this->get(route('tickets.attachment', $ticket))->assertOk();

        // Padal not assigned -> 403 Forbidden
        $this->actingAs($padalUserOther);
        $this->get(route('tickets.attachment', $ticket))->assertForbidden();

        // Teknisi assigned -> OK
        $this->actingAs($teknisiAssigned);
        $this->get(route('tickets.attachment', $ticket))->assertOk();

        // Teknisi other -> 403 Forbidden
        $this->actingAs($teknisiOther);
        $this->get(route('tickets.attachment', $ticket))->assertForbidden();
    }

    public function test_attachment_migration_command_dry_run_and_execution(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_USER]);

        // Siapkan file di disk publik
        $fileRelativePath = 'attachments/tickets/test_migration.txt';
        $fileContent = 'Konten berkas lampiran rahasia';
        Storage::disk('public')->put($fileRelativePath, $fileContent);

        $ticket = $this->createDummyTicketWithAttachment($fileRelativePath, $owner, false);

        // 1. Dry run: berkas belum disalin ke privat
        $this->artisan('tickets:migrate-attachments', ['--dry-run' => true])
            ->assertExitCode(0);
        $this->assertFalse(Storage::disk('local')->exists($fileRelativePath));
        $this->assertTrue(Storage::disk('public')->exists($fileRelativePath));

        // 2. Eksekusi nyata: berkas disalin dan diverifikasi hash SHA-256
        $this->artisan('tickets:migrate-attachments')
            ->assertExitCode(0);

        $this->assertTrue(Storage::disk('local')->exists($fileRelativePath));
        $this->assertSame($fileContent, Storage::disk('local')->get($fileRelativePath));
        // Berkas publik tetap ada karena --delete-source tidak dipakai
        $this->assertTrue(Storage::disk('public')->exists($fileRelativePath));
    }

    // =========================================================================
    // TEMUAN 1: Perlindungan Akun Produksi & Pemisahan Seeder
    // =========================================================================

    public function test_database_seeder_does_not_overwrite_existing_admin_password(): void
    {
        $existingAdmin = User::create([
            'name' => 'Admin Eksisting',
            'email' => 'admin@polda.go.id',
            'password' => Hash::make('CustomSecureAdminPass123!'),
            'role' => User::ROLE_ADMIN,
            'nrp_nip' => '198801012010121001',
            'phone_number' => '081234567890',
            'satuan_kerja' => 'Bid TIK POLDA',
            'is_active' => true,
            'approval_status' => User::APPROVAL_ACTIVE,
        ]);

        // Jalankan DatabaseSeeder
        $seeder = new DatabaseSeeder();
        $seeder->run();

        // Password tidak boleh tertimpa kembali ke default
        $freshAdmin = User::where('email', 'admin@polda.go.id')->firstOrFail();
        $this->assertTrue(Hash::check('CustomSecureAdminPass123!', $freshAdmin->password));
    }

    public function test_demo_seeder_aborts_in_production_environment(): void
    {
        $this->app['env'] = 'production';

        $demoSeeder = new DemoDataSeeder();
        $demoSeeder->run();

        // Akun demo seperti user@polda.go.id tidak boleh dibuat
        $this->assertDatabaseMissing('users', ['email' => 'user@polda.go.id']);
    }

    // =========================================================================
    // Helper
    // =========================================================================

    protected function createDummyTicketWithAttachment(string $path, ?User $user = null, bool $seedLocal = true): Ticket
    {
        $user = $user ?? User::factory()->create(['role' => User::ROLE_USER]);
        $category = Category::firstOrCreate([
            'slug' => 'jaringan-internet',
        ], [
            'name' => 'Jaringan & Internet',
            'icon' => 'bi-wifi',
            'description' => 'Test',
            'is_active' => true,
        ]);

        if ($seedLocal) {
            Storage::disk('local')->put($path, 'sample attachment content');
        }

        return Ticket::create([
            'ticket_number' => 'TKT-20261124-TEST-' . rand(100, 999),
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => 'Kendala Jaringan',
            'location' => 'Lantai 2',
            'description' => 'Deskripsi kendala jaringan',
            'status' => Ticket::STATUS_MENUNGGU_VERIFIKASI,
            'attachment' => $path,
        ]);
    }
}
