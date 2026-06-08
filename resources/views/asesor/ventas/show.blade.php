@extends('layouts.app')

@section('title', 'Procesar Venta')
@section('subtitle'){{ $venta->razon_social }} — {{ $venta->created_at->format('d/m/Y') }}@endsection

@section('topbar-actions')
  <a href="{{ route('mesa.ventas.index') }}" style="
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
    grid-template-columns: 1fr 320px;
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
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 10px 0;
    border-bottom: 1px solid rgba(255,255,255,0.04);
    gap: 16px;
  }

  .detail-row:last-child { border-bottom: none; }
  .detail-label { font-size: 12px; color: rgba(255,255,255,0.35); min-width: 200px; }
  .detail-value { font-size: 12px; color: #fff; font-weight: 500; text-align: right; }

  .section-title {
    font-size: 11px; font-weight: 700;
    text-transform: uppercase; letter-spacing: .6px;
    color: rgba(255,255,255,0.25);
    border-top: 1px solid rgba(255,255,255,0.06);
    padding-top: 14px; margin: 16px 0 12px;
  }

  /* Form fields */
  .form-group { margin-bottom: 14px; }
  .form-label {
    font-size: 11px; color: rgba(255,255,255,0.4);
    font-weight: 600; text-transform: uppercase;
    letter-spacing: .4px; display: block; margin-bottom: 6px;
  }
  .form-label .mesa-badge {
    font-size: 10px; padding: 1px 7px; border-radius: 10px;
    background: rgba(29,158,117,0.15); color: #5dcaa5;
    text-transform: none; letter-spacing: 0; font-weight: 500;
    margin-left: 6px;
  }

  .form-input {
    width: 100%; box-sizing: border-box;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 8px; padding: 9px 12px;
    font-size: 13px; color: #fff;
    font-family: 'Sora', sans-serif; outline: none;
    transition: border .2s;
  }

  .form-input:focus { border-color: rgba(47,202,245,0.4); }
  .form-input::placeholder { color: rgba(255,255,255,0.2); }

  .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

  /* Bubble selectors */
  .bubble-group { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 6px; }

  .bubble {
    padding: 6px 14px; border-radius: 20px;
    border: 1px solid rgba(255,255,255,0.1);
    background: rgba(255,255,255,0.03);
    color: rgba(255,255,255,0.5);
    font-size: 12px; font-weight: 500;
    cursor: pointer; transition: all .15s;
    font-family: 'Sora', sans-serif;
  }

  .bubble:hover { border-color: rgba(47,202,245,0.3); color: #fff; }
  .bubble.active { border-color: #2FCAF5; background: rgba(47,202,245,0.12); color: #2FCAF5; }

  /* Estado contrato */
  .bubble.active-green { border-color: #5dcaa5; background: rgba(29,158,117,0.12); color: #5dcaa5; }
  .bubble.active-red   { border-color: #ff9090; background: rgba(255,80,80,0.12);  color: #ff9090; }
  .bubble.active-orange { border-color: #fac775; background: rgba(239,159,39,0.12); color: #fac775; }

  /* Tabla líneas */
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

  /* Check pills */
  .check-pill {
    display: inline-block; font-size: 11px;
    padding: 2px 8px; border-radius: 20px; font-weight: 500;
    background: rgba(47,202,245,0.12); color: #2FCAF5;
  }

  /* Estado pill */
  .estado-pill {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 12px; padding: 4px 12px;
    border-radius: 20px; font-weight: 600;
  }
  .estado-enviada     { background: rgba(47,202,245,0.12);    color: #2FCAF5; }
  .estado-en_proceso  { background: rgba(239,159,39,0.12);    color: #fac775; }
  .estado-completada  { background: rgba(29,158,117,0.12);    color: #5dcaa5; }
  .estado-rechazada   { background: rgba(255,80,80,0.12);     color: #ff9090; }

  /* Documentos */
  .doc-item {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 14px;
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 8px; margin-bottom: 8px;
    font-size: 12px; color: rgba(255,255,255,0.6);
  }

  /* Sidebar */
  .sidebar-card {
    background: #15151c;
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 14px; padding: 18px; margin-bottom: 14px;
    position: sticky; top: 20px;
  }

  .sidebar-title {
    font-size: 11px; font-weight: 700;
    text-transform: uppercase; letter-spacing: .6px;
    color: rgba(255,255,255,0.25); margin-bottom: 14px;
  }

  .sidebar-row {
    display: flex; justify-content: space-between;
    align-items: flex-start; gap: 8px;
    margin-bottom: 10px; font-size: 12px;
  }

  .sidebar-label { color: rgba(255,255,255,0.35); }
  .sidebar-value { color: #fff; font-weight: 500; text-align: right; }

  /* Solicitud edición */
  .solicitud-box {
    background: rgba(239,159,39,0.08);
    border: 1px solid rgba(239,159,39,0.25);
    border-radius: 10px; padding: 14px 16px; margin-bottom: 16px;
  }

  .btn-submit {
    width: 100%; padding: 12px; border-radius: 10px;
    background: #5dcaa5; color: #0f0f13;
    border: none; font-size: 14px; font-weight: 700;
    cursor: pointer; font-family: 'Sora', sans-serif;
    transition: opacity .2s; margin-bottom: 8px;
  }

  .btn-submit:hover { opacity: .88; }

  .btn-rechazar {
    width: 100%; padding: 10px; border-radius: 10px;
    background: rgba(255,80,80,0.08); color: #ff9090;
    border: 1px solid rgba(255,80,80,0.2);
    font-size: 13px; font-weight: 600;
    cursor: pointer; font-family: 'Sora', sans-serif;
    transition: all .2s;
  }

  .btn-rechazar:hover { background: rgba(255,80,80,0.15); }

  /* Modal rechazo */
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
  .btn-confirm-red {
    padding: 8px 20px; border-radius: 8px;
    background: #ff9090; color: #0f0f13;
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
  html.light .form-label { color: rgba(0,0,0,0.5); }
  html.light .form-input { background: #f0f7ff; border-color: #c0dff5; color: #0f0f13; }
  html.light .bubble { border-color: #d0eaf8; color: rgba(0,0,0,0.5); }
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

{{-- Solicitud de edición --}}
@if($venta->solicitud_edicion)
  <div class="solicitud-box">
    <div style="font-size:13px;font-weight:600;color:#fac775;margin-bottom:6px;">⚠️ El asesor solicita editar esta venta</div>
    @if($venta->solicitud_edicion_motivo)
      <div style="font-size:12px;color:rgba(255,255,255,0.6);margin-bottom:12px;">{{ $venta->solicitud_edicion_motivo }}</div>
    @endif
    <div style="display:flex;gap:8px;">
      <form method="POST" action="{{ route('mesa.ventas.aprobar-edicion', $venta) }}">
        @csrf @method('PATCH')
        <button type="submit" style="padding:6px 14px;border-radius:8px;background:rgba(29,158,117,0.12);color:#5dcaa5;border:1px solid rgba(29,158,117,0.25);font-size:12px;font-weight:600;cursor:pointer;font-family:'Sora',sans-serif;">
          ✓ Aprobar edición
        </button>
      </form>
      <form method="POST" action="{{ route('mesa.ventas.rechazar-edicion', $venta) }}">
        @csrf @method('PATCH')
        <button type="submit" style="padding:6px 14px;border-radius:8px;background:rgba(255,80,80,0.08);color:#ff9090;border:1px solid rgba(255,80,80,0.2);font-size:12px;font-weight:600;cursor:pointer;font-family:'Sora',sans-serif;">
          ✗ Rechazar solicitud
        </button>
      </form>
    </div>
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
          <span class="detail-label">Asesor</span>
          <span class="detail-value">{{ $venta->asesor?->name ?? '—' }}</span>
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
          @if($venta->plan_telefonia)    <span class="check-pill">Telefonía 5000</span> @endif
          @if($venta->plan_cable_standar) <span class="check-pill">Cable TV Estándar</span> @endif
          @if($venta->plan_cable_superior) <span class="check-pill">Cable TV Superior</span> @endif
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
            <a href="{{ route('admin.ventas.documento.download', $doc) }}"
               style="margin-left:auto;font-size:11px;color:#2FCAF5;text-decoration:none;">
              Descargar
            </a>
          </div>
        @empty
          <div style="font-size:12px;color:rgba(255,255,255,0.25);margin-bottom:12px;">Sin documentos adjuntos.</div>
        @endforelse

        <div style="margin-top:12px;">
          <label class="form-label">Agregar documentos</label>
          <input type="file" name="documentos[]" multiple
                 accept=".pdf,.xlsx,.xls,.csv,.jpg,.jpeg,.png,.doc,.docx"
                 style="font-size:12px;color:rgba(255,255,255,0.5);">
        </div>
      </div>
    </div>

  </div>

  {{-- ── SIDEBAR ── --}}
  <div>
    <div class="sidebar-card">
      <div class="sidebar-title">Estado de contrato</div>
      <div class="form-group">
        <div class="bubble-group">
          <div class="bubble {{ $venta->estado_contrato === 'pendiente_loteo' ? 'active-orange' : '' }}"
               onclick="setBubble(this, 'estado_contrato', 'pendiente_loteo')">
            Pendiente loteo
          </div>
          <div class="bubble {{ $venta->estado_contrato === 'pendiente_sigex' ? 'active-orange' : '' }}"
               onclick="setBubble(this, 'estado_contrato', 'pendiente_sigex')">
            Pendiente SIGEX
          </div>
          <div class="bubble {{ $venta->estado_contrato === 'conforme' ? 'active-green' : '' }}"
               onclick="setBubble(this, 'estado_contrato', 'conforme')">
            Conforme
          </div>
          <div class="bubble {{ $venta->estado_contrato === 'no_conforme' ? 'active-red' : '' }}"
               onclick="setBubble(this, 'estado_contrato', 'no_conforme')">
            No conforme
          </div>
        </div>
        <input type="hidden" name="estado_contrato" id="inputEstadoContrato"
               value="{{ $venta->estado_contrato }}">
      </div>
    </div>

    <div class="sidebar-card">
      <div class="sidebar-title">Acciones</div>
      <button type="submit" name="estado" value="en_proceso" class="btn-submit"
              style="background:rgba(239,159,39,0.15);color:#fac775;margin-bottom:8px;">
        ⚙️ Marcar en proceso
      </button>
      <button type="submit" name="estado" value="completada" class="btn-submit">
        ✅ Marcar completada
      </button>
      <button type="button" class="btn-rechazar"
              onclick="document.getElementById('overlayRechazo').classList.add('open')">
        ❌ Rechazar venta
      </button>
    </div>

    <div class="sidebar-card">
      <div class="sidebar-title">Info</div>
      <div class="sidebar-row">
        <span class="sidebar-label">Asesor</span>
        <span class="sidebar-value">{{ $venta->asesor?->name ?? '—' }}</span>
      </div>
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
</form>

{{-- MODAL RECHAZO --}}
<div class="overlay" id="overlayRechazo" onclick="if(event.target.id==='overlayRechazo')this.classList.remove('open')">
  <div class="modal">
    <div class="modal-title">Rechazar venta</div>
    <div class="modal-sub">Explica el motivo del rechazo. El asesor podrá verlo.</div>
    <form method="POST" action="{{ route('mesa.ventas.update', $venta) }}">
      @csrf @method('PATCH')
      <input type="hidden" name="estado" value="rechazada">
      <textarea name="motivo_rechazo" class="modal-textarea"
                placeholder="Motivo del rechazo..." required>{{ $venta->motivo_rechazo }}</textarea>
      <div class="modal-footer">
        <button type="button" class="btn-cancel" onclick="document.getElementById('overlayRechazo').classList.remove('open')">Cancelar</button>
        <button type="submit" class="btn-confirm-red">Rechazar</button>
      </div>
    </form>
  </div>
</div>

<script>
function setBubble(el, name, value) {
  el.closest('.bubble-group').querySelectorAll('.bubble').forEach(b => {
    b.classList.remove('active', 'active-green', 'active-red', 'active-orange');
  });
  // Color según valor
  if (value === 'conforme') el.classList.add('active-green');
  else if (value === 'no_conforme') el.classList.add('active-red');
  else el.classList.add('active-orange');

  document.getElementById('inputEstadoContrato').value = value;
}
</script>
@endsection