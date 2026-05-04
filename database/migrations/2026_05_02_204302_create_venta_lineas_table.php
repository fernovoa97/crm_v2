<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venta_lineas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas')->onDelete('cascade');

            $table->string('nro_portar')->nullable(); // solo porta
            $table->enum('plan', [
                'max_negocios_29.90',
                'max_negocios_39.90',
                'max_negocios_49.90',
                'max_ilimitado_55.90',
                'max_ilimitado_69.90',
                'max_ilimitado_79.90',
                'max_ilimitado_95.90',
                'max_ilimitado_109.90',
                'max_ilimitado_125.00',
                'max_ilimitado_159.90',
                'max_ilimitado_189.90',
                'max_ilimitado_289.90',
            ]);
            $table->enum('operador_cedente', ['entel', 'movistar', 'bitel', 'otros'])->nullable();
            $table->string('operador_cedente_otro')->nullable();
            $table->enum('equipo_sim', ['sim_card', 'equipo', 'sim_card_equipo'])->default('sim_card');
            $table->enum('descuento', ['no_aplica', '50%', 'bajo_plantilla'])->default('no_aplica');
            $table->string('nro_wf', 6)->nullable(); // solo si descuento = bajo_plantilla
            $table->string('large_asociada')->nullable(); // solo si tipo_venta = alta

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venta_lineas');
    }
};