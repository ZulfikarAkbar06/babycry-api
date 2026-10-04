<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CryLog extends Model
{
    /** Jenis tangisan yang diizinkan (hasil klasifikasi dari perangkat IoT). */
    public const CRY_TYPES = [
        'hungry',      // lapar
        'tired',       // mengantuk / lelah
        'discomfort',  // tidak nyaman (popok basah, panas, dll)
        'pain',        // sakit / kolik
        'burping',     // perlu sendawa
        'lonely',      // ingin digendong
        'unknown',
    ];

    protected $fillable = [
        'device_id',
        'is_crying',
        'cry_type',
        'confidence',
        'sound_level',
        'temperature',
        'humidity',
        'duration_seconds',
        'notes',
        'recorded_at',
    ];

    protected $casts = [
        'is_crying'        => 'boolean',
        'confidence'       => 'float',
        'sound_level'      => 'float',
        'temperature'      => 'float',
        'humidity'         => 'float',
        'duration_seconds' => 'integer',
        'recorded_at'      => 'datetime',
    ];
}
