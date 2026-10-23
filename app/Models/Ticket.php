<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Ticket extends Model
{
    use HasFactory;

    protected $table = 'tickets';

    // Daftar status laporan LayanIT
    public const STATUS_MENUNGGU_VERIFIKASI = 'menunggu_verifikasi';
    public const STATUS_DITOLAK_ADMIN = 'ditolak_admin';
    public const STATUS_MENUNGGU_KONFIRMASI_PADAL = 'menunggu_konfirmasi_padal';
    public const STATUS_DITOLAK_PADAL = 'ditolak_padal';
    public const STATUS_DALAM_PENANGANAN = 'dalam_penanganan';
    public const STATUS_SELESAI = 'selesai';
    public const STATUS_SELESAI_DINILAI = 'selesai_dinilai';

    protected $fillable = [
        'ticket_number',         // nomor_tiket
        'user_id',               // id_user (pelapor)
        'category_id',           // id_kategori
        'padal_id',              // id_padal (entitas PADAL)
        'diperiksa_oleh',        // diperiksa_oleh (admin)
        'ditugaskan_oleh',       // ditugaskan_oleh (admin)
        'title',                 // judul
        'location',              // lokasi
        'description',           // deskripsi
        'status',                // status
        'rejection_reason',      // alasan_penolakan
        'attachment',            // lampiran
        'admin_notes',
        'resolution_notes',
        'resolution_attachment',
        'verified_at',
        'assigned_at',
        'started_at',
        'completed_at',          // tanggal_selesai
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
            'assigned_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Label status bahasa Indonesia yang rapi dan konsisten
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_MENUNGGU_VERIFIKASI => 'Menunggu Verifikasi Admin',
            self::STATUS_DITOLAK_ADMIN => 'Ditolak Admin',
            self::STATUS_MENUNGGU_KONFIRMASI_PADAL => 'Menunggu Konfirmasi Padal',
            self::STATUS_DITOLAK_PADAL => 'Ditolak Padal',
            self::STATUS_DALAM_PENANGANAN => 'Dalam Penanganan',
            self::STATUS_SELESAI => 'Selesai (Menunggu Penilaian)',
            self::STATUS_SELESAI_DINILAI => 'Selesai & Telah Dinilai',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    /**
     * Badge CSS class untuk tampilan Bootstrap
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_MENUNGGU_VERIFIKASI => 'badge bg-warning text-dark',
            self::STATUS_DITOLAK_ADMIN => 'badge bg-danger',
            self::STATUS_MENUNGGU_KONFIRMASI_PADAL => 'badge bg-info text-dark',
            self::STATUS_DITOLAK_PADAL => 'badge bg-secondary',
            self::STATUS_DALAM_PENANGANAN => 'badge bg-primary',
            self::STATUS_SELESAI => 'badge bg-success',
            self::STATUS_SELESAI_DINILAI => 'badge bg-dark',
            default => 'badge bg-light text-dark',
        };
    }

    // -------------------------------------------------------------
    // Relasi Eloquent Sesuai ERD
    // -------------------------------------------------------------

    /**
     * USER membuat(laporan) TIKET (Pelapor)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pelapor(): BelongsTo
    {
        return $this->user();
    }

    /**
     * KATEGORI_GANGGUAN memiliki TIKET
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function kategori(): BelongsTo
    {
        return $this->category();
    }

    /**
     * PADAL menangani TIKET
     */
    public function padal(): BelongsTo
    {
        return $this->belongsTo(Padal::class, 'padal_id');
    }

    /**
     * USER memeriksa(laporan) TIKET
     */
    public function diperiksaOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diperiksa_oleh');
    }

    /**
     * USER menugaskan(padal) TIKET
     */
    public function ditugaskanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ditugaskan_oleh');
    }

    /**
     * TIKET mendapatkan RATING
     */
    public function rating(): HasOne
    {
        return $this->hasOne(TicketRating::class, 'ticket_id');
    }

    /**
     * TIKET memiliki RIWAYAT_STATUS
     */
    public function histories(): HasMany
    {
        return $this->hasMany(TicketStatusHistory::class, 'ticket_id')->latest();
    }

    public function riwayatStatus(): HasMany
    {
        return $this->histories();
    }

    // Accessors atribut ERD
    public function getIdTiketAttribute()
    {
        return $this->id;
    }

    public function getNomorTiketAttribute()
    {
        return $this->ticket_number;
    }

    public function getJudulAttribute()
    {
        return $this->title;
    }

    public function getLokasiAttribute()
    {
        return $this->location;
    }

    public function getDeskripsiAttribute()
    {
        return $this->description;
    }

    public function getAlasanPenolakanAttribute()
    {
        return $this->rejection_reason;
    }

    public function getLampiranAttribute()
    {
        return $this->attachment;
    }

    public function getTanggalLaporanAttribute()
    {
        return $this->created_at;
    }

    public function getTanggalSelesaiAttribute()
    {
        return $this->completed_at;
    }
}
