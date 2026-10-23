<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * KATEGORI_GANGGUAN memiliki TIKET (Relasi ERD)
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'category_id');
    }

    // Accessors untuk atribut ERD
    public function getIdKategoriAttribute()
    {
        return $this->id;
    }

    public function getNamaKategoriAttribute()
    {
        return $this->name;
    }

    public function getDeskripsiAttribute()
    {
        return $this->description;
    }

    public function getStatusAttribute()
    {
        return $this->is_active ? 'aktif' : 'nonaktif';
    }
}
