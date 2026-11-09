<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Dosen extends Model
{
    use HasFactory;

    protected $table = 'dosens';

    protected $fillable = [
        'user_id',
        'nidn',
        'nip',
        'nama_lengkap',
        'gelar_depan',
        'gelar_belakang',
        'prodi_id',
        'no_hp',
        'email',
        'jenis_kelamin',
        'is_active',
        'id_feeder',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class, 'prodi_id');
    }

    public function kelasDosens(): HasMany
    {
        return $this->hasMany(KelasDosen::class, 'dosen_id');
    }

    public function presensiHarians(): HasMany
    {
        return $this->hasMany(PresensiHarianDosen::class, 'dosen_id');
    }

    public function jadwalKerja(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(JadwalKerjaDosen::class, 'dosen_id');
    }

    public function kelasKuliahs(): BelongsToMany
    {
        return $this->belongsToMany(KelasKuliah::class, 'kelas_dosens', 'dosen_id', 'kelas_kuliah_id')
                    ->withPivot('sks_substansi', 'rencana_pertemuan', 'realisasi_pertemuan', 'is_koordinator');
    }

    public function jadwals(): HasMany
    {
        return $this->hasMany(JadwalKuliah::class, 'dosen_id');
    }

    public function pertemuans(): HasMany
    {
        return $this->hasMany(PertemuanKuliah::class, 'dosen_id');
    }

    public function getNamaGelarAttribute(): string
    {
        $depan = $this->gelar_depan ? trim($this->gelar_depan) . ' ' : '';
        $belakang = $this->gelar_belakang ? ', ' . trim($this->gelar_belakang) : '';
        return $depan . $this->nama_lengkap . $belakang;
    }
}
