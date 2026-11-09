<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeederConfig extends Model
{
    use HasFactory;

    protected $table = 'feeder_configs';

    protected $fillable = [
        'url',
        'username',
        'password',
        'token',
        'token_expired_at',
        'kode_pt',
        'nama_pt',
        'mode',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'token_expired_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }
}
