<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Prigi Fishing Fleet Registered Profile by GT Class
 */
class PrigiFleetByGt extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'prigi_fleet_by_gt';
    protected $primaryKey = 'gt_class';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $guarded = ['*'];

    protected $casts = [
        'year' => 'integer',
        'vessel_count' => 'integer',
        'pct_fleet' => 'float',
        'max_range_nm' => 'float',
        'recommended_dom_zones' => 'array',
    ];
}

