<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ruangan extends Model
{
    use HasFactory;

    protected $table = 'ruangans';

    protected $fillable = [
        'kode_ruangan',
        'nama_ruangan',
        'gedung',
        'lantai',
        'kapasitas',
        'latitude',
        'longitude',
        'radius_meter',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'radius_meter' => 'integer',
        ];
    }

    public function jadwals(): HasMany
    {
        return $this->hasMany(JadwalKuliah::class, 'ruangan_id');
    }

    public function pertemuans(): HasMany
    {
        return $this->hasMany(PertemuanKuliah::class, 'ruangan_id');
    }
}
