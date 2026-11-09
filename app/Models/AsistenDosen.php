<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsistenDosen extends Model
{
    use HasFactory;

    protected $table = 'asisten_dosens';

    protected $fillable = [
        'nim',
        'nama',
        'email',
        'no_hp',
        'mata_kuliah_id',
        'dosen_id',
        'kelas_kuliah_id',
        'nama_kelas',
        'status',
    ];

    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MataKuliah::class, 'mata_kuliah_id');
    }

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'dosen_id');
    }

    public function kelasKuliah(): BelongsTo
    {
        return $this->belongsTo(KelasKuliah::class, 'kelas_kuliah_id');
    }
}
