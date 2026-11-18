<?php

namespace App\Services;

use App\Models\DailyPrediction;

class LatestPredictionDateService
{
    public function latest(): ?string
    {
        $date = DailyPrediction::query()->max('prediction_date');

        return $date === null ? null : (string) $date;
    }
}
