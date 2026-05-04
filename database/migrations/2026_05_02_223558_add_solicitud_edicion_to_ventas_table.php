<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->boolean('solicitud_edicion')->default(false)->after('estado');
            $table->text('solicitud_edicion_motivo')->nullable()->after('solicitud_edicion');
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn(['solicitud_edicion', 'solicitud_edicion_motivo']);
        });
    }
};