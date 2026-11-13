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
        if (!Schema::hasTable('trip_plans')) {
            Schema::create('trip_plans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('reference_no', 32)->unique();
                $table->string('port_code', 32)->index();
                $table->string('port_name', 100);
                $table->string('vessel_name', 100);
                $table->string('skipper_name', 100);
                $table->string('gt_class', 32);
                $table->string('gear_code', 32);
                $table->string('gear_name', 100);
                $table->date('departure_date')->index();
                $table->string('target_commodity', 64);
                $table->decimal('target_lat', 10, 6);
                $table->decimal('target_lon', 10, 6);
                $table->string('target_dms', 100);
                $table->decimal('distance_nm', 8, 2);
                $table->decimal('bearing_degrees', 6, 1);
                $table->string('compass_direction', 8);
                $table->decimal('base_fuel_liters', 8, 2);
                $table->decimal('safety_reserve_liters', 8, 2);
                $table->decimal('total_fuel_liters', 8, 2);
                $table->bigInteger('total_fuel_cost_rp');
                $table->unsignedInteger('projected_catch_kg');
                $table->bigInteger('gross_revenue_rp');
                $table->bigInteger('net_profit_rp');
                $table->decimal('wave_height_m', 4, 1);
                $table->string('wave_status', 32);
                $table->string('permen_kp_status', 64);
                $table->string('moon_phase_name', 100);
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_plans');
    }
};

