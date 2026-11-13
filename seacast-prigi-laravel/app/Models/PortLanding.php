<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PortLanding extends Model
{
    protected $table = 'port_landings';

    protected $guarded = [];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'cakalang_tons' => 'float',
        'madidihang_tons' => 'float',
        'tongkol_tons' => 'float',
        'total_pelagic_tons' => 'float',
        'trips_count' => 'integer',
        'cpue_proxy' => 'float',
        'fleet_lt_10gt' => 'integer',
        'fleet_10_20gt' => 'integer',
        'fleet_20_30gt' => 'integer',
        'fleet_gt_30gt' => 'integer',
        'total_fleet' => 'integer',
    ];

    public function scopeForPort($query, string $portCode)
    {
        return $query->where('port_code', $portCode);
    }

    public function scopeForYear($query, int $year)
    {
        return $query->where('year', $year);
    }
}

