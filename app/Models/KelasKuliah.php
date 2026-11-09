<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class KelasKuliah extends Model
{
    use HasFactory;

    protected $table = 'kelas_kuliahs';

    protected $fillable = [
        'tahun_akademik_id',
        'mata_kuliah_id',
        'nama_kelas',
        'kapasitas',
        'id_feeder',
        'status_sync_feeder',
    ];

    public function tahunAkademik(): BelongsTo
    {
        return $this->belongsTo(TahunAkademik::class, 'tahun_akademik_id');
    }

    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MataKuliah::class, 'mata_kuliah_id');
    }

    public function kelasDosens(): HasMany
    {
        return $this->hasMany(KelasDosen::class, 'kelas_kuliah_id');
    }

    public function dosens(): BelongsToMany
    {
        return $this->belongsToMany(Dosen::class, 'kelas_dosens', 'kelas_kuliah_id', 'dosen_id')
                    ->withPivot('sks_substansi', 'rencana_pertemuan', 'realisasi_pertemuan', 'is_koordinator');
    }

    public function pesertaKelas(): HasMany
    {
        return $this->hasMany(PesertaKelas::class, 'kelas_kuliah_id');
    }

    public function mahasiswas(): BelongsToMany
    {
        return $this->belongsToMany(Mahasiswa::class, 'peserta_kelas', 'kelas_kuliah_id', 'mahasiswa_id')
                    ->withPivot('nilai_angka', 'nilai_huruf');
    }

    public function jadwals(): HasMany
    {
        return $this->hasMany(JadwalKuliah::class, 'kelas_kuliah_id');
    }

    public function pertemuans(): HasMany
    {
        return $this->hasMany(PertemuanKuliah::class, 'kelas_kuliah_id')->orderBy('pertemuan_ke');
    }

    public function pertemuanKuliahs(): HasMany
    {
        return $this->hasMany(PertemuanKuliah::class, 'kelas_kuliah_id')->orderBy('pertemuan_ke');
    }

    public function pengesahanBkds(): HasMany
    {
        return $this->hasMany(PengesahanBkd::class, 'kelas_kuliah_id');
    }

    public function pengesahanBkd()
    {
        return $this->hasOne(PengesahanBkd::class, 'kelas_kuliah_id')->latestOfMany();
    }

    // Helper: Hitung jumlah pertemuan yang telah selesai
    public function getJumlahPertemuanSelesaiAttribute(): int
    {
        return $this->pertemuans()->where('status', 'selesai')->count();
    }

    // Helper: Cek apakah memenuhi syarat BKD (16 pertemuan)
    public function getIsMemenuhiBkdAttribute(): bool
    {
        return $this->jumlah_pertemuan_selesai >= 16;
    }
}
