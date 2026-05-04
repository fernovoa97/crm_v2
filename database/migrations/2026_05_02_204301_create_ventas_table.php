<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
            $table->foreignId('asesor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('mesa_control_id')->nullable()->constrained('users')->onDelete('set null');

            // Tipo y estado
            $table->enum('tipo', ['movil', 'fija']);
            $table->enum('tipo_ingreso', ['pdv', 'centralizado', 'almacen_propio']);
            $table->enum('estado', ['borrador', 'enviada', 'en_proceso', 'completada', 'rechazada'])->default('borrador');
            $table->text('motivo_rechazo')->nullable();

            // Estado de contrato — lo llena mesa de control
            $table->enum('estado_contrato', [
                'pendiente_loteo',
                'pendiente_sigex',
                'conforme',
                'no_conforme',
            ])->nullable();

            // ── DATOS COMUNES (autorrelleno del lead) ──
            $table->string('ruc', 20);
            $table->string('razon_social');
            $table->string('nombre_representante');
            $table->enum('tipo_documento', ['dni', 'ce']);
            $table->string('nro_documento', 20);
            $table->string('telefono_representante');
            $table->string('correo');
            $table->string('nro_sec')->nullable();

            // ── CAMPOS FIJA ──
            $table->enum('tipo_venta_fija', ['alta', 'porta'])->nullable();
            $table->string('coordenadas_cobertura')->nullable();
            $table->string('plano_cobertura')->nullable();
            $table->string('direccion_instalacion')->nullable();
            $table->string('referencia_direccion_instalacion')->nullable();
            $table->string('direccion_facturacion_fija')->nullable();
            $table->string('telefono_sot')->nullable();
            $table->enum('tecnologia', ['hfc', 'ftth'])->nullable();
            $table->enum('campana_fija', [
                'regular',
                '1_sol',
                'empresas_medio',
                'empresas_basico',
                'empresas_grande',
                'relampago',
            ])->nullable();
            $table->enum('tipo_producto_fija', ['1play', '2play', '3play'])->nullable();

            // Planes fija (cada servicio como boolean)
            $table->boolean('plan_telefonia')->default(false);
            $table->boolean('plan_cable_standar')->default(false);
            $table->boolean('plan_cable_superior')->default(false);
            $table->boolean('plan_internet_200')->default(false);
            $table->boolean('plan_internet_400')->default(false);
            $table->boolean('plan_internet_1500')->default(false);

            // Adicionales fija
            $table->unsignedTinyInteger('cantidad_decos')->default(0);
            $table->unsignedTinyInteger('cantidad_repetidores')->default(0);

            $table->decimal('precio_servicio', 8, 2)->nullable();
            $table->string('bono_fija')->nullable();
            $table->string('descuento_fija')->nullable();
            $table->enum('full_claro', ['aplica', 'no_aplica'])->nullable();
            $table->string('nro_movil_fullclaro')->nullable();

            // Mesa de control — fija
            $table->dateTime('fecha_programacion')->nullable();
            $table->date('fecha_instalacion')->nullable();
            $table->string('nro_sot_fija')->nullable();
            $table->string('proyecto_fija')->nullable();
            $table->string('pedido_fija')->nullable();

            // ── CAMPOS MÓVIL ──
            $table->enum('tipo_venta_movil', ['alta', 'porta', 'renovacion'])->nullable();
            $table->enum('tipo_entrega', ['delivery', 'recojo_cac'])->nullable();
            $table->string('coordenadas_geodir')->nullable();
            $table->string('plano_geodir')->nullable();
            $table->string('direccion_entrega')->nullable();
            $table->string('referencias_entrega')->nullable();
            $table->string('direccion_facturacion_movil')->nullable();
            $table->string('telefono_biometria')->nullable();
            $table->string('telefono_referencia_movil')->nullable();
            $table->enum('campana_movil', ['claro_negocios', 'claro_emprendedor'])->nullable();
            $table->date('fecha_despacho')->nullable();
            $table->enum('rango_horario', ['sla_3h', 'am1', 'am2', 'pm1', 'pm2'])->nullable();
            $table->enum('descuento_movil', ['no_aplica', '50%', 'bajo_plantilla'])->nullable();
            $table->string('nro_wf', 6)->nullable();

            // Mesa de control — móvil
            $table->date('fecha_activacion')->nullable();
            $table->string('descuentos_mesa_movil')->nullable();
            $table->string('pedido_movil')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};