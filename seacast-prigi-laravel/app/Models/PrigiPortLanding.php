<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Historical Empirical Fish Landings at PPN Prigi (132 Months: 2015 - 2025)
 */
class PrigiPortLanding extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'prigi_port_landings';
    protected $primaryKey = 'year_month';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $guarded = ['*'];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'cakalang_tons' => 'float',
        'madidihang_tons' => 'float',
        'tongkol_tons' => 'float',
        'total_pelagic_tons' => 'float',
        'trips_count' => 'integer',
        'cpue_proxy' => 'float',
        'mean_sst' => 'float',
        'mean_chla' => 'float',
    ];

    public function scopeForYear($query, int $year)
    {
        return $query->where('year', $year);
    }
}

