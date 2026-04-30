<?php

namespace App\Jobs;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class LiberarLeadsRecall implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $leads = Lead::where('tipificacion', 'volver_llamar')
                     ->where('recall_at', '<=', now())
                     ->get();

        $count = 0;

        foreach ($leads as $lead) {
            $lead->update([
                'tipificacion' => 'pendiente',
                'recall_at'    => null,
            ]);
            $count++;
        }

        if ($count > 0) {
            Log::info("LiberarLeadsRecall: {$count} leads devueltos a pendiente.");
        }
    }
}