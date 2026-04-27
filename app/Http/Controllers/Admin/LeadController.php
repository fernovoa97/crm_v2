<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\LeadsImport;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class LeadController extends Controller
{
    public function index()
    {
        $user  = auth()->user();
        $query = Lead::with('assignedTo');

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
            // Asesores solo ven lo suyo
            $query->where('assigned_to', $user->id);
            $availableUsers = collect();
        }

        $leads = $query->orderBy('created_at', 'desc')->paginate(50);
        return view('admin.leads.index', compact('leads', 'availableUsers'));
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

        // 🧠 2. Construir query base con protección de alcance (Scope)
        $query = Lead::whereIn('id', $leadIds);

        // Si no es admin, filtramos para que solo pueda asignar lo que le pertenece
        if (!$user->isAdmin()) {
            if ($user->isJefe()) {
                $supervisorIds = $user->subordinates()->pluck('id');
                $asesorIds     = User::whereIn('supervisor_id', $supervisorIds)->pluck('id');
                $myScopeIds    = collect([$user->id])->merge($supervisorIds)->merge($asesorIds);
            } elseif ($user->isSupervisor()) {
                $asesorIds     = $user->subordinates()->pluck('id');
                $myScopeIds    = collect([$user->id])->merge($asesorIds);
            } else {
                return back()->with('error', 'No tienes permisos para asignar leads.');
            }

            // Aplicamos el filtro: debe ser de mi equipo O estar sin asignar
            $query->where(function($q) use ($myScopeIds) {
                $q->whereIn('assigned_to', $myScopeIds)
                  ->orWhereNull('assigned_to');
            });
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

            foreach ($subset as $lead) {
                $lead->assigned_to = $userId;
                $lead->save();
            }

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
}