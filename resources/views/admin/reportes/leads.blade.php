@extends('layouts.app')

@section('title', 'Reportes')
@section('subtitle', $titulo)

@section('content')
<style>
  .rep-overview {
    display: grid; grid-template-columns: repeat(7,1fr); gap: 14px; margin-bottom: 22px;
  }
  .rep-card {
    background: #15151c; border: 1px solid rgba(255,255,255,0.07);
    border-radius: 12px; padding: 16px 18px; text-align: center;
  }
  .rep-card .rep-label { font-size: 12px; color: rgba(255,255,255,0.4); margin-bottom: 6px; }
  .rep-card .rep-value { font-size: 24px; font-weight: 700; color: #fff; }
  .rep-card.warn .rep-value { color: #fac775; }
  .rep-card.good .rep-value { color: #5dcaa5; }

  .rep-table-card {
    background: #15151c; border: 1px solid rgba(255,255,255,0.07);
    border-radius: 14px; overflow: hidden; margin-bottom: 16px;
  }
  .rep-table-top {
    padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;
    border-bottom: 1px solid rgba(255,255,255,0.06);
  }
  .rep-table-top span { font-weight: 600; color: #fff; font-size: 14px; }

  table.rep-table { width: 100%; border-collapse: collapse; font-size: 13px; }
  table.rep-table th {
    text-align: left; padding: 10px 14px; font-size: 11px; text-transform: uppercase;
    letter-spacing: .04em; color: rgba(255,255,255,0.35); border-bottom: 1px solid rgba(255,255,255,0.06);
    white-space: nowrap;
  }
  table.rep-table td {
    padding: 10px 14px; color: rgba(255,255,255,0.8); border-bottom: 1px solid rgba(255,255,255,0.04);
    white-space: nowrap;
  }
  table.rep-table tr:last-child td { border-bottom: none; }
  table.rep-table td.num { text-align: center; }

  .rep-pct-bar {
    display: inline-flex; align-items: center; gap: 6px; min-width: 90px;
  }
  .rep-pct-track {
    flex: 1; height: 6px; border-radius: 3px; background: rgba(255,255,255,0.08); overflow: hidden;
  }
  .rep-pct-fill { height: 100%; background: #2FCAF5; }
  .rep-pct-fill.low { background: #ff9090; }
  .rep-pct-fill.mid { background: #fac775; }

  .rep-alert-badge {
    display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px;
    border-radius: 20px; font-size: 11px; font-weight: 600;
  }
  .rep-alert-badge.on  { background: rgba(255,144,144,0.12); color: #ff9090; }
  .rep-alert-badge.off { background: rgba(255,255,255,0.04); color: rgba(255,255,255,0.25); }

  details.rep-team { border-bottom: 1px solid rgba(255,255,255,0.06); }
  details.rep-team:last-child { border-bottom: none; }
  details.rep-team summary {
    list-style: none; cursor: pointer; padding: 12px 20px;
    display: flex; align-items: center; gap: 14px;
  }
  details.rep-team summary::-webkit-details-marker { display: none; }
  details.rep-team summary::before {
    content: '›'; font-size: 16px; font-weight: 700; color: rgba(255,255,255,0.3);
    transition: transform .15s; display: inline-block; width: 12px;
  }
  details.rep-team[open] summary::before { transform: rotate(90deg); }
  .rep-team-name { font-weight: 600; color: #fff; min-width: 160px; }
  .rep-team-mini { display: flex; gap: 22px; font-size: 12px; color: rgba(255,255,255,0.5); }
  .rep-team-mini b { color: #fff; }
  details.rep-team .rep-asesores { padding: 0 20px 14px 46px; }

  a.rep-link {
    color: #2FCAF5; text-decoration: none; font-size: 12px; font-weight: 600;
  }
  a.rep-back {
    display: inline-flex; align-items: center; gap: 6px; color: rgba(255,255,255,0.5);
    text-decoration: none; font-size: 13px; margin-bottom: 16px;
  }
  a.rep-back:hover { color: #2FCAF5; }
</style>

@if($modo === 'admin-como-jefe')
  <a href="{{ route('admin.reportes.leads') }}" class="rep-back">← Volver a todos los equipos</a>
@endif

{{-- Overview general --}}
<div class="rep-overview">
  <div class="rep-card">
    <div class="rep-label">Total leads</div>
    <div class="rep-value">{{ $overview['total'] }}</div>
  </div>
  <div class="rep-card {{ $overview['pendiente'] > 0 ? 'warn' : '' }}">
    <div class="rep-label">Sin trabajar</div>
    <div class="rep-value">{{ $overview['pendiente'] }}</div>
  </div>
  <div class="rep-card good">
    <div class="rep-label">% Trabajado</div>
    <div class="rep-value">{{ $overview['pct_trabajado'] }}%</div>
  </div>
  <div class="rep-card good">
    <div class="rep-label">% Enviadas</div>
    <div class="rep-value">{{ $overview['pct_enviadas'] }}%</div>
  </div>
  <div class="rep-card good">
    <div class="rep-label">% Completadas (venta real)</div>
    <div class="rep-value">{{ $overview['pct_completadas'] }}%</div>
  </div>
  <div class="rep-card {{ $overview['recall_vencido'] > 0 ? 'warn' : '' }}">
    <div class="rep-label">Recall vencido</div>
    <div class="rep-value">{{ $overview['recall_vencido'] }}</div>
  </div>
  <div class="rep-card {{ $overview['pendiente_antiguo'] > 0 ? 'warn' : '' }}">
    <div class="rep-label">Pendiente +7 días</div>
    <div class="rep-value">{{ $overview['pendiente_antiguo'] }}</div>
  </div>
</div>

{{-- ================= MODO ADMIN: lista de jefes ================= --}}
@if($modo === 'admin')
<div class="rep-table-card">
  <div class="rep-table-top"><span>Equipos por jefe</span></div>
  <table class="rep-table">
    <thead><tr>
      @include('admin.reportes._fila_headers')
      <th></th>
    </tr></thead>
    <tbody>
      @forelse($filas as $f)
        @php $link = '<a href="' . route('admin.reportes.leads', ['jefe_id' => $f['id']]) . '" class="rep-link">Ver equipo →</a>'; @endphp
        @include('admin.reportes._fila', ['f' => $f, 'extra' => $link])
      @empty
        <tr><td colspan="12" style="text-align:center;color:rgba(255,255,255,0.35);padding:30px;">No hay jefes registrados.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@endif

{{-- ================= MODO JEFE (propio o admin viendo un jefe) ================= --}}
@if($modo === 'jefe' || $modo === 'admin-como-jefe')

<div class="rep-table-card">
  <div class="rep-table-top"><span>Tu bandeja (sin delegar aún)</span></div>
  <table class="rep-table">
    <thead><tr>@include('admin.reportes._fila_headers')</tr></thead>
    <tbody>@include('admin.reportes._fila', ['f' => $bandejaJefe])</tbody>
  </table>
</div>

<div class="rep-table-card">
  <div class="rep-table-top"><span>Equipos (por supervisor)</span></div>
  @forelse($equipos as $eq)
    <details class="rep-team">
      <summary>
        <span class="rep-team-name">{{ $eq['supervisor']['nombre'] }}</span>
        <span class="rep-team-mini">
          <span>Total: <b>{{ $eq['supervisor']['total'] }}</b></span>
          <span>Sin trabajar: <b>{{ $eq['supervisor']['pendiente'] }}</b></span>
          <span>% Trabajado: <b>{{ $eq['supervisor']['pct_trabajado'] }}%</b></span>
          <span>Completadas: <b>{{ $eq['supervisor']['pct_completadas'] }}%</b></span>
          @if($eq['supervisor']['recall_vencido'] + $eq['supervisor']['pendiente_antiguo'] > 0)
            <span class="rep-alert-badge on">⚠ {{ $eq['supervisor']['recall_vencido'] + $eq['supervisor']['pendiente_antiguo'] }}</span>
          @endif
        </span>
      </summary>
      <div class="rep-asesores">
        <table class="rep-table">
          <thead><tr>@include('admin.reportes._fila_headers')</tr></thead>
          <tbody>
            @forelse($eq['asesores'] as $a)
              @include('admin.reportes._fila', ['f' => $a])
            @empty
              <tr><td colspan="11" style="color:rgba(255,255,255,0.3);">Este supervisor aún no tiene asesores.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </details>
  @empty
    <div style="padding:30px;text-align:center;color:rgba(255,255,255,0.35);">Aún no tienes supervisores asignados.</div>
  @endforelse
</div>
@endif

{{-- ================= MODO SUPERVISOR ================= --}}
@if($modo === 'supervisor')

<div class="rep-table-card">
  <div class="rep-table-top"><span>Tu bandeja (sin delegar aún)</span></div>
  <table class="rep-table">
    <thead><tr>@include('admin.reportes._fila_headers')</tr></thead>
    <tbody>@include('admin.reportes._fila', ['f' => $filaSupervisor])</tbody>
  </table>
</div>

<div class="rep-table-card">
  <div class="rep-table-top"><span>Tus asesores</span></div>
  <table class="rep-table">
    <thead><tr>@include('admin.reportes._fila_headers')</tr></thead>
    <tbody>
      @forelse($asesores as $a)
        @include('admin.reportes._fila', ['f' => $a])
      @empty
        <tr><td colspan="11" style="text-align:center;color:rgba(255,255,255,0.35);padding:30px;">Aún no tienes asesores asignados.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@endif

@endsection