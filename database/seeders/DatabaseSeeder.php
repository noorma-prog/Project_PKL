<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Kategori Gangguan IT di Lingkungan POLDA (Master Data)
        $categories = [
            [
                'name' => 'Jaringan & Internet',
                'slug' => 'jaringan-internet',
                'icon' => 'bi-wifi',
                'description' => 'Gangguan koneksi internet kantor, kabel LAN satker, WiFi dinas, dan akses intranet Mabes Polri.',
                'is_active' => true,
            ],
            [
                'name' => 'Perangkat Komputer & Laptop',
                'slug' => 'perangkat-komputer-laptop',
                'icon' => 'bi-pc-display',
                'description' => 'Kerusakan PC dinas, laptop dinas personil, monitor mati, lambat, bluescreen, atau mati total.',
                'is_active' => true,
            ],
            [
                'name' => 'Sistem Informasi & Aplikasi',
                'slug' => 'sistem-informasi-aplikasi',
                'icon' => 'bi-window-stack',
                'description' => 'Kendala pada aplikasi kedinasan seperti E-Penyidikan, E-KTA, Presensi Online, SKCK, dan website.',
                'is_active' => true,
            ],
            [
                'name' => 'Printer, Scanner & Periferal',
                'slug' => 'printer-scanner-periferal',
                'icon' => 'bi-printer',
                'description' => 'Kerusakan printer cetak dokumen operasional, scanner barcode, paper jam, atau tinta error.',
                'is_active' => true,
            ],
            [
                'name' => 'CCTV & Radio Komunikasi (HT)',
                'slug' => 'cctv-radio-komunikasi',
                'icon' => 'bi-camera-video',
                'description' => 'Gangguan kamera pengawas CCTV command center / ruangan, radio HT, dan repeater frekuensi.',
                'is_active' => true,
            ],
            [
                'name' => 'Server & Pusat Data',
                'slug' => 'server-pusat-data',
                'icon' => 'bi-hdd-network',
                'description' => 'Kendala server penyimpanan satker, hak akses database, backup berkas dan lisensi sistem.',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(['name' => $cat['name']], $cat);
        }

        // 2. Akun Administrator Awal (Produksi & Fresh Install)
        // PERLINDUNGAN: Jangan menimpa akun Admin jika sudah ada di database.
        $existingAdmin = User::where('role', User::ROLE_ADMIN)->first();

        if (! $existingAdmin) {
            $adminInitialPassword = env('ADMIN_INITIAL_PASSWORD');
            if (empty($adminInitialPassword)) {
                $adminInitialPassword = Str::password(16, letters: true, numbers: true, symbols: true, spaces: false);
                $this->command?->info("Akun Admin awal berhasil dibuat: admin@polda.go.id | Kata sandi sementara: {$adminInitialPassword}");
            }

            User::create([
                'name' => 'Administrator TIK POLDA',
                'email' => 'admin@polda.go.id',
                'password' => Hash::make($adminInitialPassword),
                'role' => User::ROLE_ADMIN,
                'nrp_nip' => '198801012010121001',
                'phone_number' => '081234567890',
                'alamat' => 'Gedung Bid TIK POLDA Lt. 2, Jl. Sudirman No. 1',
                'satuan_kerja' => 'Bid TIK POLDA',
                'is_active' => true,
                'approval_status' => User::APPROVAL_ACTIVE,
            ]);
        }

        // 3. Akun dan Data Demo (Hanya berjalan di lingkungan lokal / testing)
        if (app()->environment('local', 'testing')) {
            $this->call(DemoDataSeeder::class);
        }
    }
}
