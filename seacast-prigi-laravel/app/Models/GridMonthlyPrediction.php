<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Monthly Spatio-Temporal Prediction Archive (Year 2025 - 97,548 rows)
 */
class GridMonthlyPrediction extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'grid_monthly_predictions';
    protected $primaryKey = 'cell_id'; // Composite PK (year_month, cell_id)
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $guarded = ['*'];

    protected $casts = [
        'hsi_cakalang' => 'float',
        'hsi_madidihang' => 'float',
        'hsi_albakor' => 'float',
        'hsi_tongkol' => 'float',
        'is_hotspot' => 'boolean',
        'sst_c' => 'float',
        'chla_mg_m3' => 'float',
    ];

    public function grid(): BelongsTo
    {
        return $this->belongsTo(CanonicalGrid::class, 'cell_id', 'cell_id');
    }

    public function scopeForYearMonth($query, string $yearMonth)
    {
        return $query->where('year_month', $yearMonth);
    }
}

