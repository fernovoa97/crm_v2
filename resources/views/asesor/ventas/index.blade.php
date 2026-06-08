@extends('layouts.app')

@section('title', 'Mis Ventas')
@section('subtitle', 'Historial y estado de tus ventas enviadas')

@section('topbar-actions')
  <a href="{{ route('asesor.leads.index') }}" style="
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(255,255,255,0.05); color: rgba(255,255,255,0.5);
    border: 1px solid rgba(255,255,255,0.08);
    padding: 8px 16px; border-radius: 8px;
    font-size: 13px; font-weight: 600;
    text-decoration: none;
  ">
    ← Mis leads
  </a>
@endsection

@section('content')
<style>
  .stats-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 24px;
  }

  .stat-card {
    background: #15151c;
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 12px;
    padding: 16px 18px;
  }

  .stat-label { font-size: 11px; color: rgba(255,255,255,0.35); margin-bottom: 8px; font-weight: 500; }
  .stat-value { font-size: 24px; font-weight: 600; color: #fff; }

  .table-card {
    background: #15151c;
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 14px;
    overflow: hidden;
  }

  .table-top {
    padding: 16px 20px;
    border-bottom: 1px solid rgba(255,255,255,0.07);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
  }

  .table-top span { font-size: 14px; font-weight: 600; color: #fff; }

  table { width: 100%; border-collapse: collapse; }

  th {
    font-size: 11px; font-weight: 600;
    color: rgba(255,255,255,0.25);
    text-transform: uppercase;
    letter-spacing: 0.6px;
    padding: 12px 16px;
    text-align: left;
    border-bottom: 1px solid rgba(255,255,255,0.05);
    white-space: nowrap;
  }

  td {
    font-size: 13px;
    color: rgba(255,255,255,0.75);
    padding: 12px 16px;
    border-bottom: 1px solid rgba(255,255,255,0.04);
    vertical-align: middle;
  }

  tr:last-child td { border-bottom: none; }
  tbody tr { transition: background 0.15s; cursor: pointer; }
  tbody tr:hover { background: rgba(255,255,255,0.02); }

  .estado-pill {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 11px; padding: 3px 10px;
    border-radius: 20px; font-weight: 600;
  }

  .estado-borrador    { background: rgba(255,255,255,0.07);   color: rgba(255,255,255,0.4); }
  .estado-enviada     { background: rgba(47,202,245,0.12);    color: #2FCAF5; }
  .estado-en_proceso  { background: rgba(239,159,39,0.12);    color: #fac775; }
  .estado-completada  { background: rgba(29,158,117,0.12);    color: #5dcaa5; }
  .estado-rechazada   { background: rgba(255,80,80,0.12);     color: #ff9090; }

  .tipo-pill {
    display: inline-block; font-size: 11px;
    padding: 3px 10px; border-radius: 20px; font-weight: 500;
  }

  .tipo-movil { background: rgba(127,119,221,0.15); color: #afa9ec; }
  .tipo-fija  { background: rgba(239,159,39,0.12);  color: #fac775; }

  .solicitud-badge {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 10px; padding: 2px 8px;
    border-radius: 20px; font-weight: 600;
    background: rgba(239,159,39,0.15); color: #fac775;
  }

  .btn-ver {
    padding: 4px 12px; border-radius: 6px;
    font-size: 11px; font-weight: 600;
    background: rgba(47,202,245,0.08);
    color: #2FCAF5;
    border: 1px solid rgba(47,202,245,0.2);
    cursor: pointer;
    font-family: 'Sora', sans-serif;
    text-decoration: none;
    transition: opacity 0.2s;
  }

  .btn-ver:hover { opacity: 0.75; }

  .empty-state {
    text-align: center; padding: 60px 20px;
    color: rgba(255,255,255,0.25); font-size: 14px;
  }

  .pagination-wrap {
    padding: 16px 20px; border-top: 1px solid rgba(255,255,255,0.05);
    display: flex; justify-content: space-between; align-items: center;
    font-size: 12px; color: rgba(255,255,255,0.35);
  }

  /* Light mode */
  html.light .stat-card { background: #fff; border-color: #d0eaf8; }
  html.light .stat-label { color: rgba(0,0,0,0.4); }
  html.light .stat-value { color: #0f0f13; }
  html.light .table-card { background: #fff; border-color: #d0eaf8; }
  html.light .table-top span { color: #0f0f13; }
  html.light th { color: rgba(0,0,0,0.35); border-bottom-color: #e8f3fb; }
  html.light td { color: rgba(0,0,0,0.7); border-bottom-color: #f0f7ff; }

  .btn-crear-venta {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: rgba(29,158,117,0.12);
  color: #5dcaa5;
  border: 1px solid rgba(29,158,117,0.25);
  padding: 9px 16px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 700;
  text-decoration: none;
  transition: all .2s;
  font-family: 'Sora', sans-serif;
}

.btn-crear-venta:hover {
  background: rgba(29,158,117,0.18);
  transform: translateY(-1px);
}

/* Light mode */
html.light .btn-crear-venta {
  background: rgba(29,158,117,0.08);
  border-color: rgba(29,158,117,0.2);
}
</style>

@php
  $total       = $ventas->total();
  // Conteos sobre el total real (vienen del controller), no sobre la página actual
  $enviadas    = $counts['enviada']    ?? 0;
  $enProceso   = $counts['en_proceso'] ?? 0;
  $completadas = $counts['completada'] ?? 0;
  $rechazadas  = $counts['rechazada']  ?? 0;
@endphp

{{-- Notificaciones --}}
@if(session('success'))
  <div style="background:rgba(29,158,117,0.12);border:1px solid rgba(29,158,117,0.25);color:#5dcaa5;border-radius:10px;padding:10px 14px;font-size:12px;margin-bottom:18px;display:flex;align-items:center;gap:10px;">
    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="#5dcaa5" stroke-width="1.5"><circle cx="8" cy="8" r="6"/><path d="M5 8l2 2 4-4"/></svg>
    {{ session('success') }}
  </div>
@endif

@if(session('error'))
  <div style="background:rgba(255,80,80,0.12);border:1px solid rgba(255,80,80,0.25);color:#ff9090;border-radius:10px;padding:10px 14px;font-size:12px;margin-bottom:18px;">
    {{ session('error') }}
  </div>
@endif

{{-- Stats --}}
<div class="stats-row">
  <div class="stat-card">
    <div class="stat-label">Total ventas</div>
    <div class="stat-value">{{ $total }}</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Enviadas</div>
    <div class="stat-value" style="color:#2FCAF5;">{{ $enviadas }}</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Completadas</div>
    <div class="stat-value" style="color:#5dcaa5;">{{ $completadas }}</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Rechazadas</div>
    <div class="stat-value" style="color:#ff9090;">{{ $rechazadas }}</div>
  </div>
</div>

{{-- Tabla --}}
<div class="table-card">
  <div class="table-top">
  <span>Mis ventas</span>

  <a href="{{ route('asesor.ventas.create-directo') }}" class="btn-crear-venta">
    + Crear venta desde cero
  </a>
</div>

  <table>
    <thead>
      <tr>
        <th>Empresa</th>
        <th>Tipo</th>
        <th>Tipo de venta</th>
        <th>Estado</th>
        <th>Mesa de control</th>
        <th>Fecha</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      @forelse($ventas as $venta)
      <tr onclick="window.location='{{ route('asesor.ventas.show', $venta) }}'">
        <td>
          <div style="font-weight:500;color:#fff;">{{ $venta->razon_social }}</div>
          <div style="font-size:11px;color:rgba(255,255,255,0.35);font-family:monospace;">{{ $venta->ruc }}</div>
        </td>
        <td>
          <span class="tipo-pill tipo-{{ $venta->tipo }}">
            {{ $venta->tipo === 'movil' ? '📱 Móvil' : '🏢 Fija' }}
          </span>
        </td>
        <td style="font-size:12px;">
          {{ $venta->tipo === 'movil'
            ? ucfirst(str_replace('_', ' ', $venta->tipo_venta_movil ?? '—'))
            : ucfirst($venta->tipo_venta_fija ?? '—') }}
        </td>
        <td>
          <span class="estado-pill estado-{{ $venta->estado }}">
            @switch($venta->estado)
              @case('borrador') Borrador @break
              @case('enviada') Enviada @break
              @case('en_proceso') En proceso @break
              @case('completada') Completada @break
              @case('rechazada') Rechazada @break
            @endswitch
          </span>
          @if($venta->solicitud_edicion)
            <span class="solicitud-badge">⏳ Edición solicitada</span>
          @endif
        </td>
        <td style="font-size:12px;">
          {{ $venta->mesaControl?->name ?? '—' }}
        </td>
        <td style="font-size:12px;color:rgba(255,255,255,0.4);">
          {{ $venta->created_at->format('d/m/Y') }}
        </td>
        <td onclick="event.stopPropagation()">
          <a href="{{ route('asesor.ventas.show', $venta) }}" class="btn-ver">Ver</a>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="7">
          <div class="empty-state">No tienes ventas registradas aún.</div>
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>

  <div class="pagination-wrap">
    <span>Mostrando {{ $ventas->firstItem() ?? 0 }}–{{ $ventas->lastItem() ?? 0 }} de {{ $ventas->total() }}</span>
    {{ $ventas->links() }}
  </div>
</div>
@endsection