<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KelasDosen extends Model
{
    use HasFactory;

    protected $table = 'kelas_dosens';

    protected $fillable = [
        'kelas_kuliah_id',
        'dosen_id',
        'sks_substansi',
        'rencana_pertemuan',
        'realisasi_pertemuan',
        'is_koordinator',
        'id_feeder',
    ];

    protected function casts(): array
    {
        return [
            'sks_substansi' => 'float',
            'rencana_pertemuan' => 'integer',
            'realisasi_pertemuan' => 'integer',
            'is_koordinator' => 'boolean',
        ];
    }

    public function kelasKuliah(): BelongsTo
    {
        return $this->belongsTo(KelasKuliah::class, 'kelas_kuliah_id');
    }

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'dosen_id');
    }
}
