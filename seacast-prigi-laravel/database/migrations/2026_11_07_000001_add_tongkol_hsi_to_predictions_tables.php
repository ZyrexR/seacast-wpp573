<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::connection('pgsql')->hasTable('daily_predictions')) {
            Schema::connection('pgsql')->table('daily_predictions', function (Blueprint $table) {
                if (!Schema::connection('pgsql')->hasColumn('daily_predictions', 'hsi_tongkol')) {
                    $table->float('hsi_tongkol')->nullable();
                }
            });
        }

        if (Schema::connection('pgsql')->hasTable('grid_monthly_predictions')) {
            Schema::connection('pgsql')->table('grid_monthly_predictions', function (Blueprint $table) {
                if (!Schema::connection('pgsql')->hasColumn('grid_monthly_predictions', 'hsi_tongkol')) {
                    $table->float('hsi_tongkol')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::connection('pgsql')->hasTable('daily_predictions')) {
            Schema::connection('pgsql')->table('daily_predictions', function (Blueprint $table) {
                if (Schema::connection('pgsql')->hasColumn('daily_predictions', 'hsi_tongkol')) {
                    $table->dropColumn('hsi_tongkol');
                }
            });
        }

        if (Schema::connection('pgsql')->hasTable('grid_monthly_predictions')) {
            Schema::connection('pgsql')->table('grid_monthly_predictions', function (Blueprint $table) {
                if (Schema::connection('pgsql')->hasColumn('grid_monthly_predictions', 'hsi_tongkol')) {
                    $table->dropColumn('hsi_tongkol');
                }
            });
        }
    }
};

