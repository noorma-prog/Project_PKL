<?php

namespace App\Http\Controllers;

use App\Models\Padal;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TicketAttachmentController extends Controller
{
    /**
     * Tampilkan atau unduh berkas lampiran tiket pelapor (attachment).
     */
    public function showAttachment(Ticket $ticket)
    {
        $this->authorizeAccess($ticket);

        return $this->serveFile($ticket->attachment, $ticket->ticket_number . '-lampiran');
    }

    /**
     * Tampilkan atau unduh berkas bukti penyelesaian tiket (resolution_attachment).
     */
    public function showResolutionAttachment(Ticket $ticket)
    {
        $this->authorizeAccess($ticket);

        return $this->serveFile($ticket->resolution_attachment, $ticket->ticket_number . '-bukti-penyelesaian');
    }

    /**
     * Otorisasi ketat akses berkas lampiran tiket.
     */
    protected function authorizeAccess(Ticket $ticket): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            abort(401, 'Silakan masuk terlebih dahulu untuk mengakses lampiran.');
        }

        // 1. Admin berhak mengakses semua lampiran tiket
        if ($user->isAdmin()) {
            return;
        }

        // 2. User/Pelapor hanya boleh mengakses lampiran tiket miliknya sendiri
        if ($user->isUser() && (int) $ticket->user_id === (int) $user->id) {
            return;
        }

        // 3. Padal hanya boleh mengakses tiket yang ditugaskan ke unitnya
        if ($user->isPadal()) {
            $padal = Padal::where('user_id', $user->id)->first();
            if ($padal && (int) $ticket->padal_id === (int) $padal->id) {
                return;
            }
        }

        // 4. Teknisi hanya boleh mengakses tiket yang sedang atau pernah ditugaskan kepadanya
        if ($user->isTeknisi()) {
            $isAssigned = $ticket->technicians()->where('users.id', $user->id)->exists()
                || $ticket->technicianAssignments()->where('user_id', $user->id)->exists();

            if ($isAssigned) {
                return;
            }
        }

        // Pengguna lain ditolak
        abort(403, 'Anda tidak memiliki hak akses untuk melihat berkas lampiran tiket ini.');
    }

    /**
     * Sajikan berkas lampiran dari disk privat (local) dengan fallback ke public disk.
     */
    protected function serveFile(?string $path, string $downloadBaseName)
    {
        if (empty($path)) {
            abort(404, 'Berkas lampiran tidak ditemukan pada tiket ini.');
        }

        $fullPath = null;
        $mimeType = null;

        // 1. Periksa pada penyimpanan privat (disk 'local' / storage/app/private)
        if (Storage::disk('local')->exists($path)) {
            $fullPath = Storage::disk('local')->path($path);
            $mimeType = Storage::disk('local')->mimeType($path);
        }
        // 2. Fallback untuk berkas warisan di penyimpanan publik yang belum dimigrasi
        elseif (Storage::disk('public')->exists($path)) {
            $fullPath = Storage::disk('public')->path($path);
            $mimeType = Storage::disk('public')->mimeType($path);
        }

        if (! $fullPath || ! file_exists($fullPath)) {
            abort(404, 'Berkas fisik lampiran tidak ditemukan di penyimpanan server.');
        }

        $extension = pathinfo($fullPath, PATHINFO_EXTENSION);
        $fileName = $downloadBaseName . ($extension ? '.' . $extension : '');

        return response()->file($fullPath, [
            'Content-Type' => $mimeType ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="' . $fileName . '"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}

