<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TahunAkademik extends Model
{
    use HasFactory;

    protected $table = 'tahun_akademiks';

    protected $fillable = [
        'kode',
        'nama',
        'semester',
        'tanggal_mulai',
        'tanggal_selesai',
        'is_active',
        'id_feeder',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function kelasKuliahs(): HasMany
    {
        return $this->hasMany(KelasKuliah::class, 'tahun_akademik_id');
    }

    public function getNamaSemesterAttribute(): string
    {
        return $this->nama ?: ($this->kode . ' ' . ucfirst($this->semester));
    }
}
