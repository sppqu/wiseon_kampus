<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PesertaKelas extends Model
{
    use HasFactory;

    protected $table = 'peserta_kelas';

    protected $fillable = [
        'kelas_kuliah_id',
        'mahasiswa_id',
        'nilai_angka',
        'nilai_huruf',
        'id_feeder',
    ];

    public function kelasKuliah(): BelongsTo
    {
        return $this->belongsTo(KelasKuliah::class, 'kelas_kuliah_id');
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }
}
