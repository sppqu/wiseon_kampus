<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PresensiHarianDosen extends Model
{
    use HasFactory;

    protected $table = 'presensi_harian_dosens';

    protected $fillable = [
        'dosen_id',
        'tanggal',
        'jam_masuk',
        'jam_pulang',
        'latitude_masuk',
        'longitude_masuk',
        'jarak_masuk_meter',
        'latitude_pulang',
        'longitude_pulang',
        'jarak_pulang_meter',
        'foto_masuk',
        'foto_pulang',
        'status',
        'validasi_lokasi',
        'keterangan',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'dosen_id');
    }

    // Helper: Hitung Durasi Kerja (Jam & Menit)
    public function getDurasiKerjaAttribute(): ?string
    {
        if (!$this->jam_masuk || !$this->jam_pulang) {
            return null;
        }

        $masuk = \Carbon\Carbon::parse($this->jam_masuk);
        $pulang = \Carbon\Carbon::parse($this->jam_pulang);

        $totalMenit = $masuk->diffInMinutes($pulang);
        $jam = floor($totalMenit / 60);
        $menit = $totalMenit % 60;

        return "{$jam} Jam {$menit} Menit";
    }
}
