<?php

namespace App\Exports;

use App\Models\Lead;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class WrongNumberExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Lead::with('assignedTo')
                   ->where('tipificacion', 'numero_equivocado')
                   ->get();
    }

    public function headings(): array
    {
        return [
            'ruc', 'razon_social', 'estado_sunat', 'segmento', 'giro_negocio',
            'dpto', 'prov', 'dist', 'nombre_rl', 'dni_rl', 'correo_rl',
            'movistar', 'entel', 'claro', 'bitel',
            'telf1', 'telf2', 'telf3', 'telf4', 'telf5',
            'comentario', 'asignar_a',
        ];
    }

    public function map($lead): array
    {
        return [
            $lead->ruc,
            $lead->razon_social,
            $lead->estado_sunat,
            $lead->segmento,
            $lead->giro_negocio,
            $lead->departamento,
            $lead->provincia,
            $lead->distrito,
            $lead->nombre_rl,
            $lead->dni_rl,
            $lead->correo_rl,
            $lead->movistar,
            $lead->entel,
            $lead->claro,
            $lead->bitel,
            $lead->telf1,
            $lead->telf2,
            $lead->telf3,
            $lead->telf4,
            $lead->telf5,
            $lead->comentario,
            $lead->assignedTo?->email ?? '', // ← correo del asesor prellenado
        ];
    }
}