<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class GenerateLeadsTemplate extends Command
{
    protected $signature   = 'leads:template';
    protected $description = 'Genera un Excel de prueba para importar leads';

    public function handle(): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        // Cabeceras
        $headers = [
            'ruc', 'razon_social', 'estado_sunat', 'segmento', 'giro_negocio',
            'dpto', 'prov', 'dist', 'nombre_rl', 'dni_rl', 'correo_rl',
            'movistar', 'entel', 'claro', 'bitel',
            'telf1', 'telf2', 'telf3', 'telf4', 'telf5',
            'comentario', 'asignar_a',
        ];

        foreach ($headers as $i => $header) {
            $sheet->setCellValueByColumnAndRow($i + 1, 1, $header);
        }

        // Datos de prueba
        $rows = [
            ['20123456781', 'Empresa Alpha SAC',    'ACTIVO-HABIDO', 'pyme',    'Comercio',      'LIMA',      'LIMA',      'MIRAFLORES', 'Juan Pérez',    '12345678', 'juan@alpha.com',    2, 1, 0, 0, '987654321', '987654322', '', '', '', 'Cliente potencial', ''],
            ['20123456782', 'Beta Tecnología EIRL', 'ACTIVO-HABIDO', 'micro',   'Tecnología',    'LIMA',      'LIMA',      'SAN ISIDRO', 'Ana Gómez',     '23456789', 'ana@beta.com',      0, 2, 1, 0, '912345678', '',          '', '', '', '',                  'admin@crm.com'],
            ['20123456783', 'Gamma Servicios SRL',  'ACTIVO-HABIDO', 'mayores', 'Servicios',     'AREQUIPA',  'AREQUIPA',  'AREQUIPA',   'Carlos Ruiz',   '34567890', 'carlos@gamma.com',  1, 0, 2, 1, '923456789', '923456790', '', '', '', 'Llamar en la tarde',''],
            ['20123456784', 'Delta Logística SA',   'ACTIVO-HABIDO', 'pyme',    'Logística',     'LA LIBERTAD','TRUJILLO', 'TRUJILLO',   'María Torres',  '45678901', 'maria@delta.com',   3, 0, 0, 2, '934567890', '',          '', '', '', '',                  ''],
            ['20123456785', 'Epsilon Foods SAC',    'ACTIVO-HABIDO', 'nuevo',   'Alimentos',     'CUSCO',     'CUSCO',     'CUSCO',      'Pedro Díaz',    '56789012', 'pedro@epsilon.com', 0, 1, 1, 0, '945678901', '945678902', '', '', '', 'Interesado en plan',''],
        ];

        foreach ($rows as $i => $row) {
            foreach ($row as $j => $value) {
                $sheet->setCellValueByColumnAndRow($j + 1, $i + 2, $value);
            }
        }

        $path = public_path('leads_template.xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save($path);

        $this->info("Archivo generado en: public/leads_template.xlsx");
    }
}