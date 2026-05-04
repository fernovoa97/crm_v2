<?php

namespace App\Imports;

use App\Models\Cac;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CacImport implements ToModel, WithHeadingRow
{
    public function model(array $row): ?Cac
    {
        if (empty($row['nombre'])) return null;

        return new Cac([
            'nombre'    => trim($row['nombre']),
            'direccion' => trim($row['direccion']),
        ]);
    }
}