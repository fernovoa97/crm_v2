<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Venta;
class Lead extends Model
{
    protected $fillable = [
        'ruc',
        'razon_social',
        'estado_sunat',
        'segmento',
        'giro_negocio',
        'departamento',
        'provincia',
        'distrito',
        'nombre_rl',
        'dni_rl',
        'correo_rl',
        'movistar',
        'entel',
        'claro',
        'bitel',
        'telf1',
        'telf2',
        'telf3',
        'telf4',
        'telf5',
        'comentario',
        'assigned_to',
        'tipificacion',
        'recall_at',
        'released_at',
    ];

    protected $casts = [
        'recall_at'   => 'datetime',
        'released_at' => 'datetime',
    ];

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function isBlacklisted(): bool
    {
        $telefonos = array_filter([
            $this->telf1, $this->telf2, $this->telf3,
            $this->telf4, $this->telf5,
        ]);

        return Blacklist::where('ruc', $this->ruc)
            ->orWhereIn('telefono', $telefonos)
            ->exists();
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }
}