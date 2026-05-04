<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VentaLinea extends Model
{
    protected $fillable = [
        'venta_id',
        'nro_portar',
        'plan',
        'operador_cedente',
        'operador_cedente_otro',
        'equipo_sim',
        'descuento',
        'nro_wf',
        'large_asociada',
    ];

    public function venta() { return $this->belongsTo(Venta::class); }
}