<?php

namespace App\Models;

use App\Notifications\AccountActivationNotification;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string|null $phone_number
 * @property string|null $alamat
 * @property string|null $nrp_nip
 * @property string|null $satuan_kerja
 * @property bool $is_active
 * @property bool $requires_email_verification
 * @property string|null $email_verified_at
 * @property string|null $remember_token
 * @property string $role
 * @property string $approval_status
 */
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',          // nama
        'email',         // email
        'password',      // password
        'role',          // role: 'admin', 'padal', 'teknisi', 'user'
        'phone_number',  // no_telepon (ERD)
        'alamat',        // alamat (ERD)
        'nrp_nip',       // NRP/NIP
        'satuan_kerja',  // Satuan Kerja / Bagian
        'is_active',
        'approval_status',
        'requires_email_verification',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'requires_email_verification' => 'boolean',
        ];
    }

    // Role Constants
    public const ROLE_USER = 'user';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_PADAL = 'padal';
    public const ROLE_TEKNISI = 'teknisi';

    public const APPROVAL_ACTIVE = 'active';
    public const APPROVAL_PENDING = 'pending';
    public const APPROVAL_REJECTED = 'rejected';

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isPadal(): bool
    {
        return $this->role === self::ROLE_PADAL;
    }

    public function isTeknisi(): bool
    {
        return $this->role === self::ROLE_TEKNISI;
    }

    public function isUser(): bool
    {
        return $this->role === self::ROLE_USER;
    }

    public function isApprovalActive(): bool
    {
        return $this->approval_status === self::APPROVAL_ACTIVE;
    }

    public function isApprovalPending(): bool
    {
        return $this->approval_status === self::APPROVAL_PENDING;
    }

    public function isApprovalRejected(): bool
    {
        return $this->approval_status === self::APPROVAL_REJECTED;
    }

    public function requiresEmailVerification(): bool
    {
        return (bool) $this->requires_email_verification;
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new AccountActivationNotification($token));
    }

    /**
     * Memeriksa apakah User memiliki laporan selesai yang belum diberi rating (Use Case: Cek Rating Tertunda).
     */
    public function hasUnratedCompletedTickets(): bool
    {
        return $this->tickets()
            ->where('status', Ticket::STATUS_SELESAI)
            ->whereDoesntHave('rating')
            ->whereDoesntHave('rating')
            ->exists();
    }

    /**
     * Mengambil tiket yang sedang menunggu rating dari user ini.
     */
    public function getUnratedCompletedTickets()
    {
        return $this->tickets()
            ->where('status', Ticket::STATUS_SELESAI)
            ->whereDoesntHave('rating')
            ->whereDoesntHave('rating')
            ->get();
    }

    // -------------------------------------------------------------
    // Relasi Sesuai ERD
    // -------------------------------------------------------------

    /**
     * USER membuat(laporan) TIKET
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'user_id');
    }

    /**
     * USER memeriksa(laporan) TIKET (Admin)
     */
    public function verifiedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'diperiksa_oleh');
    }

    /**
     * USER menugaskan(padal) TIKET (Admin)
     */
    public function assignedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'ditugaskan_oleh');
    }

    public function technicianTickets(): BelongsToMany
    {
        return $this->belongsToMany(Ticket::class, 'ticket_technician_assignments', 'user_id', 'ticket_id')
            ->withPivot('assigned_by', 'assigned_at', 'released_at')
            ->withTimestamps()
            ->wherePivotNull('released_at');
    }

    public function ticketTechnicianAssignments(): HasMany
    {
        return $this->hasMany(TicketTechnicianAssignment::class, 'user_id');
    }

    /**
     * USER memimpin PADAL (Padal)
     */
    public function padal(): HasOne
    {
        return $this->hasOne(Padal::class, 'user_id');
    }

    public function padalsLed(): HasMany
    {
        return $this->hasMany(Padal::class, 'user_id');
    }

    /**
     * USER beranggotakan PADAL_ANGGOTA (Teknisi)
     */
    public function padalTeams(): BelongsToMany
    {
        return $this->belongsToMany(Padal::class, 'padal_anggota', 'user_id', 'padal_id')
            ->withPivot('id', 'tanggal_bergabung')
            ->withTimestamps();
    }

    /**
     * Rating yang pernah diberikan oleh user ini
     */
    public function ratingsGiven(): HasMany
    {
        return $this->hasMany(TicketRating::class, 'user_id');
    }

    /**
     * Riwayat status yang dicatat oleh user ini
     */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(TicketStatusHistory::class, 'user_id');
    }

    // Accessors untuk atribut ERD
    public function getIdUserAttribute()
    {
        return $this->id;
    }

    public function getNamaAttribute()
    {
        return $this->name;
    }

    public function getNoTeleponAttribute()
    {
        return $this->phone_number;
    }
}
