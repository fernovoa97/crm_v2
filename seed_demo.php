<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Lead;
use App\Models\Venta;
use Illuminate\Support\Facades\Hash;

// ── Config ─────────────────────────────────────────────────────────
$NUM_JEFES              = 2;
$SUPERVISORES_POR_JEFE  = 2;
$ASESORES_POR_SUPERVISOR = 3;
$LEADS_POR_ASESOR       = 12;

// Distribución de tipificación (debe sumar 100)
$DISTRIBUCION = [
    'pendiente'          => 30,
    'prospecto'          => 25,
    'volver_llamar'      => 15,
    'no_interesado'      => 15,
    'no_califica'        => 8,
    'numero_equivocado'  => 5,
    'lista_negra'        => 2,
];

// ── Helpers ────────────────────────────────────────────────────────
$rucCounter = random_int(1000000, 8000000);
function nextRuc(&$counter) {
    return '20' . str_pad($counter++, 9, '0', STR_PAD_LEFT);
}

function tipificacionAleatoria(array $dist) {
    $r = random_int(1, 100);
    $acum = 0;
    foreach ($dist as $tip => $peso) {
        $acum += $peso;
        if ($r <= $acum) return $tip;
    }
    return 'pendiente';
}

$nombres = ['Carlos', 'María', 'José', 'Ana', 'Luis', 'Rosa', 'Miguel', 'Carmen', 'Jorge', 'Lucía',
            'Fernando', 'Patricia', 'Diego', 'Sofía', 'Ricardo', 'Valeria', 'Andrés', 'Daniela',
            'Manuel', 'Gabriela'];
$apellidos = ['García', 'Rodríguez', 'Fernández', 'López', 'Martínez', 'Sánchez', 'Pérez', 'Torres',
              'Flores', 'Vásquez', 'Ramírez', 'Chávez', 'Rojas', 'Mendoza', 'Castro'];

function nombreAleatorio($nombres, $apellidos) {
    return $nombres[array_rand($nombres)] . ' ' . $apellidos[array_rand($apellidos)];
}

$empresas = ['Comercial', 'Distribuidora', 'Servicios', 'Inversiones', 'Corporación', 'Grupo',
             'Importaciones', 'Textiles', 'Constructora', 'Tecnología'];
$rubros   = ['del Norte', 'del Sur', 'Andina', 'Peruana', 'Internacional', 'Express', 'Global', 'Lima'];

function empresaAleatoria($empresas, $rubros) {
    return $empresas[array_rand($empresas)] . ' ' . $rubros[array_rand($rubros)] . ' SAC';
}

$creados = ['jefes' => 0, 'supervisores' => 0, 'asesores' => 0, 'leads' => 0, 'ventas' => 0];
$timestamp = now()->format('YmdHis');

// ── Crear jerarquía ────────────────────────────────────────────────
for ($j = 1; $j <= $NUM_JEFES; $j++) {
    $jefe = User::create([
        'name'     => 'Jefe ' . nombreAleatorio($nombres, $apellidos),
        'email'    => "jefe.demo{$timestamp}{$j}@crm.com",
        'password' => Hash::make('password123'),
        'role'     => 'jefe',
        'status'   => 'activo',
    ]);
    $creados['jefes']++;

    // 2 leads sueltos en la bandeja del jefe (sin delegar)
    for ($k = 0; $k < 2; $k++) {
        Lead::create([
            'ruc'          => nextRuc($rucCounter),
            'razon_social' => empresaAleatoria($empresas, $rubros),
            'nombre_rl'    => nombreAleatorio($nombres, $apellidos),
            'telf1'        => '9' . random_int(10000000, 99999999),
            'tipificacion' => 'pendiente',
            'assigned_to'  => $jefe->id,
        ]);
        $creados['leads']++;
    }

    for ($s = 1; $s <= $SUPERVISORES_POR_JEFE; $s++) {
        $supervisor = User::create([
            'name'          => 'Supervisor ' . nombreAleatorio($nombres, $apellidos),
            'email'         => "supervisor.demo{$timestamp}{$j}{$s}@crm.com",
            'password'      => Hash::make('password123'),
            'role'          => 'supervisor',
            'status'        => 'activo',
            'supervisor_id' => $jefe->id,
        ]);
        $creados['supervisores']++;

        // 2 leads sueltos en la bandeja del supervisor (sin delegar)
        for ($k = 0; $k < 2; $k++) {
            Lead::create([
                'ruc'          => nextRuc($rucCounter),
                'razon_social' => empresaAleatoria($empresas, $rubros),
                'nombre_rl'    => nombreAleatorio($nombres, $apellidos),
                'telf1'        => '9' . random_int(10000000, 99999999),
                'tipificacion' => 'pendiente',
                'assigned_to'  => $supervisor->id,
            ]);
            $creados['leads']++;
        }

        for ($a = 1; $a <= $ASESORES_POR_SUPERVISOR; $a++) {
            $asesor = User::create([
                'name'          => 'Asesor ' . nombreAleatorio($nombres, $apellidos),
                'email'         => "asesor.demo{$timestamp}{$j}{$s}{$a}@crm.com",
                'password'      => Hash::make('password123'),
                'role'          => 'asesor',
                'status'        => 'activo',
                'supervisor_id' => $supervisor->id,
            ]);
            $creados['asesores']++;

            for ($l = 0; $l < $LEADS_POR_ASESOR; $l++) {
                $tip = tipificacionAleatoria($DISTRIBUCION);

                // Un ~20% de los leads quedan "viejos" (más de 7 días) para
                // probar la alerta de "pendiente antiguo"
                $creadoHace = (random_int(1, 100) <= 20) ? random_int(8, 25) : random_int(0, 6);

                $recallAt = null;
                if ($tip === 'volver_llamar') {
                    // Mitad vencidos, mitad a futuro
                    $recallAt = (random_int(0, 1) === 0)
                        ? now()->subDays(random_int(1, 5))
                        : now()->addDays(random_int(1, 5));
                }

                $lead = Lead::create([
                    'ruc'          => nextRuc($rucCounter),
                    'razon_social' => empresaAleatoria($empresas, $rubros),
                    'segmento'     => ['micro', 'pyme', 'nuevo', 'mayores'][array_rand(['micro', 'pyme', 'nuevo', 'mayores'])],
                    'departamento' => ['LIMA', 'AREQUIPA', 'LA LIBERTAD', 'CUSCO', 'PIURA'][array_rand(['LIMA', 'AREQUIPA', 'LA LIBERTAD', 'CUSCO', 'PIURA'])],
                    'nombre_rl'    => nombreAleatorio($nombres, $apellidos),
                    'telf1'        => '9' . random_int(10000000, 99999999),
                    'correo_rl'    => 'contacto' . random_int(100, 999) . '@empresa.com',
                    'tipificacion' => $tip,
                    'assigned_to'  => $asesor->id,
                    'recall_at'    => $recallAt,
                ]);
                $lead->timestamps = false;
                $lead->created_at = now()->subDays($creadoHace);
                $lead->updated_at = now()->subDays(max(0, $creadoHace - 1));
                $lead->save();
                $creados['leads']++;

                // ~20% de los "prospecto" ya tienen una venta en proceso (conversión)
                if ($tip === 'prospecto' && random_int(1, 100) <= 20) {
                    Venta::create([
                        'lead_id'               => $lead->id,
                        'asesor_id'             => $asesor->id,
                        'tipo'                  => random_int(0, 1) ? 'movil' : 'fija',
                        'tipo_ingreso'          => 'pdv',
                        'estado'                => ['en_proceso', 'completada', 'enviada'][array_rand(['en_proceso', 'completada', 'enviada'])],
                        'ruc'                   => $lead->ruc,
                        'razon_social'          => $lead->razon_social,
                        'nombre_representante'  => $lead->nombre_rl,
                        'tipo_documento'        => 'dni',
                        'nro_documento'         => (string) random_int(10000000, 99999999),
                        'telefono_representante'=> $lead->telf1,
                        'correo'                => $lead->correo_rl,
                    ]);
                    $creados['ventas']++;
                }
            }
        }
    }
}

echo "✅ Listo. Se crearon:\n";
echo "  - {$creados['jefes']} jefes\n";
echo "  - {$creados['supervisores']} supervisores\n";
echo "  - {$creados['asesores']} asesores\n";
echo "  - {$creados['leads']} leads\n";
echo "  - {$creados['ventas']} ventas (para probar conversión)\n";
echo "\nTodos los usuarios de prueba tienen contraseña: password123\n";
echo "Sus emails empiezan con 'jefe.demo', 'supervisor.demo' o 'asesor.demo'.\n";