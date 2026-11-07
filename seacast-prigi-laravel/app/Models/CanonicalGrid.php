<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

/**
 * Canonical Grid Cell Model (WPPNRI 573 - 8,129 cells)
 * Native PostGIS Integration with EPSG:4326
 */
class CanonicalGrid extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'canonical_grids';
    protected $primaryKey = 'cell_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    // Strict zero-mutation policy for scientific data
    protected $guarded = ['*'];

    protected $casts = [
        'center_lon' => 'float',
        'center_lat' => 'float',
        'depth_m' => 'float',
        'dist_port_km' => 'float',
        'dist_port_nm' => 'float',
        'is_shelf_break' => 'boolean',
        'is_prigi_inshore' => 'boolean',
        'is_prigi_offshore' => 'boolean',
    ];

    public function dailyPredictions(): HasMany
    {
        return $this->hasMany(DailyPrediction::class, 'cell_id', 'cell_id');
    }

    public function latestPrediction(): HasOne
    {
        return $this->hasOne(DailyPrediction::class, 'cell_id', 'cell_id')
            ->latest('prediction_date');
    }

    public function monthlyPredictions(): HasMany
    {
        return $this->hasMany(GridMonthlyPrediction::class, 'cell_id', 'cell_id');
    }

    /**
     * Scope query with spatial bounding box filter using native PostGIS GiST index
     */
    public function scopeInBoundingBox($query, float $minLon, float $minLat, float $maxLon, float $maxLat)
    {
        return $query->whereRaw(
            "geom && ST_MakeEnvelope(?, ?, ?, ?, 4326)",
            [$minLon, $minLat, $maxLon, $maxLat]
        );
    }

    /**
     * Scope query with distance from PPN Prigi Homeport (NM)
     */
    public function scopeWithinRadiusNm($query, float $maxNm)
    {
        return $query->where('dist_port_nm', '<=', $maxNm);
    }

    /**
     * Select geometry as GeoJSON string via ST_AsGeoJSON
     */
    public function scopeWithGeoJson($query)
    {
        return $query->select('*', DB::raw('ST_AsGeoJSON(geom) as geojson_geom'));
    }
}

