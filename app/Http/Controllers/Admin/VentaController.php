<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use App\Models\VentaLinea;
use App\Models\VentaDocumento;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VentaController extends Controller
{
    // ── ASESOR: formulario venta desde lead ──────────────────────
    public function create(Request $request)
    {
        $user = auth()->user();

        if (!$user->isAsesor()) {
            abort(403);
        }

        $lead = Lead::where('id', $request->lead_id)
                    ->where('assigned_to', $user->id)
                    ->where('tipificacion', 'prospecto')
                    ->firstOrFail();

        return view('asesor.ventas.create', compact('lead'));
    }

    // ── ASESOR: formulario venta directa (sin lead previo) ───────
    public function createDirecto()
    {
        $user = auth()->user();
        if (!$user->isAsesor()) abort(403);

        $lead = new Lead([
            'ruc'         => old('ruc', ''),
            'razon_social'=> old('razon_social', ''),
            'nombre_rl'   => old('nombre_representante', ''),
            'telf1'       => old('telefono_representante', ''),
            'correo_rl'   => old('correo', ''),
        ]);

        return view('asesor.ventas.create', compact('lead'));
    }

    // ── ASESOR: crear venta ──────────────────────────────────────
    public function store(Request $request)
    {
        $user = auth()->user();

        if (!$user->isAsesor()) {
            return back()->with('error', 'No tienes permiso para registrar ventas.');
        }

        // ── Resolver el lead ──────────────────────────────────────
        if ($request->filled('lead_id')) {
            // Flujo normal: lead existente asignado al asesor
            $lead = Lead::where('id', $request->lead_id)
                        ->where('assigned_to', $user->id)
                        ->where('tipificacion', 'prospecto')
                        ->firstOrFail();
        } else {
            // Flujo directo: validar datos mínimos primero
            $request->validate([
                'ruc' => 'required|digits:11',
                'razon_social'             => 'required|string|max:255',
                'nombre_representante'     => 'required|string|max:255',
                'telefono_representante' => ['required','regex:/^9[0-9]{8}$/'],
                'correo'                   => 'required|email',
                'segmento'                 => 'nullable|in:micro,pyme,nuevo,mayores',
            ]);

            if ($request->boolean('guardar_como_lead')) {

                // Si ya existe un lead con este RUC, no lo reutilizamos a ciegas:
                // podría pertenecer a otro asesor o ya ser cliente. Solo reusamos
                // uno existente si ya está asignado a este mismo asesor.
                $existente = Lead::where('ruc', $request->ruc)->first();

                if ($existente && (int) $existente->assigned_to !== (int) $user->id) {
                    return back()
                        ->withErrors(['ruc' => 'Ya existe un lead registrado con este RUC, asignado a otro asesor. Contacta a tu supervisor si crees que esto es un error.'])
                        ->withInput();
                }

                $lead = Lead::firstOrCreate(
                    ['ruc' => $request->ruc],
                    [
                        'razon_social' => $request->razon_social,
                        'nombre_rl'    => $request->nombre_representante,
                        'telf1'        => $request->telefono_representante,
                        'correo_rl'    => $request->correo,
                        'assigned_to'  => $user->id,
                        'tipificacion' => 'prospecto',
                        'segmento'     => $request->segmento,
                        'departamento' => $request->departamento,
                    ]
                );

            } else {

                // Lead temporal solo para crear la venta
                $lead = new Lead([
                    'ruc'            => $request->ruc,
                    'razon_social'   => $request->razon_social,
                    'nombre_rl'      => $request->nombre_representante,
                    'telf1'          => $request->telefono_representante,
                    'correo_rl'      => $request->correo,
                    'segmento'       => $request->segmento,
                    'departamento'   => $request->departamento,
                ]);
            }
        }

        // ── Validación ────────────────────────────────────────────
        $rules = [
            'tipo'                   => 'required|in:movil,fija',
            'tipo_ingreso'           => 'required|in:pdv,centralizado,almacen_propio',
            'nombre_representante'   => 'required|string',
            'tipo_documento'         => 'required|in:dni,ce',
            'nro_documento'          => 'required|string|max:20',
            'telefono_representante' => 'required|string',
            'correo'                 => 'required|email',
        ];

        if ($request->tipo === 'fija') {
            $rules = array_merge($rules, [
                'tipo_venta_fija'       => 'required|in:alta,porta',
                'direccion_instalacion' => 'required|string',
                'tecnologia'            => 'required|in:hfc,ftth',
                'campana_fija'          => 'required',
                'tipo_producto_fija'    => 'required|in:1play,2play,3play',
            ]);
        }

        if ($request->tipo === 'movil') {
            $rules = array_merge($rules, [
                'tipo_venta_movil'    => 'required|in:alta,porta,renovacion',
                'tipo_entrega'        => 'required|in:delivery,recojo_cac',
                'campana_movil'       => 'required|in:claro_negocios,claro_emprendedor',
                'fecha_despacho'      => 'required|date',
                'rango_horario'       => 'required|in:sla_3h,9-11,11-1,2-4,4-6',
                'lineas'              => 'required|array|min:1',
                'lineas.*.plan'       => 'required|string',
                'lineas.*.equipo_sim' => 'required|in:sim_card,sim_card_equipo',
                'lineas.*.descuento'  => 'required|in:no_aplica,50%,bajo_plantilla',
            ]);
        }

        $request->validate($rules);

        // ── Crear la venta ────────────────────────────────────────
        $venta = Venta::create([
            'lead_id' => $lead->exists ? $lead->id : null,
            'asesor_id'            => $user->id,
            'tipo'                 => $request->tipo,
            'tipo_ingreso'         => $request->tipo_ingreso,
            'estado'               => 'enviada',
            'ruc'                  => $lead->ruc,
            'razon_social'         => $lead->razon_social,
            'nombre_representante' => $request->nombre_representante,
            'tipo_documento'       => $request->tipo_documento,
            'nro_documento'        => $request->nro_documento,
            'telefono_representante' => $request->telefono_representante,
            'correo'               => $request->correo,
            'nro_sec'              => $request->nro_sec,

            // Fija
            'tipo_venta_fija'                  => $request->tipo_venta_fija,
            'operador_cedente_fija'            => $request->operador_cedente_fija,
            'telefono_fijo_migrar'             => $request->telefono_fijo_migrar,
            'coordenadas_cobertura'            => $request->coordenadas_cobertura,
            'plano_cobertura'                  => $request->plano_cobertura,
            'direccion_instalacion'            => $request->direccion_instalacion,
            'referencia_direccion_instalacion' => $request->referencia_direccion_instalacion,
            'direccion_facturacion_fija'       => $request->direccion_facturacion_fija,
            'telefono_sot'                     => $request->telefono_sot,
            'tecnologia'                       => $request->tecnologia,
            'campana_fija'                     => $request->campana_fija,
            'tipo_producto_fija'               => $request->tipo_producto_fija,
            'plan_telefonia'                   => $request->boolean('plan_telefonia'),
            'plan_cable_standar'               => $request->boolean('plan_cable_standar'),
            'plan_cable_superior'              => $request->boolean('plan_cable_superior'),
            'plan_internet_200'                => $request->boolean('plan_internet_200'),
            'plan_internet_400'                => $request->boolean('plan_internet_400'),
            'plan_internet_1500'               => $request->boolean('plan_internet_1500'),
            'cantidad_decos'                   => $request->cantidad_decos ?? 0,
            'cantidad_repetidores'             => $request->cantidad_repetidores ?? 0,
            'precio_servicio'                  => $request->precio_servicio,
            'bono_fija'                        => $request->bono_fija,
            'descuento_fija'                   => $request->descuento_fija,
            'full_claro'                       => $request->full_claro,
            'nro_movil_fullclaro'              => $request->nro_movil_fullclaro,

            // Móvil
            'tipo_venta_movil'            => $request->tipo_venta_movil,
            'tipo_entrega'                => $request->tipo_entrega,
            'cac_id'                      => $request->cac_id,
            'coordenadas_geodir'          => $request->coordenadas_geodir,
            'plano_geodir'                => $request->plano_geodir,
            'direccion_entrega'           => $request->direccion_entrega,
            'referencias_entrega'         => $request->referencias_entrega,
            'direccion_facturacion_movil' => $request->direccion_facturacion_movil,
            'telefono_biometria'          => $request->telefono_biometria,
            'telefono_referencia_movil'   => $request->telefono_referencia_movil,
            'campana_movil'               => $request->campana_movil,
            'fecha_despacho'              => $request->fecha_despacho,
            'rango_horario'               => $request->rango_horario,
            'descuento_movil'             => $request->descuento_movil,
            'nro_wf'                      => $request->tipo === 'fija' ? $request->nro_wf : null,
            'comentario_despacho'         => $request->comentario_despacho,
        ]);

        // ── Guardar líneas (solo móvil) ───────────────────────────
        if ($request->tipo === 'movil' && $request->has('lineas')) {
            foreach ($request->lineas as $linea) {
                VentaLinea::create([
                    'venta_id'              => $venta->id,
                    'nro_portar'            => $linea['nro_portar'] ?? null,
                    'plan'                  => $linea['plan'],
                    'operador_cedente'      => $linea['operador_cedente'] ?? null,
                    'operador_cedente_otro' => $linea['operador_cedente_otro'] ?? null,
                    'equipo_sim'            => $linea['equipo_sim'],
                    'modelo_equipo'         => $linea['modelo_equipo'] ?? null,
                    'descuento'             => $linea['descuento'],
                    'nro_wf'                => $linea['nro_wf'] ?? null,
                    'large_asociada'        => $linea['large_asociada'] ?? null,
                ]);
            }
        }

        // ── Guardar documentos ────────────────────────────────────
        if ($request->hasFile('documentos')) {
            foreach ($request->file('documentos') as $file) {
                $path = $file->store('ventas/documentos', 'private');
                VentaDocumento::create([
                    'venta_id'        => $venta->id,
                    'nombre_original' => $file->getClientOriginalName(),
                    'path'            => $path,
                    'mime_type'       => $file->getMimeType(),
                    'size'            => $file->getSize(),
                    'subido_por'      => $user->id,
                ]);
            }
        }

        return redirect()->route('asesor.leads.index')
            ->with('notif_tip', 'Venta registrada y enviada a Mesa de Control.');
    }

    // ── MESA DE CONTROL: detalle ──────────────────────────────────
    public function mesaShow(Venta $venta)
    {
        $venta->load(['lead', 'lineas', 'documentos', 'asesor', 'mesaControl']);
        return view('mesa.ventas.show', compact('venta'));
    }

    public function aprobarEdicion(Venta $venta)
    {
        if (!auth()->user()->isMesaControl() && !auth()->user()->isAdmin()) abort(403);
        $venta->update(['solicitud_edicion' => false, 'estado' => 'en_proceso']);
        return back()->with('success', 'Edición aprobada. El asesor puede editar la venta.');
    }

    public function rechazarEdicion(Venta $venta)
    {
        if (!auth()->user()->isMesaControl() && !auth()->user()->isAdmin()) abort(403);
        $venta->update(['solicitud_edicion' => false, 'solicitud_edicion_motivo' => null]);
        return back()->with('success', 'Solicitud de edición rechazada.');
    }

    // ── MESA DE CONTROL: listado ──────────────────────────────────
    public function mesaIndex(Request $request)
    {
        $query = Venta::with(['lead', 'asesor', 'mesaControl'])
            ->orderBy('created_at', 'desc');

        if ($request->estado) {
            $query->where('estado', $request->estado);
        } else {
            $query->whereIn('estado', ['enviada', 'en_proceso', 'completada', 'rechazada']);
        }

        if ($request->solicitud) {
            $query->where('solicitud_edicion', true);
        }

        $ventas = $query->paginate(20);
        return view('mesa.ventas.index', compact('ventas'));
    }

    // ── MESA DE CONTROL: editar/completar venta ───────────────────
    public function mesaUpdate(Request $request, Venta $venta)
    {
        $user = auth()->user();

        if (!$user->isMesaControl() && !$user->isAdmin()) {
            return back()->with('error', 'No tienes permiso.');
        }

        $request->validate([
            'estado'               => 'nullable|in:enviada,en_proceso,completada,rechazada',
            'estado_contrato'      => 'nullable|in:pendiente_loteo,pendiente_sigex,conforme,no_conforme',
            'motivo_rechazo'       => 'nullable|string|max:500',
            'correo'               => 'nullable|email',
            'tipo_documento'       => 'nullable|in:dni,ce',
            'tecnologia'           => 'nullable|in:hfc,ftth',
            'tipo_producto_fija'   => 'nullable|in:1play,2play,3play',
            'tipo_venta_fija'      => 'nullable|in:alta,porta',
            'tipo_venta_movil'     => 'nullable|in:alta,porta,renovacion',
            'tipo_entrega'         => 'nullable|in:delivery,recojo_cac',
            'campana_movil'        => 'nullable|in:claro_negocios,claro_emprendedor',
            'fecha_programacion'   => 'nullable|date',
            'fecha_instalacion'    => 'nullable|date',
            'fecha_despacho'       => 'nullable|date',
            'fecha_activacion'     => 'nullable|date',
            'precio_servicio'      => 'nullable|numeric|min:0',
            'cantidad_decos'       => 'nullable|integer|min:0',
            'cantidad_repetidores' => 'nullable|integer|min:0',
            'rango_horario'        => 'nullable|in:sla_3h,9-11,11-1,2-4,4-6',
            'tipo_ingreso'         => 'nullable|in:pdv,centralizado,almacen_propio',
        ]);

        $venta->update($request->only([
            'estado_contrato', 'estado', 'motivo_rechazo',
            'mesa_control_id',

            // Fija — mesa
            'fecha_programacion', 'fecha_instalacion',
            'nro_sot_fija', 'proyecto_fija', 'pedido_fija',

            // Móvil — mesa
            'fecha_activacion', 'descuentos_mesa_movil', 'pedido_movil',

            // Mesa puede editar todo
            'tipo_ingreso', 'nombre_representante', 'tipo_documento',
            'nro_documento', 'telefono_representante', 'correo', 'nro_sec',
            'coordenadas_cobertura', 'plano_cobertura', 'direccion_instalacion',
            'referencia_direccion_instalacion', 'direccion_facturacion_fija',
            'telefono_sot', 'tecnologia', 'campana_fija', 'tipo_producto_fija',
            'plan_telefonia', 'plan_cable_standar', 'plan_cable_superior',
            'plan_internet_200', 'plan_internet_400', 'plan_internet_1500',
            'cantidad_decos', 'cantidad_repetidores', 'precio_servicio',
            'bono_fija', 'descuento_fija', 'full_claro', 'nro_movil_fullclaro',
            'tipo_venta_movil', 'tipo_entrega', 'coordenadas_geodir', 'plano_geodir',
            'direccion_entrega', 'referencias_entrega', 'direccion_facturacion_movil',
            'telefono_biometria', 'telefono_referencia_movil', 'campana_movil',
            'fecha_despacho', 'rango_horario', 'descuento_movil',
            'nro_wf',
        ]));

        if (!$venta->mesa_control_id) {
            $venta->update(['mesa_control_id' => $user->id]);
        }

        // Documentos adicionales que suba mesa
        if ($request->hasFile('documentos')) {
            foreach ($request->file('documentos') as $file) {
                $path = $file->store('ventas/documentos', 'private');
                VentaDocumento::create([
                    'venta_id'        => $venta->id,
                    'nombre_original' => $file->getClientOriginalName(),
                    'path'            => $path,
                    'mime_type'       => $file->getMimeType(),
                    'size'            => $file->getSize(),
                    'subido_por'      => $user->id,
                ]);
            }
        }

        return back()->with('success', 'Venta actualizada correctamente.');
    }

    // ── ASESOR: ver sus ventas ────────────────────────────────────
    public function asesorIndex()
    {
        $user = auth()->user();

        $counts = Venta::where('asesor_id', $user->id)
            ->toBase()
            ->selectRaw('estado, count(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado')
            ->toArray();

        $ventas = Venta::with(['lead', 'lineas', 'documentos', 'mesaControl'])
            ->where('asesor_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('asesor.ventas.index', compact('ventas', 'counts'));
    }

    // ── ASESOR: ver detalle de una venta ──────────────────────────
    public function asesorShow(Venta $venta)
    {
        $user = auth()->user();

        if ($venta->asesor_id !== $user->id) {
            abort(403);
        }

        $venta->load(['lead', 'lineas', 'documentos', 'mesaControl']);

        return view('asesor.ventas.show', compact('venta'));
    }

    // ── ASESOR: formulario de edición ─────────────────────────────
    public function edit(Venta $venta)
    {
        $user = auth()->user();

        if ($venta->asesor_id !== $user->id) abort(403);

        if (!in_array($venta->estado, ['rechazada', 'en_proceso'])) {
            return redirect()->route('asesor.ventas.show', $venta)
                ->with('error', 'Esta venta no puede editarse en su estado actual.');
        }

        $venta->load(['lead', 'lineas', 'documentos']);
        return view('asesor.ventas.edit', compact('venta'));
    }

    // ── ASESOR: guardar edición ───────────────────────────────────
    public function update(Request $request, Venta $venta)
    {
        $user = auth()->user();

        if ($venta->asesor_id !== $user->id) abort(403);

        if (!in_array($venta->estado, ['rechazada', 'en_proceso'])) {
            return redirect()->route('asesor.ventas.show', $venta)
                ->with('error', 'Esta venta no puede editarse en su estado actual.');
        }

        $rules = [
            'tipo_ingreso'           => 'required|in:pdv,centralizado,almacen_propio',
            'nombre_representante'   => 'required|string',
            'tipo_documento'         => 'required|in:dni,ce',
            'nro_documento'          => 'required|string|max:20',
            'telefono_representante' => 'required|string',
            'correo'                 => 'required|email',
        ];

        if ($venta->tipo === 'fija') {
            $rules = array_merge($rules, [
                'tipo_venta_fija'       => 'required|in:alta,porta',
                'direccion_instalacion' => 'required|string',
                'tecnologia'            => 'required|in:hfc,ftth',
                'campana_fija'          => 'required',
                'tipo_producto_fija'    => 'required|in:1play,2play,3play',
            ]);
        }

        if ($venta->tipo === 'movil') {
            $rules = array_merge($rules, [
                'tipo_venta_movil' => 'required|in:alta,porta,renovacion',
                'tipo_entrega'     => 'required|in:delivery,recojo_cac',
                'campana_movil'    => 'required|in:claro_negocios,claro_emprendedor',
                'fecha_despacho'   => 'required|date',
                'rango_horario'    => 'required|in:sla_3h,9-11,11-1,2-4,4-6',
                'lineas'           => 'required|array|min:1',
            ]);
        }

        $request->validate($rules);

        $campos = [
            'tipo_ingreso', 'nombre_representante', 'tipo_documento',
            'nro_documento', 'telefono_representante', 'correo',

            // Fija
            'tipo_venta_fija', 'operador_cedente_fija', 'telefono_fijo_migrar',
            'coordenadas_cobertura', 'plano_cobertura',
            'direccion_instalacion', 'referencia_direccion_instalacion',
            'direccion_facturacion_fija', 'telefono_sot', 'tecnologia',
            'campana_fija', 'tipo_producto_fija',
            'plan_telefonia', 'plan_cable_standar', 'plan_cable_superior',
            'plan_internet_200', 'plan_internet_400', 'plan_internet_1500',
            'cantidad_decos', 'cantidad_repetidores',
            'precio_servicio', 'bono_fija', 'descuento_fija',
            'full_claro', 'nro_movil_fullclaro',

            // Móvil
            'tipo_venta_movil', 'tipo_entrega', 'cac_id',
            'coordenadas_geodir', 'plano_geodir',
            'direccion_entrega', 'referencias_entrega',
            'direccion_facturacion_movil', 'telefono_biometria',
            'telefono_referencia_movil', 'campana_movil',
            'fecha_despacho', 'rango_horario', 'comentario_despacho',
        ];

        $venta->fill($request->only($campos));

        $venta->estado            = 'enviada';
        $venta->solicitud_edicion = false;
        $venta->motivo_rechazo    = null;
        $venta->save();

        if ($venta->tipo === 'movil' && $request->has('lineas')) {
            $venta->lineas()->delete();
            foreach ($request->lineas as $linea) {
                VentaLinea::create([
                    'venta_id'              => $venta->id,
                    'nro_portar'            => $linea['nro_portar'] ?? null,
                    'plan'                  => $linea['plan'],
                    'operador_cedente'      => $linea['operador_cedente'] ?? null,
                    'operador_cedente_otro' => $linea['operador_cedente_otro'] ?? null,
                    'equipo_sim'            => $linea['equipo_sim'],
                    'modelo_equipo'         => $linea['modelo_equipo'] ?? null,
                    'descuento'             => $linea['descuento'],
                    'nro_wf'                => $linea['nro_wf'] ?? null,
                    'large_asociada'        => $linea['large_asociada'] ?? null,
                ]);
            }
        }

        if ($request->hasFile('documentos')) {
            foreach ($request->file('documentos') as $file) {
                $path = $file->store('ventas/documentos', 'private');
                VentaDocumento::create([
                    'venta_id'        => $venta->id,
                    'nombre_original' => $file->getClientOriginalName(),
                    'path'            => $path,
                    'mime_type'       => $file->getMimeType(),
                    'size'            => $file->getSize(),
                    'subido_por'      => $user->id,
                ]);
            }
        }

        return redirect()->route('asesor.ventas.show', $venta)
            ->with('success', 'Venta corregida y reenviada a Mesa de Control.');
    }

    // ── ASESOR: solicitar edición ─────────────────────────────────
    public function solicitarEdicion(Request $request, Venta $venta)
    {
        $user = auth()->user();

        if ($venta->asesor_id !== $user->id) {
            abort(403);
        }

        if (!in_array($venta->estado, ['enviada', 'en_proceso', 'rechazada'])) {
            return back()->with('error', 'No puedes solicitar edición en este estado.');
        }

        $venta->update([
            'solicitud_edicion'        => true,
            'solicitud_edicion_motivo' => $request->motivo,
        ]);

        return back()->with('success', 'Solicitud enviada a Mesa de Control.');
    }

    // ── ADMIN: vista de todas las ventas ──────────────────────────
    public function adminIndex()
    {
        $ventas = Venta::with(['lead', 'asesor', 'mesaControl', 'lineas'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.ventas.index', compact('ventas'));
    }

    // ── DESCARGAR DOCUMENTO ───────────────────────────────────────
    public function downloadDocumento(VentaDocumento $documento)
    {
        $user = auth()->user();

        if (!$user->isAdmin() && !$user->isMesaControl() && $documento->venta->asesor_id !== $user->id) {
            abort(403);
        }

        return Storage::disk('private')->download($documento->path, $documento->nombre_original);
    }
}