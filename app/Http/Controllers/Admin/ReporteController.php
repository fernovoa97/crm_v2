<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;

class ReporteController extends Controller
{
    /**
     * Reporte de leads trabajados por equipo.
     * - Admin: ve todos los jefes (y puede entrar a uno con ?jefe_id=).
     * - Jefe: ve sus propios supervisores, con drill-down a sus asesores.
     * - Supervisor: ve sus propios asesores directamente.
     */
    public function leads(Request $request)
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            $jefeId = $request->query('jefe_id');

            if ($jefeId) {
                $jefe = User::where('role', 'jefe')->findOrFail($jefeId);
                return $this->vistaJefe($jefe, esAdminViendo: true);
            }

            return $this->vistaAdmin();
        }

        if ($user->isJefe()) {
            return $this->vistaJefe($user, esAdminViendo: false);
        }

        if ($user->isSupervisor()) {
            return $this->vistaSupervisor($user);
        }

        abort(403);
    }

    /**
     * Vista raíz del admin: un resumen por cada jefe del sistema.
     */
    private function vistaAdmin()
    {
        $jefes = User::where('role', 'jefe')->where('status', 'activo')->orderBy('name')->get();

        $filas = $jefes->map(function (User $jefe) {
            $ids = $this->idsBajoJefe($jefe);
            return $this->filaParaScope($jefe->id, $jefe->name, 'jefe', $ids);
        })->values();

        $overview = $this->statsParaScope($filas->flatMap(fn ($f) => $f['scope_ids'])->all());

        return view('admin.reportes.leads', [
            'modo'      => 'admin',
            'overview'  => $overview,
            'filas'     => $filas,
            'titulo'    => 'Reporte de leads — todos los equipos',
        ]);
    }

    /**
     * Vista de un jefe (propia, o vista "como" desde el admin): sus
     * supervisores como filas principales, cada uno con sus asesores.
     */
    private function vistaJefe(User $jefe, bool $esAdminViendo)
    {
        $supervisores = $jefe->subordinates()->where('status', 'activo')->orderBy('name')->get();

        $equipos = $supervisores->map(function (User $sup) {
            $asesores = $sup->subordinates()->where('status', 'activo')->orderBy('name')->get();
            $idsEquipo = collect([$sup->id])->merge($asesores->pluck('id'));

            $filaSupervisor = $this->filaParaScope($sup->id, $sup->name, 'supervisor', $idsEquipo);

            $filasAsesores = $asesores->map(
                fn (User $ase) => $this->filaParaScope($ase->id, $ase->name, 'asesor', collect([$ase->id]))
            )->values();

            return [
                'supervisor' => $filaSupervisor,
                'asesores'   => $filasAsesores,
            ];
        })->values();

        $idsTotal = collect([$jefe->id])
            ->merge($supervisores->pluck('id'))
            ->merge(User::whereIn('supervisor_id', $supervisores->pluck('id'))->pluck('id'));

        $overview = $this->statsParaScope($idsTotal->all());

        // Lo que el propio jefe tiene en su bandeja sin delegar (mismo
        // concepto que usamos en Gestión de Leads).
        $bandejaJefe = $this->filaParaScope($jefe->id, $jefe->name . ' (tu bandeja)', 'jefe_bandeja', collect([$jefe->id]));

        return view('admin.reportes.leads', [
            'modo'        => $esAdminViendo ? 'admin-como-jefe' : 'jefe',
            'jefe'        => $jefe,
            'overview'    => $overview,
            'bandejaJefe' => $bandejaJefe,
            'equipos'     => $equipos,
            'titulo'      => 'Reporte de leads — ' . $jefe->name,
        ]);
    }

    /**
     * Vista de un supervisor: su propia bandeja + una fila por cada asesor.
     */
    private function vistaSupervisor(User $supervisor)
    {
        $asesores = $supervisor->subordinates()->where('status', 'activo')->orderBy('name')->get();

        $filaSupervisor = $this->filaParaScope($supervisor->id, $supervisor->name . ' (tu bandeja)', 'supervisor_bandeja', collect([$supervisor->id]));

        $filasAsesores = $asesores->map(
            fn (User $ase) => $this->filaParaScope($ase->id, $ase->name, 'asesor', collect([$ase->id]))
        )->values();

        $idsTotal = collect([$supervisor->id])->merge($asesores->pluck('id'));
        $overview = $this->statsParaScope($idsTotal->all());

        return view('admin.reportes.leads', [
            'modo'           => 'supervisor',
            'overview'       => $overview,
            'filaSupervisor' => $filaSupervisor,
            'asesores'       => $filasAsesores,
            'titulo'         => 'Reporte de leads — tu equipo',
        ]);
    }

    /**
     * Todos los IDs (jefe + supervisores + asesores) bajo un jefe.
     */
    private function idsBajoJefe(User $jefe)
    {
        $supervisorIds = $jefe->subordinates()->pluck('id');
        $asesorIds     = User::whereIn('supervisor_id', $supervisorIds)->pluck('id');

        return collect([$jefe->id])->merge($supervisorIds)->merge($asesorIds);
    }

    /**
     * Construye una fila de reporte (persona/equipo + su scope de IDs).
     */
    private function filaParaScope(int $id, string $nombre, string $rol, $scopeIds): array
    {
        $ids = collect($scopeIds)->all();

        return array_merge(
            ['id' => $id, 'nombre' => $nombre, 'rol' => $rol, 'scope_ids' => $ids],
            $this->statsParaScope($ids)
        );
    }

    /**
     * Calcula todas las métricas del reporte para un conjunto de IDs de usuario.
     */
    private function statsParaScope(array $ids): array
    {
        if (empty($ids)) {
            $ids = [0]; // evita que whereIn vacío traiga TODO
        }

        $base = Lead::whereIn('assigned_to', $ids);

        $total      = (clone $base)->count();
        $pendiente  = (clone $base)->where('tipificacion', 'pendiente')->count();
        $trabajado  = $total - $pendiente;

        $porTipificacion = (clone $base)
            ->selectRaw('tipificacion, count(*) as total')
            ->groupBy('tipificacion')
            ->pluck('total', 'tipificacion');

        // "Enviadas": el asesor mandó al menos una venta (cualquier estado).
        // "Completadas": mesa de control ya la aprobó — esta es la conversión REAL.
        $ventasEnviadas   = (clone $base)->whereHas('ventas')->count();
        $ventasCompletadas = (clone $base)
            ->whereHas('ventas', fn ($q) => $q->where('estado', 'completada'))
            ->count();

        $recallVencido = (clone $base)
            ->where('tipificacion', 'volver_llamar')
            ->where('recall_at', '<', now())
            ->count();

        $pendienteAntiguo = (clone $base)
            ->where('tipificacion', 'pendiente')
            ->where('created_at', '<', now()->subDays(7))
            ->count();

        return [
            'total'              => $total,
            'pendiente'          => $pendiente,
            'trabajado'          => $trabajado,
            'pct_trabajado'      => $total > 0 ? round($trabajado / $total * 100) : 0,
            'prospecto'          => $porTipificacion['prospecto'] ?? 0,
            'volver_llamar'      => $porTipificacion['volver_llamar'] ?? 0,
            'no_interesado'      => $porTipificacion['no_interesado'] ?? 0,
            'no_califica'        => $porTipificacion['no_califica'] ?? 0,
            'lista_negra'        => $porTipificacion['lista_negra'] ?? 0,
            'numero_equivocado'  => $porTipificacion['numero_equivocado'] ?? 0,
            'enviadas'           => $ventasEnviadas,
            'pct_enviadas'       => $total > 0 ? round($ventasEnviadas / $total * 100) : 0,
            'completadas'        => $ventasCompletadas,
            'pct_completadas'    => $total > 0 ? round($ventasCompletadas / $total * 100) : 0,
            'recall_vencido'     => $recallVencido,
            'pendiente_antiguo'  => $pendienteAntiguo,
        ];
    }
}