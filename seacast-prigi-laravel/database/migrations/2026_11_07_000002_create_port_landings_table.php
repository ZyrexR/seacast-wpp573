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
        if (!Schema::hasTable('port_landings')) {
            Schema::create('port_landings', function (Blueprint $table) {
                $table->id();
                $table->string('port_code', 32)->index();
                $table->string('port_name', 100);
                $table->string('province', 100)->default('Jawa Timur');
                $table->string('year_month', 7)->index();
                $table->unsignedSmallInteger('year')->index();
                $table->unsignedTinyInteger('month')->index();
                $table->decimal('cakalang_tons', 10, 2)->default(0);
                $table->decimal('madidihang_tons', 10, 2)->default(0);
                $table->decimal('tongkol_tons', 10, 2)->default(0);
                $table->decimal('total_pelagic_tons', 10, 2)->default(0);
                $table->unsignedInteger('trips_count')->default(0);
                $table->decimal('cpue_proxy', 10, 2)->default(0);
                $table->unsignedInteger('fleet_lt_10gt')->default(0);
                $table->unsignedInteger('fleet_10_20gt')->default(0);
                $table->unsignedInteger('fleet_20_30gt')->default(0);
                $table->unsignedInteger('fleet_gt_30gt')->default(0);
                $table->unsignedInteger('total_fleet')->default(0);
                $table->timestamps();

                $table->unique(['port_code', 'year_month']);
            });
        }

        try {
            if (config('database.default') !== 'pgsql' && !Schema::connection('pgsql')->hasTable('port_landings')) {
                Schema::connection('pgsql')->create('port_landings', function (Blueprint $table) {
                    $table->id();
                    $table->string('port_code', 32)->index();
                    $table->string('port_name', 100);
                    $table->string('province', 100)->default('Jawa Timur');
                    $table->string('year_month', 7)->index();
                    $table->unsignedSmallInteger('year')->index();
                    $table->unsignedTinyInteger('month')->index();
                    $table->decimal('cakalang_tons', 10, 2)->default(0);
                    $table->decimal('madidihang_tons', 10, 2)->default(0);
                    $table->decimal('tongkol_tons', 10, 2)->default(0);
                    $table->decimal('total_pelagic_tons', 10, 2)->default(0);
                    $table->unsignedInteger('trips_count')->default(0);
                    $table->decimal('cpue_proxy', 10, 2)->default(0);
                    $table->unsignedInteger('fleet_lt_10gt')->default(0);
                    $table->unsignedInteger('fleet_10_20gt')->default(0);
                    $table->unsignedInteger('fleet_20_30gt')->default(0);
                    $table->unsignedInteger('fleet_gt_30gt')->default(0);
                    $table->unsignedInteger('total_fleet')->default(0);
                    $table->timestamps();

                    $table->unique(['port_code', 'year_month']);
                });
            }
        } catch (\Throwable $e) {
            // Log/ignore if pgsql not reachable
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('port_landings');
        try {
            Schema::connection('pgsql')->dropIfExists('port_landings');
        } catch (\Throwable $e) {
        }
    }
};

