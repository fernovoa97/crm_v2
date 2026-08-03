@extends('layouts.app')

@section('title', 'Detalle de venta')
@section('subtitle'){{ $venta->razon_social }} — {{ $venta->created_at->format('d/m/Y') }}@endsection

@section('topbar-actions')
  <a href="{{ route('asesor.ventas.index') }}" style="
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(255,255,255,0.05); color: rgba(255,255,255,0.5);
    border: 1px solid rgba(255,255,255,0.08);
    padding: 8px 16px; border-radius: 8px;
    font-size: 13px; font-weight: 600;
    text-decoration: none;
  ">
    ← Volver
  </a>
@endsection

@section('content')
<style>
  .detail-layout {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 20px;
    align-items: start;
  }

  .card {
    background: #15151c;
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 14px;
    overflow: hidden;
    margin-bottom: 16px;
  }

  .card-header {
    padding: 14px 20px;
    border-bottom: 1px solid rgba(255,255,255,0.07);
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .card-header-title { font-size: 13px; font-weight: 600; color: #fff; }
  .card-body { padding: 20px; }

  .detail-row {
    display: grid;
    grid-template-columns: 180px 1fr;
    align-items: baseline;
    padding: 10px 0;
    border-bottom: 1px solid rgba(255,255,255,0.04);
    gap: 12px;
  }

  .detail-row:last-child { border-bottom: none; }
  .detail-label { font-size: 12px; color: rgba(255,255,255,0.35); }
  .detail-value { font-size: 12px; color: #fff; font-weight: 500; }

  .section-title {
    font-size: 11px; font-weight: 700;
    text-transform: uppercase; letter-spacing: .6px;
    color: rgba(255,255,255,0.25);
    border-top: 1px solid rgba(255,255,255,0.06);
    padding-top: 14px; margin: 16px 0 12px;
  }

  .check-pill {
    display: inline-block; font-size: 11px;
    padding: 2px 8px; border-radius: 20px; font-weight: 500;
    background: rgba(47,202,245,0.12); color: #2FCAF5;
  }

  .estado-pill {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 12px; padding: 4px 12px;
    border-radius: 20px; font-weight: 600;
  }
  .estado-enviada     { background: rgba(47,202,245,0.12);    color: #2FCAF5; }
  .estado-en_proceso  { background: rgba(239,159,39,0.12);    color: #fac775; }
  .estado-completada  { background: rgba(29,158,117,0.12);    color: #5dcaa5; }
  .estado-rechazada   { background: rgba(255,80,80,0.12);     color: #ff9090; }

  .lineas-table { width: 100%; border-collapse: collapse; }
  .lineas-table th {
    font-size: 10px; font-weight: 600;
    color: rgba(255,255,255,0.25);
    text-transform: uppercase; letter-spacing: .5px;
    padding: 8px 12px; text-align: left;
    border-bottom: 1px solid rgba(255,255,255,0.06);
  }
  .lineas-table td {
    font-size: 12px; color: rgba(255,255,255,0.75);
    padding: 10px 12px;
    border-bottom: 1px solid rgba(255,255,255,0.04);
  }
  .lineas-table tr:last-child td { border-bottom: none; }

  .doc-item {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 14px;
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 8px; margin-bottom: 8px;
    font-size: 12px; color: rgba(255,255,255,0.6);
  }

  .sidebar-card {
    background: #15151c;
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 14px; padding: 18px; margin-bottom: 14px;
  }

  .sidebar-title {
    font-size: 11px; font-weight: 700;
    text-transform: uppercase; letter-spacing: .6px;
    color: rgba(255,255,255,0.25); margin-bottom: 14px;
  }

  .sidebar-row {
    display: grid;
    grid-template-columns: 120px 1fr;
    align-items: baseline;
    gap: 10px;
    margin-bottom: 10px; font-size: 12px;
  }

  .sidebar-label { color: rgba(255,255,255,0.35); }
  .sidebar-value { color: #fff; font-weight: 500; }

  .rechazo-box {
    background: rgba(255,80,80,0.08);
    border: 1px solid rgba(255,80,80,0.2);
    border-radius: 10px; padding: 14px 16px; margin-bottom: 16px;
  }

  .btn-accion {
    width: 100%; padding: 11px; border-radius: 10px;
    font-size: 13px; font-weight: 600;
    cursor: pointer; font-family: 'Sora', sans-serif;
    transition: all .2s; margin-bottom: 8px;
    border: none;
  }

  .btn-editar {
    background: rgba(47,202,245,0.1); color: #2FCAF5;
    border: 1px solid rgba(47,202,245,0.2);
  }
  .btn-editar:hover { background: rgba(47,202,245,0.18); }

  .btn-solicitar {
    background: rgba(239,159,39,0.1); color: #fac775;
    border: 1px solid rgba(239,159,39,0.2);
  }
  .btn-solicitar:hover { background: rgba(239,159,39,0.18); }

  /* Modal solicitud edición */
  .overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,0.72); z-index: 1000;
    align-items: center; justify-content: center;
  }
  .overlay.open { display: flex; }
  .modal {
    background: #1a1a24; border: 1px solid rgba(255,255,255,0.1);
    border-radius: 16px; padding: 24px;
    width: 420px; max-width: 95vw;
  }
  .modal-title { font-size: 15px; font-weight: 600; color: #fff; margin-bottom: 6px; }
  .modal-sub   { font-size: 12px; color: rgba(255,255,255,0.35); margin-bottom: 18px; }
  .modal-textarea {
    width: 100%; box-sizing: border-box;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 8px; padding: 10px 12px;
    font-size: 13px; color: #fff;
    font-family: 'Sora', sans-serif; outline: none;
    resize: vertical; min-height: 80px;
  }
  .modal-footer { display: flex; justify-content: flex-end; gap: 8px; margin-top: 16px; }
  .btn-cancel {
    padding: 8px 18px; border-radius: 8px;
    background: none; border: 1px solid rgba(255,255,255,0.1);
    color: rgba(255,255,255,0.4); cursor: pointer;
    font-size: 13px; font-family: 'Sora', sans-serif;
  }
  .btn-confirm {
    padding: 8px 20px; border-radius: 8px;
    background: #fac775; color: #0f0f13;
    border: none; font-size: 13px; font-weight: 600;
    cursor: pointer; font-family: 'Sora', sans-serif;
  }

  /* Light mode */
  html.light .card { background: #fff; border-color: #d0eaf8; }
  html.light .card-header { border-bottom-color: #e8f3fb; }
  html.light .card-header-title { color: #0f0f13; }
  html.light .detail-label { color: rgba(0,0,0,0.4); }
  html.light .detail-value { color: #0f0f13; }
  html.light .detail-row { border-bottom-color: #f0f7ff; }
  html.light .section-title { color: rgba(0,0,0,0.3); border-top-color: #e8f3fb; }
  html.light .sidebar-card { background: #fff; border-color: #d0eaf8; }
  html.light .sidebar-label { color: rgba(0,0,0,0.4); }
  html.light .sidebar-value { color: #0f0f13; }
  html.light .doc-item { background: #f8fcff; border-color: #e0eef8; color: rgba(0,0,0,0.6); }
  html.light .modal { background: #fff; border-color: #d0eaf8; }
  html.light .modal-title { color: #0f0f13; }
  html.light .modal-textarea { background: #f0f7ff; border-color: #c0dff5; color: #0f0f13; }
</style>

@if(session('success'))
  <div style="background:rgba(29,158,117,0.12);border:1px solid rgba(29,158,117,0.25);color:#5dcaa5;border-radius:10px;padding:10px 14px;font-size:12px;margin-bottom:18px;display:flex;align-items:center;gap:10px;">
    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="#5dcaa5" stroke-width="1.5"><circle cx="8" cy="8" r="6"/><path d="M5 8l2 2 4-4"/></svg>
    {{ session('success') }}
  </div>
@endif

@if(session('error'))
  <div style="background:rgba(255,80,80,0.1);border:1px solid rgba(255,80,80,0.25);color:#ff9090;border-radius:10px;padding:10px 14px;font-size:12px;margin-bottom:18px;">
    {{ session('error') }}
  </div>
@endif

{{-- Motivo de rechazo --}}
@if($venta->estado === 'rechazada' && $venta->motivo_rechazo)
  <div class="rechazo-box">
    <div style="font-size:13px;font-weight:600;color:#ff9090;margin-bottom:6px;">❌ Venta rechazada por Mesa de Control</div>
    <div style="font-size:12px;color:rgba(255,255,255,0.6);">{{ $venta->motivo_rechazo }}</div>
  </div>
@endif

<div class="detail-layout">

  {{-- ── COLUMNA PRINCIPAL ── --}}
  <div>

    {{-- DATOS GENERALES --}}
    <div class="card">
      <div class="card-header">
        <div class="card-header-title">Datos generales</div>
        <span class="estado-pill estado-{{ $venta->estado }}">
          @switch($venta->estado)
            @case('enviada') ✉️ Enviada @break
            @case('en_proceso') ⚙️ En proceso @break
            @case('completada') ✅ Completada @break
            @case('rechazada') ❌ Rechazada @break
          @endswitch
        </span>
      </div>
      <div class="card-body">
        <div class="detail-row">
          <span class="detail-label">Tipo de servicio</span>
          <span class="detail-value">{{ $venta->tipo === 'movil' ? '📱 Móvil' : '🏢 Fija / Internet' }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Tipo de ingreso</span>
          <span class="detail-value">{{ ucfirst(str_replace('_', ' ', $venta->tipo_ingreso)) }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Tipo de venta</span>
          <span class="detail-value">
            {{ $venta->tipo === 'movil'
              ? ucfirst(str_replace('_', ' ', $venta->tipo_venta_movil ?? '—'))
              : ucfirst($venta->tipo_venta_fija ?? '—') }}
          </span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Fecha de registro</span>
          <span class="detail-value">{{ $venta->created_at->format('d/m/Y H:i') }}</span>
        </div>

        <div class="section-title">Datos del cliente</div>

        <div class="detail-row">
          <span class="detail-label">RUC</span>
          <span class="detail-value" style="font-family:monospace;">{{ $venta->ruc }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Razón Social</span>
          <span class="detail-value">{{ $venta->razon_social }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Representante</span>
          <span class="detail-value">{{ $venta->nombre_representante }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Documento</span>
          <span class="detail-value">{{ strtoupper($venta->tipo_documento) }} — {{ $venta->nro_documento }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Teléfono representante</span>
          <span class="detail-value">{{ $venta->telefono_representante }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Correo</span>
          <span class="detail-value">{{ $venta->correo }}</span>
        </div>
        @if($venta->nro_sec)
        <div class="detail-row">
          <span class="detail-label">N° SEC</span>
          <span class="detail-value">{{ $venta->nro_sec }}</span>
        </div>
        @endif
      </div>
    </div>

    {{-- DATOS ESPECÍFICOS FIJA --}}
    @if($venta->tipo === 'fija')
    <div class="card">
      <div class="card-header">
        <div class="card-header-title">🏢 Datos del servicio fijo</div>
      </div>
      <div class="card-body">
        <div class="detail-row">
          <span class="detail-label">Dirección de instalación</span>
          <span class="detail-value">{{ $venta->direccion_instalacion ?? '—' }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Referencia</span>
          <span class="detail-value">{{ $venta->referencia_direccion_instalacion ?? '—' }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Dirección de facturación</span>
          <span class="detail-value">{{ $venta->direccion_facturacion_fija ?? '—' }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Teléfono SOT</span>
          <span class="detail-value">{{ $venta->telefono_sot ?? '—' }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Tecnología</span>
          <span class="detail-value">{{ strtoupper($venta->tecnologia ?? '—') }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Campaña</span>
          <span class="detail-value">{{ ucfirst(str_replace('_', ' ', $venta->campana_fija ?? '—')) }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Tipo de producto</span>
          <span class="detail-value">{{ strtoupper($venta->tipo_producto_fija ?? '—') }}</span>
        </div>

        <div class="section-title">Planes contratados</div>
        <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:16px;">
          @if($venta->plan_telefonia)     <span class="check-pill">Telefonía 5000</span> @endif
          @if($venta->plan_cable_standar) <span class="check-pill">Cable TV Estándar</span> @endif
          @if($venta->plan_cable_superior)<span class="check-pill">Cable TV Superior</span> @endif
          @if($venta->plan_internet_200)  <span class="check-pill">Internet 200MB</span> @endif
          @if($venta->plan_internet_400)  <span class="check-pill">Internet 400MB</span> @endif
          @if($venta->plan_internet_1500) <span class="check-pill">Internet 1500MB</span> @endif
        </div>

        <div class="detail-row">
          <span class="detail-label">DECOs</span>
          <span class="detail-value">{{ $venta->cantidad_decos }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Repetidores</span>
          <span class="detail-value">{{ $venta->cantidad_repetidores }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Full Claro</span>
          <span class="detail-value">{{ ucfirst($venta->full_claro ?? '—') }}
            @if($venta->nro_movil_fullclaro) — {{ $venta->nro_movil_fullclaro }} @endif
          </span>
        </div>
      </div>
    </div>
    @endif

    {{-- DATOS ESPECÍFICOS MÓVIL --}}
    @if($venta->tipo === 'movil')
    <div class="card">
      <div class="card-header">
        <div class="card-header-title">📱 Datos del servicio móvil</div>
      </div>
      <div class="card-body">
        <div class="detail-row">
          <span class="detail-label">Tipo de entrega</span>
          <span class="detail-value">{{ ucfirst(str_replace('_', ' ', $venta->tipo_entrega ?? '—')) }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Dirección de entrega</span>
          <span class="detail-value">{{ $venta->direccion_entrega ?? '—' }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Referencias entrega</span>
          <span class="detail-value">{{ $venta->referencias_entrega ?? '—' }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Dirección de facturación</span>
          <span class="detail-value">{{ $venta->direccion_facturacion_movil ?? '—' }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Teléfono biometría</span>
          <span class="detail-value">{{ $venta->telefono_biometria ?? '—' }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Campaña</span>
          <span class="detail-value">{{ ucfirst(str_replace('_', ' ', $venta->campana_movil ?? '—')) }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Fecha de despacho</span>
          <span class="detail-value">{{ $venta->fecha_despacho ? $venta->fecha_despacho->format('d/m/Y') : '—' }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Rango horario</span>
          <span class="detail-value">{{ strtoupper(str_replace('_', ' ', $venta->rango_horario ?? '—')) }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Descuento</span>
          <span class="detail-value">{{ ucfirst(str_replace('_', ' ', $venta->descuento_movil ?? '—')) }}
            @if($venta->nro_wf) — WF: {{ $venta->nro_wf }} @endif
          </span>
        </div>

        <div class="section-title">Líneas solicitadas</div>

        @if($venta->lineas->count() > 0)
        <table class="lineas-table">
          <thead>
            <tr>
              <th>N° a portar</th>
              <th>Plan</th>
              <th>Operador cedente</th>
              <th>Equipo / SIM</th>
              <th>Modelo</th>
              <th>Descuento</th>
              <th>Large</th>
            </tr>
          </thead>
          <tbody>
            @foreach($venta->lineas as $linea)
            <tr>
              <td>{{ $linea->nro_portar ?? '—' }}</td>
              <td>{{ str_replace(['max_negocios_', 'max_ilimitado_'], ['MN +', 'MI +'], $linea->plan) }}</td>
              <td>{{ ucfirst($linea->operador_cedente ?? '—') }}</td>
              <td>{{ ucfirst(str_replace('_', ' ', $linea->equipo_sim)) }}</td>
              <td>{{ $linea->modelo_equipo ?? '—' }}</td>
              <td>{{ ucfirst(str_replace('_', ' ', $linea->descuento)) }}</td>
              <td>{{ $linea->large_asociada ?? '—' }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
        @endif
      </div>
    </div>
    @endif

    {{-- DOCUMENTOS --}}
    <div class="card">
      <div class="card-header">
        <div class="card-header-title">Documentos adjuntos</div>
      </div>
      <div class="card-body">
        @forelse($venta->documentos as $doc)
          <div class="doc-item">
            <span>📄</span>
            <span>{{ $doc->nombre_original }}</span>
            <span style="color:rgba(255,255,255,0.25);font-size:11px;">{{ number_format($doc->size / 1024, 0) }} KB</span>
            <a href="{{ route('asesor.ventas.documento.download', $doc) }}"
               style="margin-left:auto;font-size:11px;color:#2FCAF5;text-decoration:none;">
              Descargar
            </a>
          </div>
        @empty
          <div style="font-size:12px;color:rgba(255,255,255,0.25);">Sin documentos adjuntos.</div>
        @endforelse
      </div>
    </div>

  </div>

  {{-- ── SIDEBAR ── --}}
  <div>

    {{-- Info de gestión de mesa --}}
    @if($venta->mesaControl || $venta->estado_contrato || $venta->fecha_instalacion || $venta->fecha_activacion)
    <div class="sidebar-card">
      <div class="sidebar-title">Gestión Mesa de Control</div>
      @if($venta->mesaControl)
      <div class="sidebar-row">
        <span class="sidebar-label">Procesado por</span>
        <span class="sidebar-value">{{ $venta->mesaControl->name }}</span>
      </div>
      @endif
      @if($venta->estado_contrato)
      <div class="sidebar-row">
        <span class="sidebar-label">Estado contrato</span>
        <span class="sidebar-value">{{ ucfirst(str_replace('_', ' ', $venta->estado_contrato)) }}</span>
      </div>
      @endif
      @if($venta->tipo === 'fija')
        @if($venta->fecha_programacion)
        <div class="sidebar-row">
          <span class="sidebar-label">Fecha programación</span>
          <span class="sidebar-value">{{ $venta->fecha_programacion->format('d/m/Y H:i') }}</span>
        </div>
        @endif
        @if($venta->fecha_instalacion)
        <div class="sidebar-row">
          <span class="sidebar-label">Fecha instalación</span>
          <span class="sidebar-value">{{ $venta->fecha_instalacion->format('d/m/Y') }}</span>
        </div>
        @endif
        @if($venta->nro_sot_fija)
        <div class="sidebar-row">
          <span class="sidebar-label">N° SOT</span>
          <span class="sidebar-value">{{ $venta->nro_sot_fija }}</span>
        </div>
        @endif
      @endif
      @if($venta->tipo === 'movil')
        @if($venta->fecha_activacion)
        <div class="sidebar-row">
          <span class="sidebar-label">Fecha activación</span>
          <span class="sidebar-value">{{ $venta->fecha_activacion->format('d/m/Y') }}</span>
        </div>
        @endif
        @if($venta->pedido_movil)
        <div class="sidebar-row">
          <span class="sidebar-label">N° Pedido</span>
          <span class="sidebar-value">{{ $venta->pedido_movil }}</span>
        </div>
        @endif
      @endif
    </div>
    @endif

    {{-- Acciones del asesor --}}
    <div class="sidebar-card">
      <div class="sidebar-title">Acciones</div>

      @if(in_array($venta->estado, ['rechazada', 'en_proceso']))
        <a href="{{ route('asesor.ventas.edit', $venta) }}"
           style="display:block;text-align:center;text-decoration:none;" class="btn-accion btn-editar">
          ✏️ Editar y reenviar
        </a>
      @endif

      @if(in_array($venta->estado, ['enviada', 'en_proceso']) && !$venta->solicitud_edicion)
        <button type="button" class="btn-accion btn-solicitar"
                onclick="document.getElementById('overlaySolicitud').classList.add('open')">
          📝 Solicitar edición
        </button>
      @endif

      @if($venta->solicitud_edicion)
        <div style="font-size:12px;color:#fac775;background:rgba(239,159,39,0.08);border:1px solid rgba(239,159,39,0.2);border-radius:8px;padding:10px 12px;">
          ⏳ Solicitud de edición pendiente de aprobación por Mesa de Control.
        </div>
      @endif

      @if($venta->estado === 'enviada' && !$venta->solicitud_edicion && !in_array($venta->estado, ['rechazada', 'en_proceso']))
        <div style="font-size:12px;color:rgba(255,255,255,0.3);text-align:center;padding:8px 0;">
          La venta está siendo revisada por Mesa de Control.
        </div>
      @endif
    </div>

    {{-- Info general --}}
    <div class="sidebar-card">
      <div class="sidebar-title">Info</div>
      <div class="sidebar-row">
        <span class="sidebar-label">Registrada</span>
        <span class="sidebar-value">{{ $venta->created_at->format('d/m/Y H:i') }}</span>
      </div>
      <div class="sidebar-row">
        <span class="sidebar-label">Última actualización</span>
        <span class="sidebar-value">{{ $venta->updated_at->format('d/m/Y H:i') }}</span>
      </div>
    </div>

  </div>

</div>

{{-- MODAL SOLICITAR EDICIÓN --}}
<div class="overlay" id="overlaySolicitud" onclick="if(event.target.id==='overlaySolicitud')this.classList.remove('open')">
  <div class="modal">
    <div class="modal-title">Solicitar edición</div>
    <div class="modal-sub">Explica por qué necesitas editar esta venta. Mesa de Control revisará tu solicitud.</div>
    <form method="POST" action="{{ route('asesor.ventas.solicitar-edicion', $venta) }}">
      @csrf
      <textarea name="motivo" class="modal-textarea"
                placeholder="Motivo de la solicitud..." required></textarea>
      <div class="modal-footer">
        <button type="button" class="btn-cancel" onclick="document.getElementById('overlaySolicitud').classList.remove('open')">Cancelar</button>
        <button type="submit" class="btn-confirm">Enviar solicitud</button>
      </div>
    </form>
  </div>
</div>

@endsection