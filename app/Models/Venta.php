<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Venta extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'solicitud_edicion',
'solicitud_edicion_motivo',
        'lead_id', 'asesor_id', 'mesa_control_id',
        'tipo', 'tipo_ingreso', 'estado', 'motivo_rechazo', 'estado_contrato',

        // Datos comunes
        'ruc', 'razon_social', 'nombre_representante',
        'tipo_documento', 'nro_documento',
        'telefono_representante', 'correo', 'nro_sec',

        // Fija
        'tipo_venta_fija', 'coordenadas_cobertura', 'plano_cobertura',
        'direccion_instalacion', 'referencia_direccion_instalacion',
        'direccion_facturacion_fija', 'telefono_sot', 'tecnologia',
        'campana_fija', 'tipo_producto_fija',
        'plan_telefonia', 'plan_cable_standar', 'plan_cable_superior',
        'plan_internet_200', 'plan_internet_400', 'plan_internet_1500',
        'cantidad_decos', 'cantidad_repetidores',
        'precio_servicio', 'bono_fija', 'descuento_fija',
        'full_claro', 'nro_movil_fullclaro',
        'fecha_programacion', 'fecha_instalacion',
        'nro_sot_fija', 'proyecto_fija', 'pedido_fija',

        // Móvil
        'tipo_venta_movil', 'tipo_entrega',
        'coordenadas_geodir', 'plano_geodir',
        'direccion_entrega', 'referencias_entrega',
        'direccion_facturacion_movil',
        'telefono_biometria', 'telefono_referencia_movil',
        'campana_movil', 'fecha_despacho', 'rango_horario',
        'descuento_movil', 'nro_wf',
        'fecha_activacion', 'descuentos_mesa_movil', 'pedido_movil',
    ];

    protected $casts = [
        'fecha_programacion'  => 'datetime',
        'fecha_instalacion'   => 'date',
        'fecha_despacho'      => 'date',
        'fecha_activacion'    => 'date',
        'precio_servicio'     => 'decimal:2',
        'plan_telefonia'      => 'boolean',
        'plan_cable_standar'  => 'boolean',
        'plan_cable_superior' => 'boolean',
        'plan_internet_200'   => 'boolean',
        'plan_internet_400'   => 'boolean',
        'plan_internet_1500'  => 'boolean',
    ];

    public function lead()       { return $this->belongsTo(Lead::class); }
    public function asesor()     { return $this->belongsTo(User::class, 'asesor_id'); }
    public function mesaControl(){ return $this->belongsTo(User::class, 'mesa_control_id'); }
    public function lineas()     { return $this->hasMany(VentaLinea::class); }
    public function documentos() { return $this->hasMany(VentaDocumento::class); }
}