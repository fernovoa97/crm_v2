@extends('layouts.app')

@section('title', 'Mis Leads')
@section('subtitle', 'Gestiona y tipifica tus leads asignados')

@section('content')
<style>
  /* ── STATS ── */
  .stats-row {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 10px;
    margin-bottom: 22px;
  }
  .stat-card {
    background: #15151c;
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 12px;
    padding: 14px 16px;
  }
  .stat-label { font-size: 10px; color: rgba(255,255,255,0.35); font-weight: 600; text-transform: uppercase; letter-spacing: .6px; margin-bottom: 6px; }
  .stat-value { font-size: 22px; font-weight: 600; color: #fff; }
  .stat-sub   { font-size: 11px; margin-top: 4px; }

  /* ── TABS ── */
  .tabs-bar {
    display: flex;
    gap: 4px;
    border-bottom: 1px solid rgba(255,255,255,0.07);
    margin-bottom: 18px;
  }
  .tab-btn {
    padding: 9px 16px;
    border: none;
    background: none;
    color: rgba(255,255,255,0.35);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    font-family: 'Sora', sans-serif;
    border-bottom: 2px solid transparent;
    margin-bottom: -1px;
    transition: all .18s;
    display: flex;
    align-items: center;
    gap: 7px;
  }
  .tab-btn:hover { color: rgba(255,255,255,0.85); }
  .tab-btn.active { color: #2FCAF5; border-bottom-color: #2FCAF5; }

  .tab-count {
    font-size: 10px;
    font-weight: 600;
    padding: 1px 7px;
    border-radius: 20px;
  }
  .tc-cyan   { background: rgba(47,202,245,0.15);  color: #2FCAF5; }
  .tc-orange { background: rgba(239,159,39,0.15);  color: #fac775; }
  .tc-gray   { background: rgba(136,135,128,0.15); color: #b4b2a9; }
  .tc-green  { background: rgba(29,158,117,0.15);  color: #5dcaa5; }
  .tc-red    { background: rgba(255,80,80,0.12);   color: #ff9090; }

  /* ── PANES ── */
  .tab-pane { display: none; }
  .tab-pane.active { display: block; }

  /* ── TABLE CARD ── */
  .table-card {
    background: #15151c;
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 14px;
    overflow: hidden;
  }
  .table-top {
    padding: 14px 18px;
    border-bottom: 1px solid rgba(255,255,255,0.07);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
  }
  .table-top-title { font-size: 13px; font-weight: 600; color: #fff; }
  .search-input {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 8px;
    padding: 6px 11px;
    font-size: 12px;
    color: #fff;
    font-family: 'Sora', sans-serif;
    outline: none;
    width: 200px;
    transition: border .2s;
  }
  .search-input::placeholder { color: rgba(255,255,255,0.25); }
  .search-input:focus { border-color: rgba(47,202,245,0.4); }

  table { width: 100%; border-collapse: collapse; }
  th {
    font-size: 10px; font-weight: 600;
    color: rgba(255,255,255,0.25);
    text-transform: uppercase;
    letter-spacing: .6px;
    padding: 11px 16px;
    text-align: left;
    border-bottom: 1px solid rgba(255,255,255,0.05);
  }
  td {
    font-size: 12px;
    color: rgba(255,255,255,0.75);
    padding: 12px 16px;
    border-bottom: 1px solid rgba(255,255,255,0.04);
    vertical-align: middle;
  }
  tr:last-child td { border-bottom: none; }
  tbody tr { transition: background .12s; cursor: pointer; }
  tbody tr:hover { background: rgba(255,255,255,0.02); }

  /* ── CELL COMPONENTS ── */
  .ruc-cell .ruc   { font-weight: 600; color: #fff; font-size: 12px; }
  .ruc-cell .razon { font-size: 12px; color: rgb(252, 251, 251); margin-top: 2px; }

  .telf-list { font-size: 11px; line-height: 1.7; }
  .telf-list span { display: block; }

  .ops { display: flex; gap: 4px; flex-wrap: wrap; }
  .op { font-size: 10px; padding: 2px 7px; border-radius: 5px; font-weight: 600; }
  .op-en { background: rgba(0,130,255,.12);  color: #2169bb; }
  .op-mv { background: rgba(255,100,0,.10);  color: #2FCAF5; }
  .op-cl { background: rgba(220,0,0,.10);    color: #ff8080; }
  .op-bt { background: rgba(100,200,100,.1); color: #e3e768; }

  .tip-badge {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 10px; padding: 3px 9px; border-radius: 20px; font-weight: 600;
  }
  .tip-pendiente       { background: rgba(47,202,245,0.1);   color: #2FCAF5; }
  .tip-volver_llamar   { background: rgba(239,159,39,0.12);  color: #fac775; }
  .tip-no_interesado   { background: rgba(136,135,128,0.12); color: #b4b2a9; }
  .tip-no_califica     { background: rgba(255,80,80,0.1);    color: #ff9090; }
  .tip-lista_negra     { background: rgba(80,0,0,0.2);       color: #ff5555; }
  .tip-prospecto       { background: rgba(29,158,117,0.12);  color: #5dcaa5; }
  .tip-numero_equivocado { background: rgba(127,119,221,0.12); color: #afa9ec; }

  /* recall badge */
  .recall-badge {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 11px; color: #fac775;
    background: rgba(239,159,39,0.1);
    border-radius: 7px; padding: 4px 9px;
  }
  .recall-now { color: #ff9090; background: rgba(255,80,80,0.1); }

  /* timer bar */
  .timer-wrap { font-size: 11px; color: rgba(255,255,255,0.35); }
  .timer-bar-bg { height: 4px; background: rgba(255,255,255,0.06); border-radius: 2px; margin-top: 5px; width: 100px; }
  .timer-bar-fill { height: 4px; border-radius: 2px; }

  /* action buttons */
  .btn-tip {
    padding: 5px 13px;
    border-radius: 7px;
    border: 1px solid rgba(47,202,245,0.25);
    background: rgba(47,202,245,0.08);
    color: #2FCAF5;
    font-size: 11px; font-weight: 600;
    cursor: pointer;
    font-family: 'Sora', sans-serif;
    transition: opacity .15s;
    white-space: nowrap;
  }
  .btn-tip:hover { opacity: .75; }

  .btn-venta {
    padding: 5px 12px;
    border-radius: 7px;
    border: 1px solid rgba(29,158,117,0.3);
    background: rgba(29,158,117,0.1);
    color: #5dcaa5;
    font-size: 11px; font-weight: 600;
    cursor: pointer;
    font-family: 'Sora', sans-serif;
    white-space: nowrap;
  }

  /* empty state */
  .empty-state { text-align: center; padding: 48px 20px; color: rgba(255,255,255,0.2); font-size: 13px; }

  /* ── NOTIF BAR ── */
  .notif-bar {
    display: flex; align-items: center; gap: 10px;
    border-radius: 10px; padding: 10px 14px;
    margin-bottom: 18px; font-size: 12px;
    animation: slideIn .3s ease;
  }
  @keyframes slideIn { from { opacity:0; transform:translateY(-6px); } to { opacity:1; transform:translateY(0); } }
  .notif-success { background: rgba(29,158,117,0.12); border: 1px solid rgba(29,158,117,0.25); color: #5dcaa5; }
  .notif-warning { background: rgba(239,159,39,0.12); border: 1px solid rgba(239,159,39,0.25); color: #fac775; }
  .notif-close   { margin-left: auto; background: none; border: none; cursor: pointer; font-size: 18px; }

  /* ── MODALS ── */
  .overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,0.72); z-index: 1000;
    align-items: center; justify-content: center;
  }
  .overlay.open { display: flex; }

  .modal {
    background: #1a1a24;
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 16px;
    padding: 24px;
    position: relative;
    max-height: 90vh;
    overflow-y: auto;
  }
  .modal-title { font-size: 15px; font-weight: 600; color: #fff; margin-bottom: 4px; }
  .modal-sub   { font-size: 12px; color: rgba(255,255,255,0.35); margin-bottom: 20px; }
  .modal-close {
    position: absolute; top: 16px; right: 16px;
    background: none; border: none;
    color: rgba(255,255,255,0.35); cursor: pointer;
    font-size: 20px; line-height: 1; padding: 0;
  }
  .modal-close:hover { color: #fff; }
  .modal-footer {
    display: flex; justify-content: flex-end; gap: 8px;
    padding-top: 16px; border-top: 1px solid rgba(255,255,255,0.07);
    margin-top: 4px;
  }
  .btn-cancel {
    padding: 8px 18px; border-radius: 8px;
    background: none; border: 1px solid rgba(255,255,255,0.1);
    color: rgba(255,255,255,0.4); cursor: pointer;
    font-size: 13px; font-family: 'Sora', sans-serif;
  }
  .btn-cancel:hover { color: #fff; border-color: rgba(255,255,255,0.25); }
  .btn-confirm {
    padding: 8px 20px; border-radius: 8px;
    background: #2FCAF5; color: #0f0f13;
    border: none; font-size: 13px; font-weight: 600;
    cursor: pointer; font-family: 'Sora', sans-serif;
  }
  .btn-confirm:hover { opacity: .88; }

  /* -- TIPIFICACIÓN MODAL -- */
  #modalTip { width: 500px; max-width: 95vw; }

  .tip-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 16px; }
  .tip-option {
    padding: 12px 14px; border-radius: 10px;
    border: 1px solid rgba(255,255,255,0.07);
    background: rgba(255,255,255,0.03);
    cursor: pointer; transition: all .15s;
  }
  .tip-option:hover  { border-color: rgba(47,202,245,0.3); background: rgba(47,202,245,0.04); }
  .tip-option.sel    { border-color: #2FCAF5; background: rgba(47,202,245,0.1); }
  .tip-option-name   { font-size: 12px; font-weight: 600; color: #fff; margin-bottom: 3px; }
  .tip-option-desc   { font-size: 10px; color: rgba(255,255,255,0.35); line-height: 1.4; }

  .recall-field { display: none; margin-bottom: 16px; }
  .recall-field label { font-size: 12px; color: rgba(255,255,255,0.4); display: block; margin-bottom: 6px; }
  .recall-field input {
    width: 100%; background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 8px; padding: 9px 12px;
    font-size: 13px; color: #fff;
    font-family: 'Sora', sans-serif; outline: none;
    color-scheme: dark;
  }
  .recall-field input:focus { border-color: rgba(47,202,245,0.4); }

  /* -- VENTA MODAL -- */
  #modalVenta { width: 520px; max-width: 95vw; }

  .type-toggle { display: flex; gap: 8px; margin-bottom: 18px; }
  .type-btn {
    flex: 1; padding: 9px; border-radius: 8px;
    border: 1px solid rgba(255,255,255,0.1);
    background: none; color: rgba(255,255,255,0.35);
    cursor: pointer; font-size: 12px; font-weight: 600;
    font-family: 'Sora', sans-serif; transition: all .15s;
  }
  .type-btn.active {
    background: rgba(29,158,117,0.12);
    border-color: rgba(29,158,117,0.3);
    color: #5dcaa5;
  }

  .form-group { margin-bottom: 13px; }
  .form-group label { font-size: 11px; color: rgba(255,255,255,0.4); display: block; margin-bottom: 5px; }
  .form-group input,
  .form-group select {
    width: 100%;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 8px; padding: 8px 11px;
    font-size: 12px; color: #fff;
    font-family: 'Sora', sans-serif; outline: none;
  }
  .form-group input:focus,
  .form-group select:focus { border-color: rgba(47,202,245,0.4); }
  .form-group select option { background: #1a1a24; }
  .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
  .section-sep {
    font-size: 10px; font-weight: 600; text-transform: uppercase;
    letter-spacing: .6px; color: rgba(255,255,255,0.25);
    border-top: 1px solid rgba(255,255,255,0.06);
    padding-top: 12px; margin: 14px 0 10px;
  }

  /* ── LIGHT MODE ── */
  html.light .stat-card  { background: #fff; border-color: #d0eaf8; }
  html.light .stat-label { color: rgba(0,0,0,0.4); }
  html.light .stat-value { color: #0f0f13; }
  html.light .tab-btn    { color: rgba(0,0,0,0.4); }
  html.light .tab-btn:hover { color: #0f0f13; }
  html.light .table-card { background: #fff; border-color: #d0eaf8; }
  html.light .table-top  { border-bottom-color: #e8f3fb; }
  html.light .table-top-title { color: #0f0f13; }
  html.light .search-input { background: #f0f7ff; border-color: #c0dff5; color: #0f0f13; }
  html.light .search-input::placeholder { color: rgba(0,0,0,0.3); }
  html.light th { color: rgba(0,0,0,0.3); border-bottom-color: #f0f7ff; }
  html.light td { color: rgba(0,0,0,0.7); border-bottom-color: #f8f8f8; }
  html.light tbody tr:hover { background: rgba(47,202,245,0.04); }
  html.light .ruc-cell .ruc { color: #0f0f13; }
  html.light .ruc-cell .razon { color: rgba(0,0,0,0.4); }
  html.light .telf-list { color: rgba(0,0,0,0.7); }
  html.light .timer-wrap { color: rgba(0,0,0,0.4); }
  html.light .empty-state { color: rgba(0,0,0,0.2); }
  html.light .modal { background: #fff; border-color: #d0eaf8; }
  html.light .modal-title { color: #0f0f13; }
  html.light .modal-sub { color: rgba(0,0,0,0.4); }
  html.light .modal-close { color: rgba(0,0,0,0.3); }
  html.light .modal-close:hover { color: #0f0f13; }
  html.light .tip-option { border-color: #e0eef8; background: #f8fcff; }
  html.light .tip-option:hover { border-color: rgba(47,202,245,0.4); background: rgba(47,202,245,0.05); }
  html.light .tip-option.sel { border-color: #2FCAF5; background: rgba(47,202,245,0.08); }
  html.light .tip-option-name { color: #0f0f13; }
  html.light .tip-option-desc { color: rgba(0,0,0,0.4); }
  html.light .recall-field label { color: rgba(0,0,0,0.5); }
  html.light .recall-field input { background: #f0f7ff; border-color: #c0dff5; color: #0f0f13; color-scheme: light; }
  html.light .modal-footer { border-top-color: #e8f3fb; }
  html.light .btn-cancel { color: rgba(0,0,0,0.4); border-color: #d0eaf8; }
  html.light .btn-cancel:hover { color: #0f0f13; }
  html.light .type-btn { border-color: #d0eaf8; color: rgba(0,0,0,0.4); }
  html.light .form-group label { color: rgba(0,0,0,0.5); }
  html.light .form-group input,
  html.light .form-group select { background: #f0f7ff; border-color: #c0dff5; color: #0f0f13; }
  html.light .section-sep { color: rgba(0,0,0,0.3); border-top-color: #e8f3fb; }
  html.light .tabs-bar { border-bottom-color: #d0eaf8; }
  html.light .timer-bar-bg { background: rgba(0,0,0,0.07); }
</style>

{{-- Notificaciones de sesión --}}
@if(session('notif_tip'))
  <div class="notif-bar notif-success" id="sessionNotif">
    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="#5dcaa5" stroke-width="1.5"><circle cx="8" cy="8" r="6"/><path d="M5 8l2 2 4-4"/></svg>
    {{ session('notif_tip') }}
    <button class="notif-close" onclick="this.parentElement.remove()" style="color:#5dcaa5">×</button>
  </div>
@endif

@if(session('notif_recall'))
  <div class="notif-bar notif-warning" id="recallNotif">
    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="#fac775" stroke-width="1.5"><circle cx="8" cy="8" r="6"/><path d="M8 5v3l2 1"/></svg>
    {{ session('notif_recall') }}
    <button class="notif-close" onclick="this.parentElement.remove()" style="color:#fac775">×</button>
  </div>
@endif

{{-- Stats --}}
<div class="stats-row">
  <div class="stat-card">
    <div class="stat-label">Total asignados</div>
    <div class="stat-value">{{ $counts['total'] }}</div>
    <div class="stat-sub" style="color:#2FCAF5">Leads activos</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Pendientes</div>
    <div class="stat-value">{{ $counts['pendiente'] }}</div>
    <div class="stat-sub" style="color:rgba(255,255,255,0.3)">Sin trabajar</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Agendados</div>
    <div class="stat-value">{{ $counts['volver_llamar'] }}</div>
    <div class="stat-sub" style="color:#fac775">
      Hoy: {{ $counts['recall_hoy'] }}
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Prospectos</div>
    <div class="stat-value">{{ $counts['prospecto'] }}</div>
    <div class="stat-sub" style="color:#5dcaa5">Con propuesta</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">No interesados</div>
    <div class="stat-value">{{ $counts['no_interesado'] }}</div>
    <div class="stat-sub" style="color:rgba(255,255,255,0.3)">Reciclando</div>
  </div>
</div>

{{-- Tabs --}}
<div class="tabs-bar">
  <button class="tab-btn active" onclick="switchTab('nuevos', this)">
    Nuevos leads
    <span class="tab-count tc-cyan">{{ $counts['pendiente'] }}</span>
  </button>
  <button class="tab-btn" onclick="switchTab('agendados', this)">
    Agendados
    <span class="tab-count tc-orange">{{ $counts['volver_llamar'] }}</span>
  </button>
  <button class="tab-btn" onclick="switchTab('no_interesados', this)">
    No interesados
    <span class="tab-count tc-gray">{{ $counts['no_interesado'] }}</span>
  </button>
  <button class="tab-btn" onclick="switchTab('prospectos', this)">
    Prospectos
    <span class="tab-count tc-green">{{ $counts['prospecto'] }}</span>
  </button>
  <button class="tab-btn" onclick="switchTab('no_califica', this)">
    No califica
    <span class="tab-count tc-red">{{ $counts['no_califica'] }}</span>
  </button>
</div>

{{-- ──────────────────────────────────────────
     TAB: NUEVOS LEADS (pendiente + num. equivocado devuelto)
─────────────────────────────────────────── --}}
<div class="tab-pane active" id="tab-nuevos">
  <div class="table-card">
    <div class="table-top">
      <span class="table-top-title">Leads sin trabajar</span>
      <input class="search-input" type="text" placeholder="Buscar RUC o empresa..."
             oninput="filterTable('tbl-nuevos', this.value)">
    </div>
    <table id="tbl-nuevos">
      <thead>
        <tr>
          <th>Empresa / RUC</th>
          <th>Representante Legal</th>
          <th>Teléfonos</th>
          <th>Operadoras</th>
          <th>Tipificación</th>
          <th>Acción</th>
        </tr>
      </thead>
      <tbody>
        @forelse($leads['pendiente'] as $lead)
        <tr onclick="openTipModal({{ $lead->id }}, '{{ addslashes($lead->razon_social) }}', '{{ $lead->ruc }}')">
          <td>
            <div class="ruc-cell">
              <div class="ruc">{{ $lead->ruc }}</div>
              <div class="razon">{{ $lead->razon_social }}</div>
            </div>
          </td>
          <td>{{ $lead->nombre_rl ?? '—' }}</td>
          <td>
            <div class="telf-list">
              @foreach(array_filter([$lead->telf1,$lead->telf2,$lead->telf3,$lead->telf4,$lead->telf5]) as $t)
                <span>{{ $t }}</span>
              @endforeach
            </div>
          </td>
          <td>
            <div class="ops">
              @if($lead->movistar > 0)<span class="op op-mv">MV {{ $lead->movistar }}</span>@endif
              @if($lead->entel    > 0)<span class="op op-en">EN {{ $lead->entel }}</span>@endif
              @if($lead->claro    > 0)<span class="op op-cl">CL {{ $lead->claro }}</span>@endif
              @if($lead->bitel    > 0)<span class="op op-bt">BT {{ $lead->bitel }}</span>@endif
            </div>
          </td>
          <td><span class="tip-badge tip-pendiente">Pendiente</span></td>
          <td onclick="event.stopPropagation()">
            <button class="btn-tip"
              onclick="openTipModal({{ $lead->id }}, '{{ addslashes($lead->razon_social) }}', '{{ $lead->ruc }}')">
              Tipificar
            </button>
            <form method="POST" action="{{ route('admin.leads.release', $lead) }}" onsubmit="return confirm('¿Liberar este lead?')" style="display:inline;">
              @csrf @method('PATCH')
              <button type="submit" class="btn-tip" style="background:rgba(239,159,39,0.08);color:#fac775;border-color:rgba(239,159,39,0.2);">
                Liberar
              </button>
            </form>
          </td>
        </tr>
        @empty
        <tr><td colspan="6"><div class="empty-state">No tienes leads asignados.</div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- ──────────────────────────────────────────
     TAB: AGENDADOS
─────────────────────────────────────────── --}}
<div class="tab-pane" id="tab-agendados">
  <div class="table-card">
    <div class="table-top">
      <span class="table-top-title">Leads agendados para rellamar</span>
    </div>
    <table>
      <thead>
        <tr>
          <th>Empresa / RUC</th>
          <th>Teléfonos</th>
          <th>Operadoras</th>
          <th>Rellamar el</th>
          <th>Acción</th>
        </tr>
      </thead>
      <tbody>
        @forelse($leads['volver_llamar'] as $lead)
          @php
            $isHoy  = $lead->recall_at && $lead->recall_at->isToday();
            $isPast = $lead->recall_at && $lead->recall_at->isPast();
          @endphp
        <tr>
          <td>
            <div class="ruc-cell">
              <div class="ruc">{{ $lead->ruc }}</div>
              <div class="razon">{{ $lead->razon_social }}</div>
            </div>
          </td>
          <td>
            <div class="telf-list">
              @foreach(array_filter([$lead->telf1,$lead->telf2,$lead->telf3,$lead->telf4,$lead->telf5]) as $t)
                <span>{{ $t }}</span>
              @endforeach
            </div>
          </td>
          <td>
            <div class="ops">
              @if($lead->movistar > 0)<span class="op op-mv">MV {{ $lead->movistar }}</span>@endif
              @if($lead->entel    > 0)<span class="op op-en">EN {{ $lead->entel }}</span>@endif
              @if($lead->claro    > 0)<span class="op op-cl">CL {{ $lead->claro }}</span>@endif
              @if($lead->bitel    > 0)<span class="op op-bt">BT {{ $lead->bitel }}</span>@endif
            </div>
          </td>
          <td>
            @if($lead->recall_at)
              <div class="recall-badge {{ ($isHoy || $isPast) ? 'recall-now' : '' }}">
                <span style="width:6px;height:6px;border-radius:50%;background:currentColor;display:inline-block;flex-shrink:0"></span>
                {{ $isPast && !$isHoy ? '¡Vencido! ' : '' }}{{ $lead->recall_at->format('d/m/Y H:i') }}
              </div>
            @else
              <span style="color:rgba(255,255,255,0.25);font-size:11px">—</span>
            @endif
          </td>
          <td>
            <button class="btn-tip"
              onclick="openTipModal({{ $lead->id }}, '{{ addslashes($lead->razon_social) }}', '{{ $lead->ruc }}')">
              Tipificar
            </button>
          </td>
        </tr>
        @empty
        <tr><td colspan="5"><div class="empty-state">No tienes leads agendados.</div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- ──────────────────────────────────────────
     TAB: NO INTERESADOS
─────────────────────────────────────────── --}}
<div class="tab-pane" id="tab-no_interesados">
  <div class="table-card">
    <div class="table-top">
      <span class="table-top-title">No interesados — se reciclan en 30 días</span>
    </div>
    <table>
      <thead>
        <tr>
          <th>Empresa / RUC</th>
          <th>Teléfonos</th>
          <th>Operadoras</th>
          <th>Tipificado el</th>
          <th>Recicla en</th>
          <th>Acción</th>
        </tr>
      </thead>
      <tbody>
        @forelse($leads['no_interesado'] as $lead)
          @php
  $diasDesde     = $lead->updated_at->startOfDay()->diffInDays(now()->startOfDay());
  $diasRestantes = max(0, 30 - $diasDesde);
  $pct           = round(min(100, ($diasDesde / 30) * 100));
  $barColor      = $pct >= 80 ? '#ff9090' : ($pct >= 50 ? '#fac775' : '#5dcaa5');
@endphp
        <tr>
          <td>
            <div class="ruc-cell">
              <div class="ruc">{{ $lead->ruc }}</div>
              <div class="razon">{{ $lead->razon_social }}</div>
            </div>
          </td>
          <td>
            <div class="telf-list">
              @foreach(array_filter([$lead->telf1,$lead->telf2,$lead->telf3,$lead->telf4,$lead->telf5]) as $t)
                <span>{{ $t }}</span>
              @endforeach
            </div>
          </td>
          <td>
            <div class="ops">
              @if($lead->movistar > 0)<span class="op op-mv">MV {{ $lead->movistar }}</span>@endif
              @if($lead->entel    > 0)<span class="op op-en">EN {{ $lead->entel }}</span>@endif
              @if($lead->claro    > 0)<span class="op op-cl">CL {{ $lead->claro }}</span>@endif
              @if($lead->bitel    > 0)<span class="op op-bt">BT {{ $lead->bitel }}</span>@endif
            </div>
          </td>
          <td style="font-size:11px;color:rgba(255,255,255,0.35)">
            {{ $lead->updated_at->format('d/m/Y') }}
          </td>
          <td>
            <div class="timer-wrap">
              {{ $diasRestantes }} día{{ $diasRestantes !== 1 ? 's' : '' }}
              <div class="timer-bar-bg">
                <div class="timer-bar-fill"
                     style="width:{{ $pct }}%; background:{{ $barColor }}">
                </div>
              </div>
            </div>
          </td>
          <td>
            <button class="btn-tip"
              onclick="openTipModal({{ $lead->id }}, '{{ addslashes($lead->razon_social) }}', '{{ $lead->ruc }}')">
              Re-tipificar
            </button>
          </td>
        </tr>
        @empty
        <tr><td colspan="6"><div class="empty-state">No tienes leads en esta categoría.</div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- ──────────────────────────────────────────
     TAB: PROSPECTOS
─────────────────────────────────────────── --}}
<div class="tab-pane" id="tab-prospectos">
  <div class="table-card">
    <div class="table-top">
      <span class="table-top-title">Prospectos con propuesta enviada</span>
    </div>
    <table>
      <thead>
        <tr>
          <th>Empresa / RUC</th>
          <th>Teléfonos</th>
          <th>Operadoras</th>
          <th>Propuesta</th>
          <th>Ventas</th>
          <th>Acción</th>
        </tr>
      </thead>
      <tbody>
        @forelse($leads['prospecto'] as $lead)
        <tr>
          <td>
            <div class="ruc-cell">
              <div class="ruc">{{ $lead->ruc }}</div>
              <div class="razon">{{ $lead->razon_social }}</div>
            </div>
          </td>
          <td>
            <div class="telf-list">
              @foreach(array_filter([$lead->telf1,$lead->telf2,$lead->telf3,$lead->telf4,$lead->telf5]) as $t)
                <span>{{ $t }}</span>
              @endforeach
            </div>
          </td>
          <td>
            <div class="ops">
              @if($lead->movistar > 0)<span class="op op-mv">MV {{ $lead->movistar }}</span>@endif
              @if($lead->entel    > 0)<span class="op op-en">EN {{ $lead->entel }}</span>@endif
              @if($lead->claro    > 0)<span class="op op-cl">CL {{ $lead->claro }}</span>@endif
              @if($lead->bitel    > 0)<span class="op op-bt">BT {{ $lead->bitel }}</span>@endif
            </div>
          </td>
          <td style="font-size:11px;color:rgba(255,255,255,0.35)">
            {{ $lead->updated_at->format('d/m/Y') }}
          </td>
          <td>
            @if($lead->ventas_count > 0)
              <span style="font-size:11px;color:#5dcaa5;font-weight:600">
                {{ $lead->ventas_count }} venta{{ $lead->ventas_count > 1 ? 's' : '' }}
              </span>
            @else
              <span style="font-size:11px;color:rgba(255,255,255,0.25)">Sin ventas</span>
            @endif
          </td>
          <td style="display:flex;gap:6px;flex-wrap:wrap">
            <a href="{{ route('asesor.ventas.create', ['lead_id' => $lead->id]) }}" class="btn-venta" style="text-decoration:none;">
  + Venta
</a>
            <button class="btn-tip"
              onclick="openTipModal({{ $lead->id }}, '{{ addslashes($lead->razon_social) }}', '{{ $lead->ruc }}')">
              Tipificar
            </button>
          </td>
        </tr>
        @empty
        <tr><td colspan="6"><div class="empty-state">No tienes prospectos aún.</div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- ──────────────────────────────────────────
     TAB: NO CALIFICA
─────────────────────────────────────────── --}}
<div class="tab-pane" id="tab-no_califica">
  <div class="table-card">
    <div class="table-top">
      <span class="table-top-title">No califican</span>
    </div>
    <table>
      <thead>
        <tr>
          <th>Empresa / RUC</th>
          <th>Teléfonos</th>
          <th>Operadoras</th>
          <th>Fecha</th>
        </tr>
      </thead>
      <tbody>
        @forelse($leads['no_califica'] as $lead)
        <tr>
          <td>
            <div class="ruc-cell">
              <div class="ruc">{{ $lead->ruc }}</div>
              <div class="razon">{{ $lead->razon_social }}</div>
            </div>
          </td>
          <td>
            <div class="telf-list">
              @foreach(array_filter([$lead->telf1,$lead->telf2,$lead->telf3,$lead->telf4,$lead->telf5]) as $t)
                <span>{{ $t }}</span>
              @endforeach
            </div>
          </td>
          <td>
            <div class="ops">
              @if($lead->movistar > 0)<span class="op op-mv">MV {{ $lead->movistar }}</span>@endif
              @if($lead->entel    > 0)<span class="op op-en">EN {{ $lead->entel }}</span>@endif
              @if($lead->claro    > 0)<span class="op op-cl">CL {{ $lead->claro }}</span>@endif
              @if($lead->bitel    > 0)<span class="op op-bt">BT {{ $lead->bitel }}</span>@endif
            </div>
          </td>
          <td style="font-size:11px;color:rgba(255,255,255,0.35)">
            {{ $lead->updated_at->format('d/m/Y') }}
          </td>
        </tr>
        @empty
        <tr><td colspan="4"><div class="empty-state">No hay leads en esta categoría.</div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>


{{-- ══════════════════════════════════════════
     MODAL: TIPIFICACIÓN
══════════════════════════════════════════ --}}
<div class="overlay" id="overlayTip" onclick="closeBg(event,'overlayTip')">
  <div class="modal" id="modalTip">
    <button class="modal-close" onclick="closeModal('overlayTip')">×</button>
    <div class="modal-title" id="tipTitle">Tipificar lead</div>
    <div class="modal-sub"   id="tipSub">Selecciona una acción para este lead</div>

    <form method="POST" action="{{ route('asesor.leads.tipificar') }}" id="formTip">
      @csrf
      <input type="hidden" name="lead_id"     id="tipLeadId">
      <input type="hidden" name="tipificacion" id="tipValor">

      <div class="tip-grid">
        <div class="tip-option" onclick="selTip(this,'volver_llamar')">
          <div class="tip-option-name">📅 Volver a llamar</div>
          <div class="tip-option-desc">Agenda una fecha y hora para rellamar</div>
        </div>
        <div class="tip-option" onclick="selTip(this,'no_interesado')">
          <div class="tip-option-name">🚫 No interesado</div>
          <div class="tip-option-desc">Pasa a espera — se recicla en 30 días</div>
        </div>
        <div class="tip-option" onclick="selTip(this,'numero_equivocado')">
          <div class="tip-option-name">📵 Número equivocado</div>
          <div class="tip-option-desc">Va al admin para corrección de teléfonos</div>
        </div>
        <div class="tip-option" onclick="selTip(this,'no_califica')">
          <div class="tip-option-name">❌ No califica</div>
          <div class="tip-option-desc">No cumple el perfil, descartado</div>
        </div>
        <div class="tip-option" onclick="selTip(this,'lista_negra')">
          <div class="tip-option-name">⛔ Lista negra</div>
          <div class="tip-option-desc">Bloquea RUC y teléfonos permanentemente</div>
        </div>
        <div class="tip-option" onclick="selTip(this,'prospecto')">
          <div class="tip-option-name">⭐ Prospecto</div>
          <div class="tip-option-desc">Propuesta enviada — listo para cerrar venta</div>
        </div>
      </div>

      <div class="recall-field" id="recallField">
        <label>Fecha y hora de rellamada</label>
        <input type="datetime-local" name="recall_at" id="recallInput"
               min="{{ now()->format('Y-m-d\TH:i') }}">
      </div>

      <div class="modal-footer">
        <button type="button" class="btn-cancel" onclick="closeModal('overlayTip')">Cancelar</button>
        <button type="submit" class="btn-confirm" id="btnConfirmTip">Confirmar</button>
      </div>
    </form>
  </div>
</div>


{{-- ══════════════════════════════════════════
     MODAL: INGRESAR VENTA
══════════════════════════════════════════ --}}
<div class="overlay" id="overlayVenta" onclick="closeBg(event,'overlayVenta')">
  <div class="modal" id="modalVenta">
    <button class="modal-close" onclick="closeModal('overlayVenta')">×</button>
    <div class="modal-title" id="ventaTitle">Registrar venta</div>
    <div class="modal-sub"   id="ventaSub">Completa los datos de la venta</div>

    <form method="POST" action="#" id="formVenta">
      @csrf
      <input type="hidden" name="lead_id" id="ventaLeadId">
      <input type="hidden" name="tipo"    id="ventaTipo" value="movil">

      <div class="type-toggle">
        <button type="button" class="type-btn active" id="btnMovil" onclick="setTipo('movil')">📱 Móvil</button>
        <button type="button" class="type-btn"        id="btnFija"  onclick="setTipo('fija')">🏢 Fija / Internet</button>
      </div>

      {{-- Campos comunes --}}
      <div class="form-row">
        <div class="form-group">
          <label>Operadora</label>
          <select name="operadora">
            <option>Movistar</option>
            <option>Entel</option>
            <option>Claro</option>
            <option>Bitel</option>
          </select>
        </div>
        <div class="form-group">
          <label>Cant. de líneas</label>
          <input type="number" name="cantidad_lineas" min="1" placeholder="Ej: 4">
        </div>
      </div>
      <div class="form-group">
        <label>Nombre del contacto</label>
        <input type="text" name="nombre_contacto" placeholder="Representante / encargado">
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Teléfono de contacto</label>
          <input type="text" name="telefono_contacto" placeholder="9XX XXX XXX">
        </div>
        <div class="form-group">
          <label>Correo</label>
          <input type="email" name="correo_contacto" placeholder="correo@empresa.com">
        </div>
      </div>

      {{-- Campos móvil --}}
      <div id="fieldsMovil">
        <div class="section-sep">Datos móvil</div>
        <div class="form-row">
          <div class="form-group">
            <label>Plan por línea (S/.)</label>
            <input type="number" step="0.01" name="plan_precio" placeholder="Ej: 49.90">
          </div>
          <div class="form-group">
            <label>Tipo de contrato</label>
            <select name="tipo_contrato">
              <option>Portabilidad</option>
              <option>Alta nueva</option>
              <option>Renovación</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label>Observaciones</label>
          <input type="text" name="observaciones_movil" placeholder="Notas sobre la venta móvil">
        </div>
      </div>

      {{-- Campos fija --}}
      <div id="fieldsFija" style="display:none">
        <div class="section-sep">Datos fija / internet</div>
        <div class="form-row">
          <div class="form-group">
            <label>Tipo de servicio</label>
            <select name="tipo_servicio">
              <option>Internet fibra</option>
              <option>Telefonía fija</option>
              <option>Pack combo</option>
            </select>
          </div>
          <div class="form-group">
            <label>Velocidad / Plan</label>
            <input type="text" name="velocidad_plan" placeholder="Ej: 200 Mbps">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Precio mensual (S/.)</label>
            <input type="number" step="0.01" name="precio_mensual" placeholder="Ej: 89.90">
          </div>
          <div class="form-group">
            <label>Dirección de instalación</label>
            <input type="text" name="direccion_instalacion" placeholder="Av. / Jr.">
          </div>
        </div>
        <div class="form-group">
          <label>Observaciones</label>
          <input type="text" name="observaciones_fija" placeholder="Notas sobre la venta fija">
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn-cancel" onclick="closeModal('overlayVenta')">Cancelar</button>
        <button type="submit" class="btn-confirm">Registrar venta</button>
      </div>
    </form>
  </div>
</div>

<script>
// ── TABS ──────────────────────────────────
function switchTab(id, btn) {
  document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  document.getElementById('tab-' + id).classList.add('active');
  btn.classList.add('active');
  // Guardar tab activo en URL hash
  history.replaceState(null, '', '#' + id);
}

// Restaurar tab activo desde hash
(function() {
  var hash = location.hash.replace('#', '');
  var validTabs = ['nuevos','agendados','no_interesados','prospectos','no_califica'];
  if (hash && validTabs.includes(hash)) {
    var btn = document.querySelector('.tab-btn[onclick*="' + hash + '"]');
    if (btn) switchTab(hash, btn);
  }
})();

// ── FILTRO DE TABLA ───────────────────────
function filterTable(id, q) {
  q = q.toLowerCase();
  document.querySelectorAll('#' + id + ' tbody tr').forEach(row => {
    row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
}

// ── MODAL TIPIFICACIÓN ────────────────────
var currentLeadId = null;
var selectedTip   = null;

function openTipModal(leadId, empresa, ruc) {
  currentLeadId = leadId;
  selectedTip   = null;
  document.getElementById('tipTitle').textContent   = empresa;
  document.getElementById('tipSub').textContent     = 'RUC ' + ruc;
  document.getElementById('tipLeadId').value        = leadId;
  document.getElementById('tipValor').value         = '';
  document.getElementById('recallField').style.display = 'none';
  document.getElementById('recallInput').value      = '';
  document.querySelectorAll('.tip-option').forEach(o => o.classList.remove('sel'));
  document.getElementById('overlayTip').classList.add('open');
}

function selTip(el, tip) {
  document.querySelectorAll('.tip-option').forEach(o => o.classList.remove('sel'));
  el.classList.add('sel');
  selectedTip = tip;
  document.getElementById('tipValor').value = tip;
  document.getElementById('recallField').style.display = (tip === 'volver_llamar') ? 'block' : 'none';
  if (tip !== 'volver_llamar') document.getElementById('recallInput').value = '';
}

document.getElementById('formTip').addEventListener('submit', function(e) {
  if (!selectedTip) {
    e.preventDefault();
    alert('Por favor selecciona una tipificación.');
    return;
  }
  if (selectedTip === 'volver_llamar' && !document.getElementById('recallInput').value) {
    e.preventDefault();
    alert('Debes seleccionar una fecha y hora para la rellamada.');
    return;
  }
  if (selectedTip === 'lista_negra') {
    if (!confirm('¿Estás seguro? Esta acción bloqueará el RUC y todos los teléfonos del lead permanentemente.')) {
      e.preventDefault();
    }
  }
});

// ── MODAL VENTA ───────────────────────────
function openVentaModal(leadId, empresa, ruc) {
  document.getElementById('ventaLeadId').value    = leadId;
  document.getElementById('ventaTitle').textContent = 'Nueva venta — ' + empresa;
  document.getElementById('ventaSub').textContent   = 'RUC ' + ruc;
  setTipo('movil');
  document.getElementById('overlayVenta').classList.add('open');
}

function setTipo(tipo) {
  document.getElementById('ventaTipo').value    = tipo;
  document.getElementById('btnMovil').classList.toggle('active', tipo === 'movil');
  document.getElementById('btnFija').classList.toggle('active',  tipo === 'fija');
  document.getElementById('fieldsMovil').style.display = tipo === 'movil' ? 'block' : 'none';
  document.getElementById('fieldsFija').style.display  = tipo === 'fija'  ? 'block' : 'none';
}

// ── HELPERS ───────────────────────────────
function closeModal(id)           { document.getElementById(id).classList.remove('open'); }
function closeBg(event, id)       { if (event.target.id === id) closeModal(id); }

// Cerrar con Escape
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    closeModal('overlayTip');
    closeModal('overlayVenta');
  }
});
</script>
@endsection