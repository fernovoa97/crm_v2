<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VentaDocumento extends Model
{
    protected $fillable = [
        'venta_id',
        'nombre_original',
        'path',
        'mime_type',
        'size',
        'subido_por',
    ];

    public function venta()     { return $this->belongsTo(Venta::class); }
    public function subidoPor() { return $this->belongsTo(User::class, 'subido_por'); }
}