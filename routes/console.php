<?php

use App\Jobs\LiberarLeadsRecall;
use App\Jobs\ReciclarLeadsNoInteresados;
use Illuminate\Support\Facades\Schedule;

// Cada 5 minutos: devolver leads con recall vencido al asesor
Schedule::job(new LiberarLeadsRecall)
    ->everyFiveMinutes()
    ->name('liberar-leads-recall')
    ->withoutOverlapping();

// Medianoche diaria: reciclar no interesados de +30 días al admin
Schedule::job(new ReciclarLeadsNoInteresados)
    ->dailyAt('00:00')
    ->name('reciclar-leads-no-interesados')
    ->withoutOverlapping();