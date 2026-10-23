<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketStatusHistory extends Model
{
    use HasFactory;

    protected $table = 'ticket_status_histories';

    protected $fillable = [
        'ticket_id',     // id_tiket
        'user_id',       // id_user
        'status_from',   // status_sebelumnya
        'status_to',     // status_baru
        'notes',         // keterangan
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Accessors atribut ERD
    public function getIdRiwayatAttribute()
    {
        return $this->id;
    }

    public function getIdTiketAttribute()
    {
        return $this->ticket_id;
    }

    public function getIdUserAttribute()
    {
        return $this->user_id;
    }

    public function getStatusSebelumnyaAttribute()
    {
        return $this->status_from;
    }

    public function getStatusBaruAttribute()
    {
        return $this->status_to;
    }

    public function getKeteranganAttribute()
    {
        return $this->notes;
    }

    public function getTanggalPerubahanAttribute()
    {
        return $this->created_at;
    }
}
