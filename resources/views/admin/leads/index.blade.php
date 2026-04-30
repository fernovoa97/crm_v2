@extends('layouts.app')

@section('title', 'Gestión de Leads')
@section('subtitle', 'Importa y administra tu base de leads')

@section('topbar-actions')
  {{-- BOTÓN ASIGNAR MASIVO: Para Admin, Jefe y Supervisor --}}
  @php
      $user = auth()->user();
      // Validamos usando la propiedad 'role' directamente
      $isManager = $user->isAdmin() || $user->role === 'jefe' || $user->role === 'supervisor';
  @endphp

  @if($isManager)
    <button type="button" class="btn-assign-bulk" onclick="openBulkModal()">
        Asignar leads
    </button>
  @endif

  {{-- BOTÓN IMPORTAR: Estrictamente para el ADMIN --}}
  @if($user->isAdmin())
  <button onclick="document.getElementById('modalImport').style.display='flex'" style="
    display: inline-flex; align-items: center; gap: 6px;
    background: #2FCAF5; color: #0f0f13;
    padding: 8px 16px; border-radius: 8px;
    font-size: 13px; font-weight: 600;
    border: none; cursor: pointer;
    transition: opacity 0.2s;
  " onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
    <svg width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="#0f0f13" stroke-width="2">
      <path d="M6 1v10M1 6h10"/>
    </svg>
    Importar Excel
  </button>
  @endif
@endsection

@section('content')
<style>
  .stats-row {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
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
  .stat-badge {
    display: inline-block; font-size: 10px;
    padding: 2px 8px; border-radius: 20px;
    margin-top: 6px; font-weight: 500;
  }

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

  .table-top-left { display: flex; align-items: center; gap: 10px; }
  .table-top-right { display: flex; align-items: center; gap: 8px; }

  .table-top span { font-size: 14px; font-weight: 600; color: #fff; white-space: nowrap; }

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
    transition: border 0.2s;
  }

  .search-input::placeholder { color: rgba(255,255,255,0.25); }
  .search-input:focus { border-color: rgba(47,202,245,0.4); }

  .filter-select {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 8px;
    padding: 7px 12px;
    font-size: 13px;
    color: rgba(255,255,255,0.7);
    font-family: 'Sora', sans-serif;
    outline: none;
  }

  .filter-select option { background: #1e1e2a; }

  .btn-assign-bulk {
    display: inline-flex;
    align-items: center; gap: 6px;
    background: rgba(47,202,245,0.12);
    color: #2FCAF5;
    border: 1px solid rgba(47,202,245,0.25);
    border-radius: 8px;
    padding: 7px 14px;
    font-size: 13px; font-weight: 600;
    cursor: pointer;
    font-family: 'Sora', sans-serif;
    transition: all 0.2s;
  }

  .btn-assign-bulk:hover { background: rgba(47,202,245,0.2); }

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
    white-space: nowrap;
  }

  tr:last-child td { border-bottom: none; }
  tbody tr { transition: background 0.15s; }
  tbody tr:hover { background: rgba(255,255,255,0.02); }

  .tip-pill {
    display: inline-block; font-size: 11px;
    padding: 3px 10px; border-radius: 20px; font-weight: 500;
  }

  .tip-pendiente         { background: rgba(136,135,128,0.15); color: #b4b2a9; }
  .tip-prospecto         { background: rgba(47,202,245,0.12);  color: #2FCAF5; }
  .tip-volver_llamar     { background: rgba(239,159,39,0.15);  color: #fac775; }
  .tip-no_interesado     { background: rgba(216,90,48,0.15);   color: #f0997b; }
  .tip-no_califica       { background: rgba(162,45,45,0.15);   color: #f09595; }
  .tip-lista_negra       { background: rgba(50,50,60,0.5);     color: rgba(255,255,255,0.3); }
  .tip-numero_equivocado { background: rgba(127,119,221,0.15); color: #afa9ec; }

  .seg-pill {
    display: inline-block; font-size: 10px;
    padding: 2px 8px; border-radius: 20px; font-weight: 500;
  }

  .seg-micro   { background: rgba(29,158,117,0.12); color: #5dcaa5; }
  .seg-pyme    { background: rgba(47,202,245,0.12); color: #2FCAF5; }
  .seg-nuevo   { background: rgba(127,119,221,0.15); color: #afa9ec; }
  .seg-mayores { background: rgba(239,159,39,0.15); color: #fac775; }

  .btn-delete, .btn-assign-single {
    padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 500;
    cursor: pointer; font-family: 'Sora', sans-serif; transition: opacity 0.2s;
  }

  .btn-delete { background: rgba(255,80,80,0.08); color: #ff9090; border: 1px solid rgba(255,80,80,0.15); }
  .btn-assign-single { background: rgba(47,202,245,0.08); color: #2FCAF5; border: 1px solid rgba(47,202,245,0.2); }
  .btn-delete:hover, .btn-assign-single:hover { opacity: 0.75; }

  .empty-state { text-align: center; padding: 60px 20px; color: rgba(255,255,255,0.25); font-size: 14px; }

  .pagination-wrap {
    padding: 16px 20px; border-top: 1px solid rgba(255,255,255,0.05);
    display: flex; justify-content: space-between; align-items: center;
    font-size: 12px; color: rgba(255,255,255,0.35);
  }

  /* Modales */
  .modal-overlay {
    display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6);
    z-index: 200; align-items: center; justify-content: center;
  }

  .modal-box {
    background: #15151c; border: 1px solid rgba(255,255,255,0.1);
    border-radius: 16px; padding: 32px; width: 440px; max-width: 90vw;
  }

  .modal-title { font-size: 16px; font-weight: 600; color: #fff; margin-bottom: 6px; }
  .modal-sub { font-size: 13px; color: rgba(255,255,255,0.4); margin-bottom: 24px; }

  .file-drop {
    border: 2px dashed rgba(47,202,245,0.3); border-radius: 12px; padding: 32px;
    text-align: center; cursor: pointer; transition: all 0.2s; margin-bottom: 20px;
  }

  .file-drop:hover { border-color: rgba(47,202,245,0.6); background: rgba(47,202,245,0.03); }
  .file-drop p { font-size: 13px; color: rgba(255,255,255,0.4); margin-top: 8px; }

  .assign-user-list { display: flex; flex-direction: column; gap: 8px; max-height: 280px; overflow-y: auto; margin-bottom: 20px; }
  .assign-user-item {
    display: flex; align-items: center; gap: 10px; padding: 10px 12px;
    border-radius: 9px; border: 1px solid rgba(255,255,255,0.07);
    cursor: pointer; transition: all 0.2s;
  }

  .assign-user-item:hover { border-color: rgba(47,202,245,0.3); background: rgba(47,202,245,0.04); }

  .assign-avatar {
    width: 28px; height: 28px; border-radius: 50%;
    background: rgba(47,202,245,0.12); color: #2FCAF5;
    display: flex; align-items: center; justify-content: center;
    font-size: 10px; font-weight: 600; flex-shrink: 0;
  }

  .assign-name { font-size: 13px; color: #fff; font-weight: 500; }
  .assign-role { font-size: 11px; color: rgba(255,255,255,0.35); }

  .modal-actions { display: flex; justify-content: flex-end; gap: 10px; }

  .btn-modal-cancel {
    padding: 9px 18px; background: none; border: 1px solid rgba(255,255,255,0.1);
    border-radius: 8px; font-size: 13px; color: rgba(255,255,255,0.4); cursor: pointer;
  }

  .btn-modal-confirm {
    padding: 9px 20px; background: #2FCAF5; border: none; border-radius: 8px;
    font-size: 13px; font-weight: 600; color: #0f0f13; cursor: pointer;
  }

  /* Modo claro */
  html.light .stat-card { background: #fff; border-color: #d0eaf8; }
  html.light .stat-label { color: rgba(0,0,0,0.4); }
  html.light .stat-value { color: #0f0f13; }
  html.light .table-card { background: #fff; border-color: #d0eaf8; }
  html.light .table-top span { color: #0f0f13; }
  html.light .search-input { background: #f0f7ff; border-color: #c0dff5; color: #0f0f13; }
  html.light .filter-select { background: #f0f7ff; border-color: #c0dff5; color: #0f0f13; }
  html.light th { color: rgba(0,0,0,0.35); border-bottom-color: #e8f3fb; }
  html.light td { color: rgba(0,0,0,0.7); border-bottom-color: #f0f7ff; }
  html.light .modal-box { background: #fff; border-color: #d0eaf8; }
  html.light .modal-title { color: #0f0f13; }
  html.light .assign-name { color: #0f0f13; }
</style>

@php
    $user = auth()->user();
    
    // Usamos el objeto $leads que viene del Controller (es un Paginator)
    // Para las estadísticas, filtramos la colección de leads que se están mostrando.
    $isManager = $user->isAdmin() || $user->role === 'jefe' || $user->role === 'supervisor';
    
    // Obtenemos los leads de la página actual para contar las tipificaciones
    $currentPageLeads = $leads->getCollection();
@endphp

{{-- 1. Stats Row --}}
<div class="stats-row">
  <div class="stat-card">
    <div class="stat-label">Total leads</div>
    {{-- Muestra el total histórico filtrado que calculó el Controller --}}
    <div class="stat-value">{{ $leads->total() }}</div>
    <div class="stat-badge" style="background:rgba(47,202,245,0.1);color:#2FCAF5;">En base</div>
  </div>
  
  <div class="stat-card">
    <div class="stat-label">Pendientes</div>
    {{-- Contamos sobre la colección actual --}}
    <div class="stat-value">{{ $currentPageLeads->where('tipificacion','pendiente')->count() }}</div>
    <div class="stat-badge" style="background:rgba(136,135,128,0.15);color:#b4b2a9;">En esta página</div>
  </div>

  <div class="stat-card">
    <div class="stat-label">Prospectos</div>
    <div class="stat-value">{{ $currentPageLeads->where('tipificacion','prospecto')->count() }}</div>
    <div class="stat-badge" style="background:rgba(47,202,245,0.12);color:#2FCAF5;">En esta página</div>
  </div>

  <div class="stat-card">
    <div class="stat-label">Volver a llamar</div>
    <div class="stat-value">{{ $currentPageLeads->where('tipificacion','volver_llamar')->count() }}</div>
    <div class="stat-badge" style="background:rgba(239,159,39,0.15);color:#fac775;">En esta página</div>
  </div>

  <div class="stat-card">
    <div class="stat-label">Lista negra</div>
    <div class="stat-value">{{ $currentPageLeads->where('tipificacion','lista_negra')->count() }}</div>
    <div class="stat-badge" style="background:rgba(50,50,60,0.5);color:rgba(255,255,255,0.3);">En esta página</div>
  </div>
</div>

{{-- 2. Tabla --}}
<div class="table-card">
  <div class="table-top">
    <div class="table-top-left">
      <span>Base de leads</span>
    </div>
    <div class="table-top-right">
      @if($isManager)
      <button type="button" class="btn-assign-bulk" onclick="openBulkModal()">
        <svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
          <path d="M2 8h12M9 3l5 5-5 5"/>
        </svg>
        Asignar leads
      </button>
      @endif

      <select class="filter-select" id="filterTip" onchange="filterTable()">
        <option value="">Todas las tipificaciones</option>
        <option value="pendiente">Pendiente</option>
        <option value="prospecto">Prospecto</option>
        <option value="volver_llamar">Volver a llamar</option>
        <option value="no_interesado">No interesado</option>
        <option value="no_califica">No califica</option>
        <option value="lista_negra">Lista negra</option>
        <option value="numero_equivocado">Número equivocado</option>
      </select>

      <input class="search-input" type="text" id="searchInput" placeholder="Buscar RUC o razón social..." oninput="filterTable()"/>
    </div>
  </div>

  <div style="overflow-x:auto;">
    <table id="leadsTable">
      <thead>
        <tr>
          <th>RUC</th>
          <th>Razón Social</th>
          <th>Segmento</th>
          <th>Departamento</th>
          <th>Tipificación</th>
          <th>Asignado a</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse($leads as $lead)
        <tr data-tip="{{ $lead->tipificacion }}" data-id="{{ $lead->id }}">
          <td style="font-family:monospace;font-size:12px;">{{ $lead->ruc }}</td>
          <td><div style="font-weight:500;color:#fff;">{{ $lead->razon_social }}</div></td>
          <td>
            @if($lead->segmento)
              <span class="seg-pill seg-{{ $lead->segmento }}">{{ ucfirst($lead->segmento) }}</span>
            @else — @endif
          </td>
          <td>{{ $lead->departamento ?? '—' }}</td>
          <td>
            <span class="tip-pill tip-{{ $lead->tipificacion }}">
              {{ ucfirst(str_replace('_', ' ', $lead->tipificacion)) }}
            </span>
          </td>
          <td>{{ $lead->assignedTo?->name ?? 'Sin asignar' }}</td>
          <td>
            <div style="display:flex;gap:6px;">
              @if($isManager)
                <button type="button" class="btn-assign-single" onclick="openSingleModal({{ $lead->id }}, '{{ addslashes($lead->razon_social) }}')">Asignar</button>
                
                @if($user->isAdmin())
                <form method="POST" action="{{ route('admin.leads.destroy', $lead) }}" onsubmit="return confirm('¿Eliminar?')">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn-delete">Eliminar</button>
                </form>
                @endif
              @else
                <a href="{{ route('asesor.leads.index') }}" class="btn-assign-single" style="text-decoration:none;">Gestionar</a>
              @endif
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="7"><div class="empty-state">No tienes leads asignados.</div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="pagination-wrap">
    <span>Mostrando {{ $leads->firstItem() ?? 0 }}–{{ $leads->lastItem() ?? 0 }} de {{ $leads->total() }}</span>
    {{ $leads->links() }}
  </div>
</div>

{{-- 3. Modales --}}

{{-- MODAL IMPORTAR --}}
@if($user->isAdmin())
<div class="modal-overlay" id="modalImport" onclick="if(event.target===this)this.style.display='none'">
  <div class="modal-box">
    <div class="modal-title">Importar leads desde Excel</div>
    <div class="modal-sub">El archivo debe tener las columnas RUC y Razón Social.</div>
    <form method="POST" action="{{ route('admin.leads.import') }}" enctype="multipart/form-data">
      @csrf
      <div class="file-drop" onclick="document.getElementById('fileInput').click()">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#2FCAF5" stroke-width="1.5" style="margin:0 auto 10px;">
          <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
        </svg>
        <p id="fileName">Haz clic para seleccionar tu archivo .xlsx o .csv</p>
        <input type="file" id="fileInput" name="file" accept=".xlsx,.xls,.csv" style="display:none"
               onchange="document.getElementById('fileName').textContent = this.files[0]?.name ?? 'Archivo seleccionado'"/>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn-modal-cancel" onclick="document.getElementById('modalImport').style.display='none'">Cancelar</button>
        <button type="submit" class="btn-modal-confirm">Importar ahora</button>
      </div>
    </form>
  </div>
</div>
@endif

{{-- MODAL ASIGNACIÓN MASIVA --}}
<div class="modal-overlay" id="modalBulk">
  <div class="modal-box">
    <div class="modal-title">Asignar leads automáticamente</div>
    <div class="modal-sub">Se repartirán los leads visibles según los filtros aplicados.</div>
    <div class="assign-user-list" id="bulkUserList"></div>
    <div style="display:flex;justify-content:space-between; margin-top:15px; font-size:13px; color:#fff;">
      <span>Total a repartir: <b id="assignedCount">0</b></span>
      <span>Disponibles: <b id="assignedTotal">0</b></span>
    </div>
    <div class="modal-actions" style="margin-top:20px;">
      <button type="button" class="btn-modal-cancel" onclick="closeModals()">Cancelar</button>
      <button type="button" class="btn-modal-confirm" onclick="submitBulkAssign()">Confirmar Asignación</button>
    </div>
  </div>
</div>

{{-- MODAL ASIGNACIÓN INDIVIDUAL --}}
<div class="modal-overlay" id="modalSingle">
  <div class="modal-box">
    <div class="modal-title">Asignar Lead</div>
    <div class="modal-sub" id="singleLeadName"></div>
    <form id="singleForm" method="POST">
      @csrf
      <div class="assign-user-list" id="singleUserList"></div>
      <div class="modal-actions" style="margin-top:20px;">
        <button type="button" class="btn-modal-cancel" onclick="closeModals()">Cancelar</button>
        <button type="submit" class="btn-modal-confirm">Asignar</button>
      </div>
    </form>
  </div>
</div>

<form id="hiddenBulkForm" method="POST" action="{{ route('admin.leads.assign') }}" style="display:none;">@csrf</form>

<script>
const availableUsers = @json($availableUsers ?? []);

function closeModals() {
  ['modalBulk', 'modalSingle', 'modalImport'].forEach(id => {
      const el = document.getElementById(id);
      if(el) el.style.display = 'none';
  });
}

function filterTable() {
  const q = document.getElementById('searchInput').value.toLowerCase();
  const tip = document.getElementById('filterTip').value;
  document.querySelectorAll('#leadsTable tbody tr[data-id]').forEach(row => {
    const text = row.textContent.toLowerCase();
    const matchSearch = text.includes(q);
    const matchTip = !tip || row.dataset.tip === tip;
    row.style.display = (matchSearch && matchTip) ? '' : 'none';
  });
}

function openBulkModal() {
  const visible = document.querySelectorAll('#leadsTable tbody tr:not([style*="display: none"])');
  if (!visible.length) return alert('No hay leads para asignar.');
  document.getElementById('assignedTotal').textContent = visible.length;
  renderUsersList('bulkUserList', true);
  document.getElementById('modalBulk').style.display = 'flex';
}

function openSingleModal(leadId, leadName) {
  const form = document.getElementById('singleForm');
  form.action = `/admin/leads/${leadId}/assign-single`;
  document.getElementById('singleLeadName').textContent = leadName;
  renderUsersList('singleUserList', false);
  document.getElementById('modalSingle').style.display = 'flex';
}

function renderUsersList(containerId, isBulk) {
  const container = document.getElementById(containerId);
  if(!container) return;
  container.innerHTML = '';
  availableUsers.forEach(user => {
    const item = document.createElement('div');
    item.className = 'assign-user-item';
    item.innerHTML = `
      <div class="assign-avatar">${user.name.substring(0,2).toUpperCase()}</div>
      <div style="flex:1;">
        <div class="assign-name">${user.name}</div>
        <div class="assign-role">${user.role}</div>
      </div>
      ${isBulk 
        ? `<input type="number" class="bulk-qty" data-user-id="${user.id}" min="0" value="0" oninput="updateAssignedCount()" style="width:60px; background:rgba(0,0,0,0.2); border:1px solid #333; color:#fff; border-radius:4px; padding:2px 5px;">`
        : `<input type="radio" name="user_id" value="${user.id}" required>`}
    `;
    container.appendChild(item);
  });
}

function updateAssignedCount() {
  let total = 0;
  document.querySelectorAll('.bulk-qty').forEach(i => total += parseInt(i.value) || 0);
  const el = document.getElementById('assignedCount');
  if(el) el.textContent = total;
}

function submitBulkAssign() {
  const form = document.getElementById('hiddenBulkForm');
  form.innerHTML = '@csrf';
  const visibleRows = document.querySelectorAll('#leadsTable tbody tr:not([style*="display: none"])');
  visibleRows.forEach(row => addHiddenInput(form, 'lead_ids[]', row.dataset.id));

  let total = 0;
  document.querySelectorAll('.bulk-qty').forEach(i => {
    const val = parseInt(i.value) || 0;
    if (val > 0) {
      addHiddenInput(form, 'user_ids[]', i.dataset.userId);
      addHiddenInput(form, 'user_qtys[]', val);
      total += val;
    }
  });

  if (total === 0) return alert('Ingresa cantidades.');
  if (total > visibleRows.length) return alert('La cantidad excede los leads disponibles.');
  form.submit();
}

function addHiddenInput(form, name, value) {
  const input = document.createElement('input');
  input.type = 'hidden'; input.name = name; input.value = value;
  form.appendChild(input);
}
</script>
@endsection