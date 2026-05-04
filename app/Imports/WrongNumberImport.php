<?php

namespace App\Imports;

use App\Models\Lead;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;

class WrongNumberImport implements ToModel, WithHeadingRow, SkipsOnError
{
    use SkipsErrors;

    public int $updated = 0;
    public int $skipped = 0;

    public function model(array $row): ?Lead
    {
        $ruc = trim($row['ruc'] ?? '');
        if (!$ruc) { $this->skipped++; return null; }

        // Solo procesar leads que estén en numero_equivocado
        $lead = Lead::where('ruc', $ruc)
                    ->where('tipificacion', 'numero_equivocado')
                    ->first();

        if (!$lead) { $this->skipped++; return null; }

        // Resolver asignación
        $assignedTo = $lead->assigned_to; // mantener el actual si no viene
        $asignarA   = trim($row['asignar_a'] ?? '');
        if ($asignarA) {
            $user = User::where('email', $asignarA)->first();
            if ($user) $assignedTo = $user->id;
        }

        // Actualizar teléfonos y reasignar, volver a pendiente
        $lead->update([
            'telf1'        => trim($row['telf1'] ?? ''),
            'telf2'        => trim($row['telf2'] ?? ''),
            'telf3'        => trim($row['telf3'] ?? ''),
            'telf4'        => trim($row['telf4'] ?? ''),
            'telf5'        => trim($row['telf5'] ?? ''),
            'movistar'     => intval($row['movistar'] ?? 0),
            'entel'        => intval($row['entel'] ?? 0),
            'claro'        => intval($row['claro'] ?? 0),
            'bitel'        => intval($row['bitel'] ?? 0),
            'tipificacion' => 'pendiente',
            'assigned_to'  => $assignedTo,
            'recall_at'    => null,
        ]);

        $this->updated++;
        return null; // null porque no creamos, ya actualizamos arriba
    }
}