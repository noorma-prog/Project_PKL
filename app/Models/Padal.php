<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Padal extends Model
{
    use HasFactory;

    protected $table = 'padals';

    protected $fillable = [
        'user_id',      // id_user (Ketua/Pemimpin Padal)
        'nama_padal',   // nama_padal
        'deskripsi',    // deskripsi
        'status',       // status ('aktif'/'nonaktif')
    ];

    /**
     * User yang memimpin unit Padal ini (Relasi ERD: USER memimpin PADAL)
     */
    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function user(): BelongsTo
    {
        return $this->leader();
    }

    /**
     * Anggota dalam unit Padal ini (Relasi ERD: PADAL memiliki PADAL_ANGGOTA)
     */
    public function anggotaPadal(): HasMany
    {
        return $this->hasMany(PadalAnggota::class, 'padal_id');
    }

    /**
     * Teknisi yang menjadi anggota unit Padal ini (Relasi ERD: USER beranggotakan PADAL_ANGGOTA)
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'padal_anggota', 'padal_id', 'user_id')
            ->withPivot('id', 'tanggal_bergabung')
            ->withTimestamps();
    }

    public function anggota(): BelongsToMany
    {
        return $this->members();
    }

    /**
     * Tiket yang ditangani oleh unit Padal ini (Relasi ERD: PADAL menangani TIKET)
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'padal_id');
    }

    /**
     * Rating dan ulasan yang didapatkan Padal
     */
    public function ratings(): HasMany
    {
        return $this->hasMany(TicketRating::class, 'padal_id');
    }

    public function getAvgRatingAttribute(): float
    {
        return round((float) $this->ratings()->avg('rating'), 1) ?: 0.0;
    }

    // Accessor atribut ERD
    public function getIdPadalAttribute()
    {
        return $this->id;
    }

    public function getIdUserAttribute()
    {
        return $this->user_id;
    }
}
