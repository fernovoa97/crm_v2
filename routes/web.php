<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\LeadController;

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
});

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    // 1. LAS RUTAS DE ACCIÓN PRIMERO (Sin parámetros)
    Route::post('leads/assign', [LeadController::class, 'assign'])->name('leads.assign');
    Route::post('leads/import', [LeadController::class, 'import'])->name('leads.import');

    // 2. RUTAS CON PARÁMETROS
    Route::post('leads/{lead}/assign-single', [LeadController::class, 'assignSingle'])->name('leads.assign-single');

    // 3. EL RESOURCE AL FINAL
    Route::resource('leads', LeadController::class);
});

// Rutas del asesor
Route::middleware(['auth'])->prefix('asesor')->name('asesor.')->group(function () {
    Route::get('leads', [LeadController::class, 'asesor'])->name('leads.index');
    Route::post('leads/tipificar', [LeadController::class, 'tipificar'])->name('leads.tipificar');
});

require __DIR__.'/auth.php';