<?php

namespace App\Jobs;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ReciclarLeadsNoInteresados implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $leads = Lead::where('tipificacion', 'no_interesado')
                     ->where('updated_at', '<=', now()->subDays(30))
                     ->get();

        $count = 0;

        foreach ($leads as $lead) {
            $lead->update([
                'tipificacion' => 'pendiente',
                'assigned_to'  => null,
                'recall_at'    => null,
                'released_at'  => now(),
            ]);
            $count++;
        }

        if ($count > 0) {
            Log::info("ReciclarLeadsNoInteresados: {$count} leads reciclados al admin.");
        }
    }
}