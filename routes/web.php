<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\VentaController;
use App\Http\Controllers\Admin\CacController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Rutas solo admin
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', UserController::class);
    Route::post('cacs/import', [CacController::class, 'import'])->name('cacs.import');
    Route::get('cacs', [CacController::class, 'index'])->name('cacs.index');
});

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    // CACs — búsqueda usada por asesores al crear venta (solo requiere auth, no admin)
    Route::get('cacs/search', [CacController::class, 'search'])->name('cacs.search');

    // 1. RUTAS DE ACCIÓN PRIMERO (Sin parámetros)
    Route::post('leads/assign', [LeadController::class, 'assign'])->name('leads.assign');
    Route::post('leads/import', [LeadController::class, 'import'])->name('leads.import');
    Route::get('leads/export-wrong-number', [LeadController::class, 'exportWrongNumber'])->name('leads.export-wrong-number');
    Route::post('leads/import-wrong-number', [LeadController::class, 'importWrongNumber'])->name('leads.import-wrong-number');

    // 2. RUTAS CON PARÁMETROS
    Route::post('leads/{lead}/assign-single', [LeadController::class, 'assignSingle'])->name('leads.assign-single');
    Route::patch('leads/{lead}/release', [LeadController::class, 'release'])->name('leads.release');

    // 3. RESOURCE AL FINAL
    Route::resource('leads', LeadController::class);

    // Ventas admin — sin parámetros primero
    Route::get('ventas', [VentaController::class, 'adminIndex'])->name('ventas.index');
    Route::get('ventas/documentos/{documento}', [VentaController::class, 'downloadDocumento'])->name('ventas.documento.download');
    // Con parámetros al final
    Route::get('ventas/{venta}', [VentaController::class, 'mesaShow'])->name('ventas.show');
    Route::patch('ventas/{venta}/aprobar-edicion', [VentaController::class, 'aprobarEdicion'])->name('ventas.aprobar-edicion');
    Route::patch('ventas/{venta}/rechazar-edicion', [VentaController::class, 'rechazarEdicion'])->name('ventas.rechazar-edicion');
});

// Rutas del asesor
Route::middleware(['auth'])->prefix('asesor')->name('asesor.')->group(function () {
    Route::get('leads', [LeadController::class, 'asesor'])->name('leads.index');
    Route::post('leads/tipificar', [LeadController::class, 'tipificar'])->name('leads.tipificar');

    // Ventas asesor — sin parámetros primero
    Route::get('ventas/create-directo', [VentaController::class, 'createDirecto'])->name('ventas.create-directo');
    Route::get('ventas/create', [VentaController::class, 'create'])->name('ventas.create');
    Route::get('ventas', [VentaController::class, 'asesorIndex'])->name('ventas.index');
    Route::post('ventas', [VentaController::class, 'store'])->name('ventas.store');
    // Con parámetros al final
    Route::get('ventas/documentos/{documento}', [VentaController::class, 'downloadDocumento'])->name('ventas.documento.download');
    Route::get('ventas/{venta}', [VentaController::class, 'asesorShow'])->name('ventas.show');
    Route::get('ventas/{venta}/edit', [VentaController::class, 'edit'])->name('ventas.edit');
    Route::put('ventas/{venta}', [VentaController::class, 'update'])->name('ventas.update');
    Route::post('ventas/{venta}/solicitar-edicion', [VentaController::class, 'solicitarEdicion'])->name('ventas.solicitar-edicion');
});

// Rutas de mesa de control
Route::middleware(['auth'])->prefix('mesa')->name('mesa.')->group(function () {
    Route::get('ventas', [VentaController::class, 'mesaIndex'])->name('ventas.index');
    Route::get('ventas/{venta}', [VentaController::class, 'mesaShow'])->name('ventas.show');
    Route::patch('ventas/{venta}', [VentaController::class, 'mesaUpdate'])->name('ventas.update');
    Route::patch('ventas/{venta}/aprobar-edicion', [VentaController::class, 'aprobarEdicion'])->name('ventas.aprobar-edicion');
    Route::patch('ventas/{venta}/rechazar-edicion', [VentaController::class, 'rechazarEdicion'])->name('ventas.rechazar-edicion');
});

require __DIR__.'/auth.php';