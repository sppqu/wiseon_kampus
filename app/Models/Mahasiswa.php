<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Mahasiswa extends Model
{
    use HasFactory;

    protected $table = 'mahasiswas';

    protected $fillable = [
        'nim',
        'nama_lengkap',
        'prodi_id',
        'angkatan',
        'jenis_kelamin',
        'email',
        'no_hp',
        'status',
        'id_feeder',
    ];

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class, 'prodi_id');
    }

    public function pesertaKelas(): HasMany
    {
        return $this->hasMany(PesertaKelas::class, 'mahasiswa_id');
    }

    public function kelasKuliahs(): BelongsToMany
    {
        return $this->belongsToMany(KelasKuliah::class, 'peserta_kelas', 'mahasiswa_id', 'kelas_kuliah_id');
    }

    public function presensiMahasiswas(): HasMany
    {
        return $this->hasMany(PresensiMahasiswa::class, 'mahasiswa_id');
    }
}
