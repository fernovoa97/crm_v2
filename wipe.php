<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tablas = [
    'venta_documentos', 'venta_lineas', 'ventas', 'leads', 'blacklist',
    'cacs', 'users', 'sessions', 'jobs', 'job_batches', 'failed_jobs',
    'cache', 'cache_locks', 'password_reset_tokens',
];

DB::statement('SET FOREIGN_KEY_CHECKS=0');
foreach ($tablas as $t) {
    DB::table($t)->truncate();
    echo "Vaciada: $t\n";
}
DB::statement('SET FOREIGN_KEY_CHECKS=1');

echo "\nListo, todas las tablas vacías.\n";