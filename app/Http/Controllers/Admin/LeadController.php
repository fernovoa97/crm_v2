<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\LeadsImport;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Blacklist;

use App\Exports\WrongNumberExport;
use App\Imports\WrongNumberImport;



class LeadController extends Controller
{
    public function exportWrongNumber()
{
    if (!auth()->user()->isAdmin()) {
        return back()->with('error', 'No tienes permiso.');
    }

    return Excel::download(new WrongNumberExport(), 'numeros_equivocados.xlsx');
}

public function importWrongNumber(Request $request)
{
    if (!auth()->user()->isAdmin()) {
        return back()->with('error', 'No tienes permiso.');
    }

    $request->validate([
        'file' => 'required|mimes:xlsx,xls,csv|max:10240',
    ]);

    $import = new WrongNumberImport();
    Excel::import($import, $request->file('file'));

    $msg = "Corrección completada: {$import->updated} leads actualizados, {$import->skipped} omitidos.";
    return redirect()->route('admin.leads.index')->with('success', $msg);
}

public function index()
{
    $user  = auth()->user();
    $query = Lead::with('assignedTo');
    $ids   = collect(); // scope de IDs para roles no-admin

    if ($user->isAdmin()) {
        $availableUsers = User::whereNotIn('role', ['admin', 'mesa_control'])
            ->where('status', 'activo')
            ->orderBy('name')
            ->get(['id', 'name', 'role']);

    } elseif ($user->isJefe()) {

        $supervisorIds = $user->subordinates()->pluck('id');
        $asesorIds     = User::whereIn('supervisor_id', $supervisorIds)->pluck('id');
        $ids           = collect([$user->id])->merge($supervisorIds)->merge($asesorIds);

        $query->whereIn('assigned_to', $ids);

        $availableUsers = User::whereIn('id', $supervisorIds->merge($asesorIds))
            ->where('status', 'activo')
            ->orderBy('name')
            ->get(['id', 'name', 'role']);

    } elseif ($user->isSupervisor()) {

        $asesorIds = $user->subordinates()->pluck('id');
        $ids       = collect([$user->id])->merge($asesorIds);

        $query->whereIn('assigned_to', $ids);

        $availableUsers = $user->subordinates()
            ->where('status', 'activo')
            ->orderBy('name')
            ->get(['id', 'name', 'role']);

    } else {

        $query->where('assigned_to', $user->id);
        $availableUsers = collect();
    }

    $leads = $query->orderBy('created_at', 'desc')->paginate(50);

    /*
    |--------------------------------------------------------------------------
    | Estadísticas globales
    |--------------------------------------------------------------------------
    */

    $stats = [
        'total'             => Lead::count(),
        'sin_asignar'       => Lead::whereNull('assigned_to')->count(),
        'pendiente'         => Lead::where('tipificacion', 'pendiente')->count(),
        'prospecto'         => Lead::where('tipificacion', 'prospecto')->count(),
        'volver_llamar'     => Lead::where('tipificacion', 'volver_llamar')->count(),
        'no_interesado'     => Lead::where('tipificacion', 'no_interesado')->count(),
        'no_califica'       => Lead::where('tipificacion', 'no_califica')->count(),
        'lista_negra'       => Lead::where('tipificacion', 'lista_negra')->count(),
        'numero_equivocado' => Lead::where('tipificacion', 'numero_equivocado')->count(),
    ];

    /*
    |--------------------------------------------------------------------------
    | Botón Exportar Números Equivocados
    |--------------------------------------------------------------------------
    */

    $wrongNumberCount = $stats['numero_equivocado'];

    return view('admin.leads.index', compact(
        'leads',
        'availableUsers',
        'wrongNumberCount',
        'stats'
    ));
}
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        $import = new LeadsImport();
        Excel::import($import, $request->file('file'));

        $msg = "Importación completada: {$import->imported} leads importados, {$import->skipped} omitidos.";
        return redirect()->route('admin.leads.index')->with('success', $msg);
    }

    public function assign(Request $request)
    {
        $request->validate([
            'user_ids'    => 'required|array',
            'user_ids.*'  => 'exists:users,id',
            'user_qtys'   => 'required|array',
            'user_qtys.*' => 'integer|min:1',
            'lead_ids'    => 'required|array', // Validamos que vengan los IDs de leads
        ]);

        $user     = auth()->user();
        $userIds  = $request->user_ids;
        $userQtys = $request->user_qtys;
        $leadIds  = $request->lead_ids;

        // 🔐 1. Validar permisos de los usuarios destino
        foreach ($userIds as $targetId) {
            $target = User::findOrFail($targetId);
            if (!$this->canAssignTo($user, $target)) {
                return back()->with('error', "No tienes permiso para asignar a {$target->name}.");
            }
        }

        // 🧠 2. Construir query — SOLO leads sin asignar
        $query = Lead::whereIn('id', $leadIds)
                    ->whereNull('assigned_to');

        if (!$user->isAdmin()) {
            if ($user->isJefe()) {
                $supervisorIds = $user->subordinates()->pluck('id');
                $asesorIds     = User::whereIn('supervisor_id', $supervisorIds)->pluck('id');
                $myScopeIds    = collect([$user->id])->merge($supervisorIds)->merge($asesorIds);
            } elseif ($user->isSupervisor()) {
                $asesorIds  = $user->subordinates()->pluck('id');
                $myScopeIds = collect([$user->id])->merge($asesorIds);
            } else {
                return back()->with('error', 'No tienes permisos para asignar leads.');
            }
            // El scope aquí ya no filtra assigned_to porque son todos NULL
            // Solo validamos que los lead_ids pertenezcan a leads accesibles por este usuario
            $query->whereIn('id', Lead::whereIn('assigned_to', $myScopeIds)
                                        ->orWhereNull('assigned_to')
                                        ->pluck('id'));
        }

        $leads = $query->get();

        if ($leads->isEmpty()) {
            return back()->with('error', 'No se encontraron leads válidos para asignar.');
        }

        // ⚖️ 3. Distribución
        $index = 0;
        $totalAsignado = 0;

        foreach ($userIds as $i => $userId) {
    $qty = (int) $userQtys[$i];
    $subset = $leads->slice($index, $qty);

    Lead::whereIn('id', $subset->pluck('id'))
        ->update(['assigned_to' => $userId]);

    $index += $qty;
    $totalAsignado += $subset->count();
}

        return back()->with('success', "{$totalAsignado} leads asignados correctamente.");
    }

    public function assignSingle(Request $request, Lead $lead)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user   = auth()->user();
        $target = User::findOrFail($request->user_id);

        if (!$this->canAssignTo($user, $target)) {
            return back()->with('error', "No tienes permiso para asignar a {$target->name}.");
        }

        $lead->update(['assigned_to' => $request->user_id]);
        return back()->with('success', "Lead asignado a {$target->name}.");
    }

    public function destroy(Lead $lead)
    {
        if (!auth()->user()->isAdmin()) {
            return back()->with('error', 'No tienes permiso para eliminar.');
        }
        $lead->delete();
        return redirect()->route('admin.leads.index')->with('success', 'Lead eliminado.');
    }

    public function release(Lead $lead)
{
    $user = auth()->user();

    if ($lead->assigned_to !== $user->id) {
        return back()->with('error', 'No puedes liberar un lead que no es tuyo.');
    }

    $lead->update([
        'assigned_to'  => null,
        'tipificacion' => 'pendiente',
        'recall_at'    => null,
    ]);

    return back()->with('success', 'Lead liberado correctamente.');
}

    private function canAssignTo(User $from, User $to): bool
    {
        if ($from->isAdmin()) return true;

        if ($from->isJefe()) {
            $supervisorIds = $from->subordinates()->pluck('id');
            $asesorIds     = User::whereIn('supervisor_id', $supervisorIds)->pluck('id');
            return $supervisorIds->contains($to->id) || $asesorIds->contains($to->id);
        }

        if ($from->isSupervisor()) {
            return $from->subordinates()->pluck('id')->contains($to->id);
        }

        return false;
    }

    public function asesor()
{
    $user = auth()->user();

    if (!$user->isAsesor()) {
        abort(403);
    }

    $gruposTipificacion = [
        'pendiente',
        'volver_llamar',
        'no_interesado',
        'no_califica',
        'prospecto',
    ];

    $leads = [];
    foreach ($gruposTipificacion as $tip) {
        $query = Lead::where('assigned_to', $user->id)
                     ->where('tipificacion', $tip)
                     ->orderBy('created_at', 'desc');

        if ($tip === 'volver_llamar') {
            $query->orderBy('recall_at', 'asc');
        }
         if ($tip === 'prospecto') {
        $query->withCount('ventas'); // ← agregar
        }


        $leads[$tip] = $query->get();
    }

    $counts = [
        'total'         => array_sum(array_map(fn($c) => $c->count(), $leads)),
        'pendiente'     => $leads['pendiente']->count(),
        'volver_llamar' => $leads['volver_llamar']->count(),
        'no_interesado' => $leads['no_interesado']->count(),
        'no_califica'   => $leads['no_califica']->count(),
        'prospecto'     => $leads['prospecto']->count(),
        'recall_hoy'    => $leads['volver_llamar']
                            ->filter(fn($l) => $l->recall_at && $l->recall_at->isToday())
                            ->count(),
    ];

    return view('admin.leads.asesor', compact('leads', 'counts'));
}

public function tipificar(Request $request)
{
    $request->validate([
        'lead_id'      => 'required|exists:leads,id',
        'tipificacion' => 'required|in:volver_llamar,no_interesado,numero_equivocado,no_califica,lista_negra,prospecto',
        'recall_at'    => 'required_if:tipificacion,volver_llamar|nullable|date|after:now',
    ], [
        'recall_at.required_if' => 'Debes seleccionar una fecha y hora para la rellamada.',
        'recall_at.after'       => 'La fecha de rellamada debe ser en el futuro.',
    ]);

    $user = auth()->user();
    $lead = Lead::where('id', $request->lead_id)
                ->where('assigned_to', $user->id)
                ->firstOrFail();

    $tip = $request->tipificacion;

    if ($tip === 'volver_llamar') {
        $lead->update([
            'tipificacion' => 'volver_llamar',
            'recall_at'    => $request->recall_at,
        ]);
        return redirect()->route('asesor.leads.index')
            ->with('notif_tip', "Lead agendado para el {$lead->recall_at->format('d/m/Y H:i')}.");
    }

    if ($tip === 'no_interesado') {
        $lead->update([
            'tipificacion' => 'no_interesado',
            'recall_at'    => null,
        ]);
        return redirect()->route('asesor.leads.index')
            ->with('notif_tip', 'Lead marcado como No interesado. Se reciclará en 30 días.');
    }

    if ($tip === 'numero_equivocado') {
        $lead->update([
            'tipificacion' => 'numero_equivocado',
            'recall_at'    => null,
        ]);
        return redirect()->route('asesor.leads.index')
            ->with('notif_tip', 'Lead enviado al administrador para corrección de teléfonos.');
    }

    if ($tip === 'no_califica') {
        $lead->update([
            'tipificacion' => 'no_califica',
            'recall_at'    => null,
        ]);
        return redirect()->route('asesor.leads.index')
            ->with('notif_tip', 'Lead marcado como No califica.');
    }

    if ($tip === 'lista_negra') {
        Blacklist::firstOrCreate(
            ['ruc' => $lead->ruc],
            ['motivo' => 'Tipificado lista negra por asesor', 'created_by' => $user->id]
        );

        $telefonos = array_filter([
            $lead->telf1, $lead->telf2, $lead->telf3,
            $lead->telf4, $lead->telf5,
        ]);

        foreach ($telefonos as $telf) {
            Blacklist::firstOrCreate(
                ['telefono' => $telf],
                ['motivo' => 'Teléfono de lead en lista negra', 'created_by' => $user->id]
            );
        }

        $lead->update([
            'tipificacion' => 'lista_negra',
            'recall_at'    => null,
        ]);

        return redirect()->route('asesor.leads.index')
            ->with('notif_tip', 'RUC y teléfonos bloqueados. Lead enviado a lista negra.');
    }

    if ($tip === 'prospecto') {
        $lead->update([
            'tipificacion' => 'prospecto',
            'recall_at'    => null,
        ]);
        return redirect()->route('asesor.leads.index')
            ->with('notif_tip', '¡Lead movido a Prospectos! Ya puedes registrar una venta.');
    }

    return redirect()->route('asesor.leads.index');
}
}