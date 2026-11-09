<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeederLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'feeder_logs';

    protected $fillable = [
        'endpoint_action',
        'status',
        'error_code',
        'error_desc',
        'request_data',
        'response_data',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }
}
