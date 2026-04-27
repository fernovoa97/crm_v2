@extends('layouts.app')

@section('title', 'Gestión de usuarios')
@section('subtitle', 'Administra el equipo y sus accesos')

@section('topbar-actions')
  <a href="{{ route('admin.users.create') }}" style="
    display: inline-flex; align-items: center; gap: 6px;
    background: #2FCAF5; color: #0f0f13;
    padding: 8px 16px; border-radius: 8px;
    font-size: 13px; font-weight: 600;
    text-decoration: none; transition: opacity 0.2s;
  " onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
    <svg width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="#0f0f13" stroke-width="2">
      <path d="M6 1v10M1 6h10"/>
    </svg>
    Nuevo usuario
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
    transition: border 0.2s;
  }

  .search-input::placeholder { color: rgba(255,255,255,0.25); }
  .search-input:focus { border-color: rgba(47,202,245,0.4); }

  table { width: 100%; border-collapse: collapse; }

  th {
    font-size: 11px; font-weight: 600;
    color: rgba(255,255,255,0.25);
    text-transform: uppercase;
    letter-spacing: 0.6px;
    padding: 12px 20px;
    text-align: left;
    border-bottom: 1px solid rgba(255,255,255,0.05);
  }

  td {
    font-size: 13px;
    color: rgba(255,255,255,0.75);
    padding: 13px 20px;
    border-bottom: 1px solid rgba(255,255,255,0.04);
  }

  tr:last-child td { border-bottom: none; }
  tbody tr { transition: background 0.15s; }
  tbody tr:hover { background: rgba(255,255,255,0.02); }

  .user-cell { display: flex; align-items: center; gap: 10px; }

  .user-avatar {
    width: 32px; height: 32px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 600;
    flex-shrink: 0;
  }

  .user-name { font-size: 13px; font-weight: 500; color: #fff; }
  .user-email { font-size: 11px; color: rgba(255,255,255,0.35); margin-top: 1px; }

  .role-pill {
    display: inline-block; font-size: 11px;
    padding: 3px 10px; border-radius: 20px; font-weight: 500;
  }

  .role-admin    { background: rgba(127,119,221,0.15); color: #afa9ec; }
  .role-jefe     { background: rgba(47,202,245,0.12);  color: #2FCAF5; }
  .role-supervisor { background: rgba(29,158,117,0.15); color: #5dcaa5; }
  .role-asesor   { background: rgba(239,159,39,0.15);  color: #fac775; }
  .role-mesa_control { background: rgba(136,135,128,0.15); color: #b4b2a9; }

  .status-badge {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 12px; font-weight: 500;
  }

  .status-dot { width: 6px; height: 6px; border-radius: 50%; }
  .dot-activo   { background: #5dcaa5; }
  .dot-inactivo { background: #888780; }

  .actions { display: flex; gap: 6px; }

  .btn-edit, .btn-delete {
    padding: 5px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    border: none;
    font-family: 'Sora', sans-serif;
    text-decoration: none;
    transition: opacity 0.2s;
  }

  .btn-edit {
    background: rgba(47,202,245,0.1);
    color: #2FCAF5;
    border: 1px solid rgba(47,202,245,0.2);
  }

  .btn-delete {
    background: rgba(255,80,80,0.08);
    color: #ff9090;
    border: 1px solid rgba(255,80,80,0.15);
  }

  .btn-edit:hover, .btn-delete:hover { opacity: 0.75; }

  .empty-state {
    text-align: center;
    padding: 60px 20px;
    color: rgba(255,255,255,0.25);
    font-size: 14px;
  }

  /* ── MODO CLARO ── */
html.light .stats-row .stat-card { background: #ffffff; border-color: #d0eaf8; }
html.light .stat-label { color: rgba(0,0,0,0.4); }
html.light .stat-value { color: #0f0f13; }
html.light .table-card { background: #ffffff; border-color: #d0eaf8; }
html.light .table-top { border-bottom-color: #d0eaf8; }
html.light .table-top span { color: #0f0f13; }
html.light .search-input { background: #f0f7ff; border-color: #c0dff5; color: #0f0f13; }
html.light .search-input::placeholder { color: rgba(0,0,0,0.3); }
html.light th { color: rgba(0,0,0,0.35); border-bottom-color: #e8f3fb; }
html.light td { color: rgba(0,0,0,0.7); border-bottom-color: #f0f7ff; }
html.light tbody tr:hover { background: rgba(47,202,245,0.04); }
html.light .user-name { color: #0f0f13; }
html.light .user-email { color: rgba(0,0,0,0.4); }
html.light .btn-edit { background: rgba(47,202,245,0.08); border-color: rgba(47,202,245,0.25); }
html.light .btn-delete { background: rgba(255,80,80,0.06); border-color: rgba(255,80,80,0.2); }
html.light .empty-state { color: rgba(0,0,0,0.25); }
html.light .stat-badge { filter: brightness(0.7); }
</style>

{{-- Stats --}}
<div class="stats-row">
  <div class="stat-card">
    <div class="stat-label">Total usuarios</div>
    <div class="stat-value">{{ $users->count() }}</div>
    <div class="stat-badge" style="background:rgba(47,202,245,0.1);color:#2FCAF5;">Registrados</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Asesores</div>
    <div class="stat-value">{{ $users->where('role','asesor')->count() }}</div>
    <div class="stat-badge" style="background:rgba(239,159,39,0.15);color:#fac775;">Activos en campo</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Supervisores</div>
    <div class="stat-value">{{ $users->where('role','supervisor')->count() }}</div>
    <div class="stat-badge" style="background:rgba(29,158,117,0.15);color:#5dcaa5;">En equipos</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Inactivos</div>
    <div class="stat-value">{{ $users->where('status','inactivo')->count() }}</div>
    <div class="stat-badge" style="background:rgba(136,135,128,0.15);color:#b4b2a9;">Sin acceso</div>
  </div>
</div>

{{-- Tabla --}}
<div class="table-card">
  <div class="table-top">
    <span>Todos los usuarios</span>
    <input class="search-input" type="text" id="searchInput" placeholder="Buscar por nombre o email..."/>
  </div>

  <table id="usersTable">
    <thead>
      <tr>
        <th>Usuario</th>
        <th>Rol</th>
        <th>Supervisor</th>
        <th>Estado</th>
        <th>Contrato</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      @forelse($users as $user)
      <tr>
        <td>
          <div class="user-cell">
            <div class="user-avatar" style="background:rgba(47,202,245,0.1);color:#2FCAF5;">
              {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>
            <div>
              <div class="user-name">{{ $user->name }}</div>
              <div class="user-email">{{ $user->email }}</div>
            </div>
          </div>
        </td>
        <td>
          <span class="role-pill role-{{ $user->role }}">
            {{ ucfirst(str_replace('_', ' ', $user->role)) }}
          </span>
        </td>
        <td>
          {{ $user->supervisor?->name ?? '—' }}
        </td>
        <td>
          <span class="status-badge">
            <span class="status-dot dot-{{ $user->status }}"></span>
            {{ ucfirst($user->status) }}
          </span>
        </td>
        <td style="color:rgba(255,255,255,0.4); font-size:12px;">
          {{ $user->contract_start ? $user->contract_start->format('d/m/Y') : '—' }}
        </td>
        <td>
          <div class="actions">
            <a href="{{ route('admin.users.edit', $user) }}" class="btn-edit">Editar</a>
            <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                  onsubmit="return confirm('¿Eliminar a {{ $user->name }}?')">
              @csrf @method('DELETE')
              <button type="submit" class="btn-delete">Eliminar</button>
            </form>
          </div>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="6">
          <div class="empty-state">No hay usuarios registrados aún.</div>
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>

<script>
  document.getElementById('searchInput').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#usersTable tbody tr').forEach(row => {
      row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
  });
</script>
@endsection