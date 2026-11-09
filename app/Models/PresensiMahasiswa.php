<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PresensiMahasiswa extends Model
{
    use HasFactory;

    protected $table = 'presensi_mahasiswas';

    protected $fillable = [
        'pertemuan_kuliah_id',
        'mahasiswa_id',
        'status',
        'metode_presensi',
        'waktu_presensi',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'waktu_presensi' => 'datetime',
        ];
    }

    public function pertemuan(): BelongsTo
    {
        return $this->belongsTo(PertemuanKuliah::class, 'pertemuan_kuliah_id');
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }
}
