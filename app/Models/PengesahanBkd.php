<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengesahanBkd extends Model
{
    use HasFactory;

    protected $table = 'pengesahan_bkds';

    protected $fillable = [
        'kelas_kuliah_id',
        'dosen_id',
        'tahun_akademik_id',
        'total_pertemuan',
        'persen_kehadiran',
        'status_kelayakan_bkd',
        'status_pengesahan',
        'disahkan_oleh',
        'nidn_pengesah',
        'tanggal_pengesahan',
        'qr_verifikasi',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'total_pertemuan' => 'integer',
            'persen_kehadiran' => 'float',
            'tanggal_pengesahan' => 'date',
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

    public function tahunAkademik(): BelongsTo
    {
        return $this->belongsTo(TahunAkademik::class, 'tahun_akademik_id');
    }
}
