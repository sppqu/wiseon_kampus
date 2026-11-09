<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JadwalKerjaDosen extends Model
{
    use HasFactory;

    protected $table = 'jadwal_kerja_dosens';

    protected $fillable = [
        'dosen_id',
        'jam_masuk',
        'jam_pulang',
        'toleransi_menit',
        'hari_kerja',
        'radius_meter',
        'is_custom',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'hari_kerja' => 'array',
            'is_custom' => 'boolean',
            'toleransi_menit' => 'integer',
            'radius_meter' => 'integer',
        ];
    }

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'dosen_id');
    }

    // Helper: Cek apakah hari tertentu adalah hari kerja dosen ini
    public function isHariKerja(string $namaHari): bool
    {
        if (empty($this->hari_kerja)) {
            // Default Senin - Jumat
            return in_array($namaHari, ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat']);
        }

        return in_array($namaHari, $this->hari_kerja);
    }
}
