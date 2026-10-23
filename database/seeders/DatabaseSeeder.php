<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Padal;
use App\Models\PadalAnggota;
use App\Models\Ticket;
use App\Models\TicketRating;
use App\Models\TicketStatusHistory;
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
        // 1. Kategori Gangguan IT di Lingkungan POLDA (KATEGORI_GANGGUAN)
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

        $createdCategories = [];
        foreach ($categories as $cat) {
            $createdCategories[$cat['slug']] = Category::updateOrCreate(['name' => $cat['name']], $cat);
        }

        // 2. Akun Bawaan untuk Setiap Role (USER)
        $defaultPassword = Hash::make('password');

        // Admin
        $admin = User::updateOrCreate(
            ['email' => 'admin@polda.go.id'],
            [
                'name' => 'Administrator TIK POLDA',
                'password' => $defaultPassword,
                'role' => User::ROLE_ADMIN,
                'nrp_nip' => '198801012010121001',
                'phone_number' => '081234567890',
                'alamat' => 'Gedung Bid TIK POLDA Lt. 2, Jl. Sudirman No. 1',
                'satuan_kerja' => 'Bid TIK POLDA',
                'is_active' => true,
            ]
        );

        // Padal 1 (Perwira Pengendali)
        $padalUser1 = User::updateOrCreate(
            ['email' => 'padal@polda.go.id'],
            [
                'name' => 'AKP Budi Santoso, S.Kom',
                'password' => $defaultPassword,
                'role' => User::ROLE_PADAL,
                'nrp_nip' => '82051234',
                'phone_number' => '081234567891',
                'alamat' => 'Komplek Asrama POLDA Blok A-12',
                'satuan_kerja' => 'Bid TIK POLDA',
                'is_active' => true,
            ]
        );

        // Padal 2
        $padalUser2 = User::updateOrCreate(
            ['email' => 'padal2@polda.go.id'],
            [
                'name' => 'IPTU Hendra Wijaya, S.T',
                'password' => $defaultPassword,
                'role' => User::ROLE_PADAL,
                'nrp_nip' => '85091122',
                'phone_number' => '081234567892',
                'alamat' => 'Komplek Asrama POLDA Blok B-05',
                'satuan_kerja' => 'Bid TIK POLDA',
                'is_active' => true,
            ]
        );

        // Teknisi 1
        $teknisi1 = User::updateOrCreate(
            ['email' => 'teknisi1@polda.go.id'],
            [
                'name' => 'Bripka Ahmad Fauzi (Teknisi Jaringan)',
                'password' => $defaultPassword,
                'role' => User::ROLE_TEKNISI,
                'nrp_nip' => '90040567',
                'phone_number' => '081234567893',
                'alamat' => 'Jl. Bhayangkara No. 45',
                'satuan_kerja' => 'Subbid Tekkom Bid TIK',
                'is_active' => true,
            ]
        );

        // Teknisi 2
        $teknisi2 = User::updateOrCreate(
            ['email' => 'teknisi2@polda.go.id'],
            [
                'name' => 'Briptu Dian Pratama (Teknisi Hardware/Sistem)',
                'password' => $defaultPassword,
                'role' => User::ROLE_TEKNISI,
                'nrp_nip' => '94080123',
                'phone_number' => '081234567894',
                'alamat' => 'Jl. Tribrata No. 18',
                'satuan_kerja' => 'Subbid Tekinfo Bid TIK',
                'is_active' => true,
            ]
        );

        // Teknisi 3
        $teknisi3 = User::updateOrCreate(
            ['email' => 'teknisi3@polda.go.id'],
            [
                'name' => 'Pengatur I Siti Rahma (Teknisi Aplikasi)',
                'password' => $defaultPassword,
                'role' => User::ROLE_TEKNISI,
                'nrp_nip' => '199503152020122002',
                'phone_number' => '081234567895',
                'alamat' => 'Jl. Cenderawasih No. 9',
                'satuan_kerja' => 'Subbid Tekinfo Bid TIK',
                'is_active' => true,
            ]
        );

        // User 1 (Pelapor SPKT)
        $user1 = User::updateOrCreate(
            ['email' => 'user@polda.go.id'],
            [
                'name' => 'Aipda Bambang Sukoco',
                'password' => $defaultPassword,
                'role' => User::ROLE_USER,
                'nrp_nip' => '86010789',
                'phone_number' => '081234567896',
                'alamat' => 'Gedung SPKT POLDA, Ruang Pelayanan Terpadu',
                'satuan_kerja' => 'SPKT POLDA',
                'is_active' => true,
            ]
        );

        // User 2 (Pelapor Ditlantas)
        $user2 = User::updateOrCreate(
            ['email' => 'user2@polda.go.id'],
            [
                'name' => 'Brigadir Rina Septiani',
                'password' => $defaultPassword,
                'role' => User::ROLE_USER,
                'nrp_nip' => '92110456',
                'phone_number' => '081234567897',
                'alamat' => 'Gedung Ditlantas POLDA, Bagian SIM & Tilang',
                'satuan_kerja' => 'Ditlantas POLDA',
                'is_active' => true,
            ]
        );

        // 3. Entitas PADAL & PADAL_ANGGOTA
        // Padal Unit 1 dipimpin oleh AKP Budi Santoso
        $padal1 = Padal::updateOrCreate(
            ['nama_padal' => 'Regu Padal Alpha (Jaringan & Infrastruktur)'],
            [
                'user_id' => $padalUser1->id,
                'deskripsi' => 'Regu Perwira Pengendali penanganan insiden infrastruktur jaringan, server, fiber optic, dan WiFi seluruh satker POLDA.',
                'status' => 'aktif',
            ]
        );

        // Padal Unit 2 dipimpin oleh IPTU Hendra Wijaya
        $padal2 = Padal::updateOrCreate(
            ['nama_padal' => 'Regu Padal Bravo (Sistem Informasi & Hardware)'],
            [
                'user_id' => $padalUser2->id,
                'deskripsi' => 'Regu Perwira Pengendali penanganan perangkat PC dinas, laptop pimpinan, sistem basis data, dan aplikasi kedinasan.',
                'status' => 'aktif',
            ]
        );

        // Anggota Padal 1: Teknisi 1 & Teknisi 2
        PadalAnggota::updateOrCreate(
            ['padal_id' => $padal1->id, 'user_id' => $teknisi1->id],
            ['tanggal_bergabung' => now()]
        );
        PadalAnggota::updateOrCreate(
            ['padal_id' => $padal1->id, 'user_id' => $teknisi2->id],
            ['tanggal_bergabung' => now()]
        );

        // Anggota Padal 2: Teknisi 2 & Teknisi 3
        PadalAnggota::updateOrCreate(
            ['padal_id' => $padal2->id, 'user_id' => $teknisi2->id],
            ['tanggal_bergabung' => now()]
        );
        PadalAnggota::updateOrCreate(
            ['padal_id' => $padal2->id, 'user_id' => $teknisi3->id],
            ['tanggal_bergabung' => now()]
        );

        // 4. Contoh Data Tiket Alur Kerja Lengkap Sesuai ERD & Use Case
        // Tiket 1: Selesai & Telah Dinilai oleh Pelapor
        $ticket1 = Ticket::updateOrCreate(
            ['ticket_number' => 'TKT-' . date('Ymd') . '-A101'],
            [
                'user_id' => $user1->id,
                'category_id' => $createdCategories['jaringan-internet']->id,
                'padal_id' => $padal1->id,
                'diperiksa_oleh' => $admin->id,
                'ditugaskan_oleh' => $admin->id,
                'title' => 'Koneksi Internet Putus di Ruang Pelayanan SPKT',
                'location' => 'Gedung SPKT Lt. 1 Ruang 102',
                'description' => 'Akses internet dinas dan jaringan intranet e-penyidikan terputus sejak pagi, kabel LAN tampak longgar dan router indikator merah.',
                'status' => Ticket::STATUS_SELESAI_DINILAI,
                'admin_notes' => 'Prioritas penanganan darurat karena berkaitan langsung dengan pelayanan publik SPKT.',
                'resolution_notes' => 'Kabel LAN utama diganti dan port switch di-reset. Koneksi internet kembali normal dan stabil 100 Mbps.',
                'verified_at' => now()->subHours(6),
                'assigned_at' => now()->subHours(6),
                'started_at' => now()->subHours(5),
                'completed_at' => now()->subHours(2),
            ]
        );

        TicketRating::updateOrCreate(
            ['ticket_id' => $ticket1->id],
            [
                'user_id' => $user1->id,
                'padal_id' => $padal1->id,
                'rating' => 5,
                'review' => 'Respon sangat cepat dan masalah langsung teratasi. Pelayanan SPKT dapat berjalan lancar kembali. Terima kasih Padal Alpha!',
            ]
        );

        TicketStatusHistory::updateOrCreate(
            ['ticket_id' => $ticket1->id, 'status_to' => Ticket::STATUS_MENUNGGU_VERIFIKASI],
            [
                'user_id' => $user1->id,
                'status_from' => null,
                'notes' => 'Laporan dibuat oleh pelapor ' . $user1->name,
                'created_at' => now()->subHours(6),
            ]
        );
        TicketStatusHistory::updateOrCreate(
            ['ticket_id' => $ticket1->id, 'status_to' => Ticket::STATUS_MENUNGGU_KONFIRMASI_PADAL],
            [
                'user_id' => $admin->id,
                'status_from' => Ticket::STATUS_MENUNGGU_VERIFIKASI,
                'notes' => 'Laporan dikonfirmasi oleh Admin dan ditugaskan ke ' . $padal1->nama_padal,
                'created_at' => now()->subHours(6),
            ]
        );
        TicketStatusHistory::updateOrCreate(
            ['ticket_id' => $ticket1->id, 'status_to' => Ticket::STATUS_DALAM_PENANGANAN],
            [
                'user_id' => $padalUser1->id,
                'status_from' => Ticket::STATUS_MENUNGGU_KONFIRMASI_PADAL,
                'notes' => 'Penugasan diterima oleh Padal. Mulai pengerjaan di lokasi bersama teknisi.',
                'created_at' => now()->subHours(5),
            ]
        );
        TicketStatusHistory::updateOrCreate(
            ['ticket_id' => $ticket1->id, 'status_to' => Ticket::STATUS_SELESAI],
            [
                'user_id' => $padalUser1->id,
                'status_from' => Ticket::STATUS_DALAM_PENANGANAN,
                'notes' => 'Pekerjaan dinyatakan selesai oleh Padal. Menunggu rating dari pelapor.',
                'created_at' => now()->subHours(2),
            ]
        );
        TicketStatusHistory::updateOrCreate(
            ['ticket_id' => $ticket1->id, 'status_to' => Ticket::STATUS_SELESAI_DINILAI],
            [
                'user_id' => $user1->id,
                'status_from' => Ticket::STATUS_SELESAI,
                'notes' => 'Pelapor memberikan penilaian pelayanan: 5 Bintang. Ulasan: Sangat cepat dan responsif.',
                'created_at' => now()->subHour(),
            ]
        );

        // Tiket 2: Sedang Dalam Penanganan oleh Padal 2
        $ticket2 = Ticket::updateOrCreate(
            ['ticket_number' => 'TKT-' . date('Ymd') . '-B202'],
            [
                'user_id' => $user2->id,
                'category_id' => $createdCategories['perangkat-komputer-laptop']->id,
                'padal_id' => $padal2->id,
                'diperiksa_oleh' => $admin->id,
                'ditugaskan_oleh' => $admin->id,
                'title' => 'Komputer Cetak SIM Ditlantas Mengalami Layar Biru (Bluescreen)',
                'location' => 'Gedung Ditlantas Lt. 2 Bagian SIM',
                'description' => 'Komputer dinas cetak material SIM mendadak restart sendiri dan muncul pesan bluescreen dump error.',
                'status' => Ticket::STATUS_DALAM_PENANGANAN,
                'admin_notes' => 'Segera ditindaklanjuti untuk menghindari antrian pemohon SIM.',
                'verified_at' => now()->subHours(3),
                'assigned_at' => now()->subHours(3),
                'started_at' => now()->subHours(2),
            ]
        );

        TicketStatusHistory::updateOrCreate(
            ['ticket_id' => $ticket2->id, 'status_to' => Ticket::STATUS_DALAM_PENANGANAN],
            [
                'user_id' => $padalUser2->id,
                'status_from' => Ticket::STATUS_MENUNGGU_KONFIRMASI_PADAL,
                'notes' => 'Padal menerima penugasan dan mulai melakukan diagnosa memory RAM dan driver komputer.',
                'created_at' => now()->subHours(2),
            ]
        );

        // Tiket 3: Menunggu Verifikasi Admin (Baru Masuk)
        Ticket::updateOrCreate(
            ['ticket_number' => 'TKT-' . date('Ymd') . '-C303'],
            [
                'user_id' => $user2->id,
                'category_id' => $createdCategories['printer-scanner-periferal']->id,
                'title' => 'Printer HP LaserJet Bagian Tilang Tidak Mau Menarik Kertas (Paper Jam)',
                'location' => 'Ruang Pelayanan Tilang Ditlantas',
                'description' => 'Roller penarik kertas macet dan printer blinking lampu kuning, berkas tilang tidak bisa tercetak.',
                'status' => Ticket::STATUS_MENUNGGU_VERIFIKASI,
                'created_at' => now()->subMinutes(30),
            ]
        );
    }
}
