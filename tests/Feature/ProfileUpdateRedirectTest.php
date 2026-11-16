<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProfileUpdateRedirectTest extends TestCase
{
    protected const SUCCESS_MESSAGE = 'Profil Anda berhasil diperbarui.';

    protected User $admin;
    protected User $padal;
    protected User $teknisi;
    protected User $user;

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
        $this->user = $this->createUser(User::ROLE_USER, 'user');
    }

    /**
     * Kondisi 1: Login sebagai Admin, ubah data profil, simpan -> kembali ke Dashboard Admin.
     */
    public function test_admin_profile_update_redirects_to_admin_dashboard(): void
    {
        $this->actingAs($this->admin);

        $response = $this->from(route('profile.show'))
            ->put(route('profile.update'), [
                'name' => 'Admin Updated Name',
                'email' => $this->admin->email,
                'phone_number' => '081111111111',
                'alamat' => 'Polda Sultra Ruang Admin',
                'satuan_kerja' => 'Bid TIK Admin',
                'nrp_nip' => 'NRP-ADMIN-01',
            ]);

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('success', self::SUCCESS_MESSAGE);

        // Pastikan perubahan data tersimpan di database
        $this->admin->refresh();
        $this->assertSame('Admin Updated Name', $this->admin->name);
        $this->assertSame('081111111111', $this->admin->phone_number);
        $this->assertSame('Polda Sultra Ruang Admin', $this->admin->alamat);
        $this->assertSame('Bid TIK Admin', $this->admin->satuan_kerja);
        $this->assertSame('NRP-ADMIN-01', $this->admin->nrp_nip);

        // Notifikasi sukses tampil di Dashboard
        $dashboardResponse = $this->get(route('admin.dashboard'));
        $dashboardResponse->assertOk();

        // Data baru tampil ketika membuka profil kembali
        $profileResponse = $this->get(route('profile.show'));
        $profileResponse->assertOk();
        $profileResponse->assertSee('Admin Updated Name');
        $profileResponse->assertSee('081111111111');
        $profileResponse->assertSee('NRP-ADMIN-01');
    }

    /**
     * Kondisi 2: Login sebagai Padal, ubah data profil, simpan -> kembali ke Dashboard Padal.
     */
    public function test_padal_profile_update_redirects_to_padal_dashboard(): void
    {
        $this->actingAs($this->padal);

        $response = $this->from(route('profile.show'))
            ->put(route('profile.update'), [
                'name' => 'Padal Updated Name',
                'phone_number' => '082222222222',
                'alamat' => 'Polda Sultra Pos Padal',
                'satuan_kerja' => 'Subbag Renmin Padal',
                'nrp_nip' => 'NRP-PADAL-01',
            ]);

        $response->assertRedirect(route('padal.dashboard'));
        $response->assertSessionHas('success', self::SUCCESS_MESSAGE);

        // Pastikan perubahan data tersimpan di database
        $this->padal->refresh();
        $this->assertSame('Padal Updated Name', $this->padal->name);
        $this->assertSame('082222222222', $this->padal->phone_number);
        $this->assertSame('Polda Sultra Pos Padal', $this->padal->alamat);
        $this->assertSame('Subbag Renmin Padal', $this->padal->satuan_kerja);
        $this->assertSame('NRP-PADAL-01', $this->padal->nrp_nip);

        // Notifikasi sukses tampil di Dashboard
        $dashboardResponse = $this->get(route('padal.dashboard'));
        $dashboardResponse->assertOk();

        // Data baru tampil ketika membuka profil kembali
        $profileResponse = $this->get(route('profile.show'));
        $profileResponse->assertOk();
        $profileResponse->assertSee('Padal Updated Name');
        $profileResponse->assertSee('082222222222');
        $profileResponse->assertSee('NRP-PADAL-01');
    }

    /**
     * Kondisi 3: Login sebagai Teknisi, ubah data profil, simpan -> kembali ke Dashboard Teknisi.
     */
    public function test_teknisi_profile_update_redirects_to_teknisi_dashboard(): void
    {
        $this->actingAs($this->teknisi);

        $response = $this->from(route('profile.show'))
            ->put(route('profile.update'), [
                'name' => 'Teknisi Updated Name',
                'phone_number' => '083333333333',
                'alamat' => 'Bengkel IT Polda',
                'satuan_kerja' => 'Teknisi Jaringan',
                'nrp_nip' => 'NRP-TEKNISI-01',
            ]);

        $response->assertRedirect(route('teknisi.dashboard'));
        $response->assertSessionHas('success', self::SUCCESS_MESSAGE);

        // Pastikan perubahan data tersimpan di database
        $this->teknisi->refresh();
        $this->assertSame('Teknisi Updated Name', $this->teknisi->name);
        $this->assertSame('083333333333', $this->teknisi->phone_number);
        $this->assertSame('Bengkel IT Polda', $this->teknisi->alamat);
        $this->assertSame('Teknisi Jaringan', $this->teknisi->satuan_kerja);
        $this->assertSame('NRP-TEKNISI-01', $this->teknisi->nrp_nip);

        // Notifikasi sukses tampil di Dashboard
        $dashboardResponse = $this->get(route('teknisi.dashboard'));
        $dashboardResponse->assertOk();

        // Data baru tampil ketika membuka profil kembali
        $profileResponse = $this->get(route('profile.show'));
        $profileResponse->assertOk();
        $profileResponse->assertSee('Teknisi Updated Name');
        $profileResponse->assertSee('083333333333');
        $profileResponse->assertSee('NRP-TEKNISI-01');
    }

    /**
     * Kondisi 4: Login sebagai User/Pelapor, ubah data profil, simpan -> kembali ke Dashboard User.
     */
    public function test_user_profile_update_redirects_to_user_dashboard(): void
    {
        $this->actingAs($this->user);

        $response = $this->from(route('profile.show'))
            ->put(route('profile.update'), [
                'name' => 'Pelapor Updated Name',
                'phone_number' => '084444444444',
                'alamat' => 'Gedung SPKT Lantai 1',
                'satuan_kerja' => 'SPKT Polda',
                'nrp_nip' => 'NRP-USER-01',
            ]);

        $response->assertRedirect(route('user.dashboard'));
        $response->assertSessionHas('success', self::SUCCESS_MESSAGE);

        // Pastikan perubahan data tersimpan di database
        $this->user->refresh();
        $this->assertSame('Pelapor Updated Name', $this->user->name);
        $this->assertSame('084444444444', $this->user->phone_number);
        $this->assertSame('Gedung SPKT Lantai 1', $this->user->alamat);
        $this->assertSame('SPKT Polda', $this->user->satuan_kerja);
        $this->assertSame('NRP-USER-01', $this->user->nrp_nip);

        // Notifikasi sukses tampil di Dashboard
        $dashboardResponse = $this->get(route('user.dashboard'));
        $dashboardResponse->assertOk();

        // Data baru tampil ketika membuka profil kembali
        $profileResponse = $this->get(route('profile.show'));
        $profileResponse->assertOk();
        $profileResponse->assertSee('Pelapor Updated Name');
        $profileResponse->assertSee('084444444444');
        $profileResponse->assertSee('NRP-USER-01');
    }

    /**
     * Kondisi 7: Jika validasi gagal (contoh: nama kosong), pengguna tetap berada di halaman Profil.
     */
    public function test_validation_failure_keeps_user_on_profile_page(): void
    {
        $this->actingAs($this->user);

        $response = $this->from(route('profile.show'))
            ->put(route('profile.update'), [
                'name' => '', // Nama wajib diisi
                'phone_number' => '085555555555',
            ]);

        // Tetap berada di halaman profil
        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHasErrors('name');

        // Pastikan data tidak berubah di database
        $this->assertNotSame('', $this->user->fresh()->name);
    }

    /**
     * Jika password lama salah saat ubah password, tetap berada di halaman Profil.
     */
    public function test_wrong_current_password_keeps_user_on_profile_page(): void
    {
        $this->actingAs($this->user);

        $response = $this->from(route('profile.show'))
            ->put(route('profile.update'), [
                'name' => 'User Test',
                'current_password' => 'wrong-current-password',
                'new_password' => 'newpassword123',
                'new_password_confirmation' => 'newpassword123',
            ]);

        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHasErrors('current_password');
    }

    private function createUser(string $role, string $emailPrefix): User
    {
        return User::factory()->create([
            'name' => ucfirst($emailPrefix) . ' Original',
            'email' => $emailPrefix . '@example.test',
            'password' => bcrypt('password'),
            'role' => $role,
            'is_active' => true,
            'approval_status' => User::APPROVAL_ACTIVE,
            'requires_email_verification' => false,
            'email_verified_at' => now(),
            'satuan_kerja' => 'Satker Awal',
            'nrp_nip' => 'NRP-AWAL',
            'phone_number' => '081200000000',
            'alamat' => 'Alamat Awal',
        ]);
    }
}
