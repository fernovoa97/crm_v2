<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();

            // Datos de la empresa
            $table->string('ruc', 11)->unique();
            $table->string('razon_social');
            $table->string('estado_sunat')->nullable();
            $table->enum('segmento', ['micro', 'pyme', 'nuevo', 'mayores'])->nullable();
            $table->string('giro_negocio')->nullable();
            $table->string('departamento')->nullable();
            $table->string('provincia')->nullable();
            $table->string('distrito')->nullable();

            // Datos del representante legal
            $table->string('nombre_rl')->nullable();
            $table->string('dni_rl', 15)->nullable();
            $table->string('correo_rl')->nullable();

            // Cantidad de líneas por operadora
            $table->integer('movistar')->default(0);
            $table->integer('entel')->default(0);
            $table->integer('claro')->default(0);
            $table->integer('bitel')->default(0);

            // Teléfonos de contacto
            $table->string('telf1')->nullable();
            $table->string('telf2')->nullable();
            $table->string('telf3')->nullable();
            $table->string('telf4')->nullable();
            $table->string('telf5')->nullable();

            // Extra
            $table->text('comentario')->nullable();

            // Asignación
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();

            // Tipificación
            $table->enum('tipificacion', [
                'pendiente',
                'numero_equivocado',
                'volver_llamar',
                'no_interesado',
                'no_califica',
                'lista_negra',
                'prospecto',
            ])->default('pendiente');

            $table->timestamp('recall_at')->nullable();
            $table->timestamp('released_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};