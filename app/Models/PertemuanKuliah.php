<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PertemuanKuliah extends Model
{
    use HasFactory;

    protected $table = 'pertemuan_kuliahs';

    protected $fillable = [
        'jadwal_kuliah_id',
        'kelas_kuliah_id',
        'dosen_id',
        'ruangan_id',
        'pertemuan_ke',
        'jenis_pertemuan',
        'tanggal',
        'jam_mulai_jadwal',
        'jam_selesai_jadwal',
        'jam_mulai_aktual',
        'jam_selesai_aktual',
        'rencana_materi',
        'realisasi_materi',
        'cpmk',
        'metode',
        'latitude',
        'longitude',
        'jarak_meter',
        'validasi_lokasi',
        'foto_bukti',
        'qr_token',
        'status',
        'approval_kaprodi',
        'catatan_kaprodi',
        'id_feeder_aktivitas',
        'status_sync_feeder',
        'pesan_sync_feeder',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'latitude' => 'float',
            'longitude' => 'float',
            'jarak_meter' => 'integer',
        ];
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(JadwalKuliah::class, 'jadwal_kuliah_id');
    }

    public function kelasKuliah(): BelongsTo
    {
        return $this->belongsTo(KelasKuliah::class, 'kelas_kuliah_id');
    }

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'dosen_id');
    }

    public function ruangan(): BelongsTo
    {
        return $this->belongsTo(Ruangan::class, 'ruangan_id');
    }

    public function presensiMahasiswas(): HasMany
    {
        return $this->hasMany(PresensiMahasiswa::class, 'pertemuan_kuliah_id');
    }

    // Helper: Hitung persentase kehadiran mahasiswa pada pertemuan ini
    public function getPersenHadirMahasiswaAttribute(): float
    {
        $totalPeserta = $this->presensiMahasiswas()->count();
        if ($totalPeserta === 0) {
            return 0.0;
        }

        $hadir = $this->presensiMahasiswas()->where('status', 'hadir')->count();
        return round(($hadir / $totalPeserta) * 100, 1);
    }
}
