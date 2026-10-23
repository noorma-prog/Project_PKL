<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PadalAnggota extends Model
{
    use HasFactory;

    protected $table = 'padal_anggota';

    protected $fillable = [
        'padal_id',           // id_padal
        'user_id',            // id_user (teknisi)
        'tanggal_bergabung',  // tanggal_bergabung
    ];

    protected function casts(): array
    {
        return [
            'tanggal_bergabung' => 'datetime',
        ];
    }

    public function padal(): BelongsTo
    {
        return $this->belongsTo(Padal::class, 'padal_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Accessor atribut ERD
    public function getIdPadalAnggotaAttribute()
    {
        return $this->id;
    }

    public function getIdPadalAttribute()
    {
        return $this->padal_id;
    }

    public function getIdUserAttribute()
    {
        return $this->user_id;
    }
}
