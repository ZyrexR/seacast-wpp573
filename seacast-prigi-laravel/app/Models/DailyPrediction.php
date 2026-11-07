<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Daily Oceanographic and Species Habitat Suitability Index (HSI) Predictions
 */
class DailyPrediction extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'daily_predictions';
    protected $primaryKey = 'cell_id'; // Composite PK (prediction_date, cell_id)
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $guarded = ['*'];

    protected $casts = [
        'prediction_date' => 'date:Y-m-d',
        'hsi_cakalang' => 'float',
        'hsi_madidihang' => 'float',
        'hsi_albakor' => 'float',
        'is_hotspot' => 'boolean',
        'sst_c' => 'float',
        'chla_mg_m3' => 'float',
        'wave_height_m' => 'float',
        'grad_sst' => 'float',
        'is_bycatch_risk' => 'boolean',
    ];

    public function grid(): BelongsTo
    {
        return $this->belongsTo(CanonicalGrid::class, 'cell_id', 'cell_id');
    }

    public function scopeForDate($query, string $date)
    {
        return $query->where('prediction_date', $date);
    }

    public function scopeHotspots($query)
    {
        return $query->where('is_hotspot', true);
    }

    public function scopeByDomZone($query, string $zone)
    {
        return $query->where('dom_zone', $zone);
    }
}

