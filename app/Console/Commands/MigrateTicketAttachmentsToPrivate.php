<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateTicketAttachmentsToPrivate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tickets:migrate-attachments 
                            {--dry-run : Jalankan simulasi tanpa memindahkan berkas fisik} 
                            {--delete-source : Hapus berkas asli di storage publik setelah verifikasi integritas berhasil}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrasi berkas lampiran tiket dari penyimpanan publik ke penyimpanan privat dengan verifikasi hash integritas.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        $deleteSource = $this->option('delete-source');

        $this->info('===========================================================');
        $this->info('  MIGRASI LAMPIRAN TIKET: PUBLIC -> PRIVATE (LOCAL DISK)   ');
        $this->info('===========================================================');

        if ($isDryRun) {
            $this->warn('MODE SIMULASI (--dry-run) AKTIF: Berkas fisik tidak akan diubah.');
        }

        $tickets = Ticket::whereNotNull('attachment')
            ->orWhereNotNull('resolution_attachment')
            ->get();

        $this->info("Menemukan {$tickets->count()} tiket yang memiliki catatan berkas lampiran.");

        $migratedCount = 0;
        $alreadyPrivateCount = 0;
        $notFoundCount = 0;
        $failedVerificationCount = 0;

        foreach ($tickets as $ticket) {
            // 1. Periksa lampiran pelapor (attachment)
            if (! empty($ticket->attachment)) {
                $status = $this->processFile($ticket->attachment, "Tiket #{$ticket->ticket_number} (Lampiran Pelapor)", $isDryRun, $deleteSource);
                $this->accumulateCount($status, $migratedCount, $alreadyPrivateCount, $notFoundCount, $failedVerificationCount);
            }

            // 2. Periksa bukti penyelesaian (resolution_attachment)
            if (! empty($ticket->resolution_attachment)) {
                $status = $this->processFile($ticket->resolution_attachment, "Tiket #{$ticket->ticket_number} (Bukti Penyelesaian)", $isDryRun, $deleteSource);
                $this->accumulateCount($status, $migratedCount, $alreadyPrivateCount, $notFoundCount, $failedVerificationCount);
            }
        }

        $this->newLine();
        $this->info('--- RINGKASAN MIGRASI ---');
        $this->line("Total Berkas Berhasil Dipindahkan / Perlu Dipindahkan: {$migratedCount}");
        $this->line("Berkas Sudah Berada di Penyimpanan Privat: {$alreadyPrivateCount}");
        $this->line("Berkas Fisik Tidak Ditemukan: {$notFoundCount}");

        if ($failedVerificationCount > 0) {
            $this->error("Peringatan: {$failedVerificationCount} berkas gagal diverifikasi hash keutuhannya.");
            return Command::FAILURE;
        }

        $this->info('Proses selesai dengan sukses.');
        return Command::SUCCESS;
    }

    /**
     * Proses pemindahan satu berkas.
     */
    protected function processFile(string $relativePath, string $label, bool $isDryRun, bool $deleteSource): string
    {
        $publicDisk = Storage::disk('public');
        $privateDisk = Storage::disk('local');

        $existsPublic = $publicDisk->exists($relativePath);
        $existsPrivate = $privateDisk->exists($relativePath);

        if (! $existsPublic && ! $existsPrivate) {
            $this->warn("[TIDAK DITEMUKAN] {$label}: {$relativePath} tidak ada di public maupun private disk.");
            return 'not_found';
        }

        if ($existsPrivate && ! $existsPublic) {
            $this->line("[SUDAH PRIVAT] {$label}: {$relativePath} sudah aman di penyimpanan privat.");
            return 'already_private';
        }

        // Berkas ada di publik (dan mungkin belum ada di privat, atau perlu sinkronisasi)
        $publicSize = $publicDisk->size($relativePath);
        $publicContent = $publicDisk->get($relativePath);
        $publicHash = hash('sha256', $publicContent);

        if ($isDryRun) {
            $this->info("[SIMULASI SALIN] {$label}: {$relativePath} ({$publicSize} bytes, SHA256: " . substr($publicHash, 0, 12) . "...) -> storage/app/private/{$relativePath}");
            return 'migrated';
        }

        // Tulis ke privat
        $saved = $privateDisk->put($relativePath, $publicContent);
        if (! $saved) {
            $this->error("[GAGAL TULIS] {$label}: Gagal menulis ke penyimpanan privat {$relativePath}.");
            return 'failed_verification';
        }

        // Verifikasi integritas checksum SHA-256
        $privateContent = $privateDisk->get($relativePath);
        $privateHash = hash('sha256', $privateContent);

        if ($publicHash !== $privateHash) {
            $this->error("[GAGAL INTEGRITAS] {$label}: Hash SHA-256 tidak cocok! Membatalkan pemindahan berkas.");
            $privateDisk->delete($relativePath);
            return 'failed_verification';
        }

        $this->info("[BERHASIL MIGRASI] {$label}: {$relativePath} berhasil dipindahkan dan diverifikasi (SHA256 Cocok).");

        // Hapus file sumber publik hanya jika opsi --delete-source diaktifkan secara eksplisit
        if ($deleteSource) {
            $publicDisk->delete($relativePath);
            $this->comment("  -> Berkas lama di public disk telah dihapus.");
        } else {
            $this->comment("  -> Berkas lama di public disk dipertahankan untuk keamanan.");
        }

        return 'migrated';
    }

    protected function accumulateCount(string $status, int &$migrated, int &$alreadyPrivate, int &$notFound, int &$failed): void
    {
        match ($status) {
            'migrated' => $migrated++,
            'already_private' => $alreadyPrivate++,
            'not_found' => $notFound++,
            'failed_verification' => $failed++,
            default => null,
        };
    }
}

