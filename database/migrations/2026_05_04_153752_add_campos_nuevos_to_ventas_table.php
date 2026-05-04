<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            // C1: Recojo en CAC
            $table->unsignedBigInteger('cac_id')->nullable()->after('tipo_entrega');
            $table->foreign('cac_id')->references('id')->on('cacs')->nullOnDelete();

            // B5: Porta fija
            $table->string('operador_cedente_fija')->nullable()->after('tipo_venta_fija');
            $table->string('telefono_fijo_migrar')->nullable()->after('operador_cedente_fija');

            // B6: Comentario de despacho
            $table->string('comentario_despacho')->nullable()->after('rango_horario');
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropForeign(['cac_id']);
            $table->dropColumn(['cac_id', 'operador_cedente_fija', 'telefono_fijo_migrar', 'comentario_despacho']);
        });
    }
};