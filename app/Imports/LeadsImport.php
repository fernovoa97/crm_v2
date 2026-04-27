<?php

namespace App\Imports;

use App\Models\Lead;
use App\Models\User;
use App\Models\Blacklist;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;

class LeadsImport implements ToModel, WithHeadingRow, SkipsOnError
{
    use SkipsErrors;

    public int $imported = 0;
    public int $skipped  = 0;

    public function model(array $row): ?Lead
    {
        $ruc = trim($row['ruc'] ?? '');

        if (!$ruc) {
            $this->skipped++;
            return null;
        }

        // Verificar blacklist
        if (Blacklist::where('ruc', $ruc)->exists()) {
            $this->skipped++;
            return null;
        }

        // Verificar teléfonos en blacklist
        $telefonos = array_filter([
            trim($row['telf1'] ?? ''),
            trim($row['telf2'] ?? ''),
            trim($row['telf3'] ?? ''),
            trim($row['telf4'] ?? ''),
            trim($row['telf5'] ?? ''),
        ]);

        if ($telefonos && Blacklist::whereIn('telefono', $telefonos)->exists()) {
            $this->skipped++;
            return null;
        }

        // Si el RUC ya existe, saltarlo
        if (Lead::where('ruc', $ruc)->exists()) {
            $this->skipped++;
            return null;
        }

        // Resolver asignación
        $assignedTo = null;
        $asignarA   = trim($row['asignar_a'] ?? '');

        if ($asignarA) {
            $user = User::where('email', $asignarA)->first();
            if ($user) {
                $assignedTo = $user->id;
            }
        }

        $this->imported++;

        return new Lead([
            'ruc'          => $ruc,
            'razon_social' => trim($row['razon_social'] ?? ''),
            'estado_sunat' => trim($row['estado_sunat'] ?? ''),
            'segmento'     => $this->resolveSegmento($row['segmento'] ?? ''),
            'giro_negocio' => trim($row['giro_negocio'] ?? ''),
            'departamento' => trim($row['dpto'] ?? ''),
            'provincia'    => trim($row['prov'] ?? ''),
            'distrito'     => trim($row['dist'] ?? ''),
            'nombre_rl'    => trim($row['nombre_rl'] ?? ''),
            'dni_rl'       => trim($row['dni_rl'] ?? ''),
            'correo_rl'    => trim($row['correo_rl'] ?? ''),
            'movistar'     => intval($row['movistar'] ?? 0),
            'entel'        => intval($row['entel'] ?? 0),
            'claro'        => intval($row['claro'] ?? 0),
            'bitel'        => intval($row['bitel'] ?? 0),
            'telf1'        => trim($row['telf1'] ?? ''),
            'telf2'        => trim($row['telf2'] ?? ''),
            'telf3'        => trim($row['telf3'] ?? ''),
            'telf4'        => trim($row['telf4'] ?? ''),
            'telf5'        => trim($row['telf5'] ?? ''),
            'comentario'   => trim($row['comentario'] ?? ''),
            'assigned_to'  => $assignedTo,
            'tipificacion' => 'pendiente',
        ]);
    }

    private function resolveSegmento(string $valor): ?string
    {
        $valor = strtolower(trim($valor));
        $map   = [
            'micro'   => 'micro',
            'pyme'    => 'pyme',
            'nuevo'   => 'nuevo',
            'mayores' => 'mayores',
        ];
        return $map[$valor] ?? null;
    }
}