<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('venta_lineas', function (Blueprint $table) {
            $table->string('modelo_equipo')->nullable()->after('equipo_sim');
        });

        // MySQL no permite modificar un enum con Schema::table() directamente
        // sin doctrine/dbal, así que lo hacemos con SQL nativo.
        DB::statement("ALTER TABLE venta_lineas MODIFY equipo_sim ENUM('sim_card', 'sim_card_equipo') NOT NULL DEFAULT 'sim_card'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE venta_lineas MODIFY equipo_sim ENUM('sim_card', 'equipo', 'sim_card_equipo') NOT NULL DEFAULT 'sim_card'");

        Schema::table('venta_lineas', function (Blueprint $table) {
            $table->dropColumn('modelo_equipo');
        });
    }
};
