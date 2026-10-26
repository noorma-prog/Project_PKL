<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketRating extends Model
{
    use HasFactory;

    protected $table = 'ticket_ratings';

    protected $fillable = [
        'ticket_id',   // id_tiket
        'user_id',     // id_user (pelapor)
        'padal_id',    // id_padal (unit Padal yang dinilai)
        'rating',      // nilai_rating (1 - 5)
        'review',      // komentar
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function padal(): BelongsTo
    {
        return $this->belongsTo(Padal::class, 'padal_id');
    }

    // Accessors atribut ERD
    public function getIdRatingAttribute()
    {
        return $this->id;
    }

    public function getIdTiketAttribute()
    {
        return $this->ticket_id;
    }

    public function getNilaiRatingAttribute()
    {
        return $this->rating;
    }

    public function getKomentarAttribute()
    {
        return $this->review;
    }

    public function getTanggalRatingAttribute()
    {
        return $this->created_at;
    }
}
