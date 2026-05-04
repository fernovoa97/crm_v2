@extends('layouts.app')

@section('title', 'Ventas')
@section('subtitle', 'Gestiona y procesa las ventas recibidas')

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

  .filter-bar {
    display: flex;
    gap: 8px;
    margin-bottom: 16px;
    flex-wrap: wrap;
  }

  .filter-btn {
    padding: 6px 14px;
    border-radius: 20px;
    border: 1px solid rgba(255,255,255,0.1);
    background: none;
    color: rgba(255,255,255,0.4);
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    font-family: 'Sora', sans-serif;
    transition: all .15s;
  }

  .filter-btn:hover { border-color: rgba(255,255,255,0.2); color: #fff; }
  .filter-btn.active { border-color: #2FCAF5; background: rgba(47,202,245,0.12); color: #2FCAF5; }

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

  .search-input {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 8px;
    padding: 7px 12px;
    font-size: 13px;
    color: #fff;
    font-family: 'Sora', sans-serif;
    outline: none;
    width: 220px;
  }

  .search-input::placeholder { color: rgba(255,255,255,0.25); }
  .search-input:focus { border-color: rgba(47,202,245,0.4); }

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

  .btn-procesar {
    padding: 4px 12px; border-radius: 6px;
    font-size: 11px; font-weight: 600;
    background: rgba(29,158,117,0.1);
    color: #5dcaa5;
    border: 1px solid rgba(29,158,117,0.25);
    cursor: pointer;
    font-family: 'Sora', sans-serif;
    text-decoration: none;
    transition: opacity 0.2s;
    white-space: nowrap;
  }

  .btn-procesar:hover { opacity: 0.75; }

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
  html.light .search-input { background: #f0f7ff; border-color: #c0dff5; color: #0f0f13; }
  html.light .filter-btn { border-color: #d0eaf8; color: rgba(0,0,0,0.4); }
</style>

@php
  $total      = $ventas->total();
  $enviadas   = $ventas->getCollection()->where('estado', 'enviada')->count();
  $enProceso  = $ventas->getCollection()->where('estado', 'en_proceso')->count();
  $completadas = $ventas->getCollection()->where('estado', 'completada')->count();
@endphp

@if(session('success'))
  <div style="background:rgba(29,158,117,0.12);border:1px solid rgba(29,158,117,0.25);color:#5dcaa5;border-radius:10px;padding:10px 14px;font-size:12px;margin-bottom:18px;display:flex;align-items:center;gap:10px;">
    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="#5dcaa5" stroke-width="1.5"><circle cx="8" cy="8" r="6"/><path d="M5 8l2 2 4-4"/></svg>
    {{ session('success') }}
  </div>
@endif

{{-- Stats --}}
<div class="stats-row">
  <div class="stat-card">
    <div class="stat-label">Total</div>
    <div class="stat-value">{{ $total }}</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Pendientes</div>
    <div class="stat-value" style="color:#2FCAF5;">{{ $enviadas }}</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">En proceso</div>
    <div class="stat-value" style="color:#fac775;">{{ $enProceso }}</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Completadas</div>
    <div class="stat-value" style="color:#5dcaa5;">{{ $completadas }}</div>
  </div>
</div>

{{-- Filtros --}}
<div class="filter-bar">
  <a href="{{ route('mesa.ventas.index') }}" class="filter-btn {{ !request('estado') ? 'active' : '' }}">Todas</a>
  <a href="{{ route('mesa.ventas.index', ['estado' => 'enviada']) }}" class="filter-btn {{ request('estado') === 'enviada' ? 'active' : '' }}">Enviadas</a>
  <a href="{{ route('mesa.ventas.index', ['estado' => 'en_proceso']) }}" class="filter-btn {{ request('estado') === 'en_proceso' ? 'active' : '' }}">En proceso</a>
  <a href="{{ route('mesa.ventas.index', ['estado' => 'completada']) }}" class="filter-btn {{ request('estado') === 'completada' ? 'active' : '' }}">Completadas</a>
  <a href="{{ route('mesa.ventas.index', ['estado' => 'rechazada']) }}" class="filter-btn {{ request('estado') === 'rechazada' ? 'active' : '' }}">Rechazadas</a>
  <a href="{{ route('mesa.ventas.index', ['solicitud' => '1']) }}" class="filter-btn {{ request('solicitud') ? 'active' : '' }}" style="{{ request('solicitud') ? 'border-color:#fac775;background:rgba(239,159,39,0.12);color:#fac775;' : '' }}">
    ⚠️ Solicitudes edición
  </a>
</div>

{{-- Tabla --}}
<div class="table-card">
  <div class="table-top">
    <span>Ventas recibidas</span>
    <input class="search-input" type="text" placeholder="Buscar RUC o empresa..." oninput="filterTable(this.value)">
  </div>

  <table id="ventasTable">
    <thead>
      <tr>
        <th>Empresa</th>
        <th>Asesor</th>
        <th>Tipo</th>
        <th>Tipo de venta</th>
        <th>Estado</th>
        <th>Fecha</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      @forelse($ventas as $venta)
      <tr onclick="window.location='{{ route('mesa.ventas.show', $venta) }}'">
        <td>
          <div style="font-weight:500;color:#fff;">{{ $venta->razon_social }}</div>
          <div style="font-size:11px;color:rgba(255,255,255,0.35);font-family:monospace;">{{ $venta->ruc }}</div>
        </td>
        <td style="font-size:12px;">{{ $venta->asesor?->name ?? '—' }}</td>
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
              @case('enviada') ✉️ Enviada @break
              @case('en_proceso') ⚙️ En proceso @break
              @case('completada') ✅ Completada @break
              @case('rechazada') ❌ Rechazada @break
            @endswitch
          </span>
          @if($venta->solicitud_edicion)
            <span class="solicitud-badge">⚠️ Edición solicitada</span>
          @endif
        </td>
        <td style="font-size:12px;color:rgba(255,255,255,0.4);">
          {{ $venta->created_at->format('d/m/Y') }}
        </td>
        <td onclick="event.stopPropagation()">
          <a href="{{ route('mesa.ventas.show', $venta) }}" class="btn-procesar">
            {{ in_array($venta->estado, ['enviada']) ? 'Procesar' : 'Ver / Editar' }}
          </a>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="7">
          <div class="empty-state">No hay ventas disponibles.</div>
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

<script>
function filterTable(q) {
  q = q.toLowerCase();
  document.querySelectorAll('#ventasTable tbody tr').forEach(row => {
    row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
}
</script>
@endsection