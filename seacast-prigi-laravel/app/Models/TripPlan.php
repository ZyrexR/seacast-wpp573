<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripPlan extends Model
{
    protected $table = 'trip_plans';

    protected $guarded = [];

    protected $casts = [
        'departure_date' => 'date:Y-m-d',
        'target_lat' => 'float',
        'target_lon' => 'float',
        'distance_nm' => 'float',
        'bearing_degrees' => 'float',
        'base_fuel_liters' => 'float',
        'safety_reserve_liters' => 'float',
        'total_fuel_liters' => 'float',
        'total_fuel_cost_rp' => 'integer',
        'projected_catch_kg' => 'integer',
        'gross_revenue_rp' => 'integer',
        'net_profit_rp' => 'integer',
        'wave_height_m' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForPort($query, string $portCode)
    {
        return $query->where('port_code', $portCode);
    }
}

