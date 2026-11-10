<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerguruanTinggi extends Model
{
    use HasFactory;

    protected $table = 'perguruan_tinggis';

    protected $fillable = [
        'kode_pt',
        'nama_pt',
        'singkatan',
        'logo',
        'akreditasi',
        'sk_pendirian',
        'tanggal_sk',
        'nama_rektor',
        'nip_rektor',
        'email',
        'telepon',
        'website',
        'alamat',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_sk' => 'date',
        ];
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? asset('storage/' . $this->logo) : null;
    }
}
