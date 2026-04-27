@extends('layouts.app')

@section('title', 'Editar usuario')
@section('subtitle', 'Modifica los datos de ' . $user->name)

@section('topbar-actions')
  <a href="{{ route('admin.users.index') }}" style="
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(255,255,255,0.07); color: rgba(255,255,255,0.7);
    padding: 8px 16px; border-radius: 8px;
    font-size: 13px; font-weight: 600;
    text-decoration: none; border: 1px solid rgba(255,255,255,0.1);
    transition: opacity 0.2s;
  " onmouseover="this.style.opacity='.75'" onmouseout="this.style.opacity='1'">
    ← Volver
  </a>
@endsection

@section('content')
<style>
  .form-card {
    background: #15151c;
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 14px;
    padding: 28px 32px;
    max-width: 720px;
  }

  .form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
  }

  .form-group { display: flex; flex-direction: column; gap: 6px; }
  .form-group.full { grid-column: span 2; }

  .form-label {
    font-size: 11px;
    font-weight: 600;
    color: rgba(255,255,255,0.4);
    letter-spacing: 0.7px;
    text-transform: uppercase;
  }

  .form-input, .form-select {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 9px;
    padding: 10px 14px;
    font-size: 13px;
    color: #fff;
    font-family: 'Sora', sans-serif;
    outline: none;
    transition: border 0.2s, background 0.2s;
    width: 100%;
  }

  .form-input::placeholder { color: rgba(255,255,255,0.2); }

  .form-input:focus, .form-select:focus {
    border-color: rgba(47,202,245,0.5);
    background: rgba(47,202,245,0.05);
  }

  .form-select option { background: #1e1e2a; color: #fff; }

  .form-error {
    font-size: 11px;
    color: #ff9090;
    margin-top: 2px;
  }

  .section-divider {
    grid-column: span 2;
    border: none;
    border-top: 1px solid rgba(255,255,255,0.06);
    margin: 6px 0;
  }

  .section-title {
    grid-column: span 2;
    font-size: 12px;
    font-weight: 600;
    color: rgba(255,255,255,0.25);
    letter-spacing: 0.8px;
    text-transform: uppercase;
    margin-bottom: -6px;
  }

  .password-hint {
    font-size: 11px;
    color: rgba(255,255,255,0.25);
    margin-top: 2px;
  }

  .form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 24px;
    padding-top: 20px;
    border-top: 1px solid rgba(255,255,255,0.06);
  }

  .btn-cancel {
    padding: 10px 20px;
    background: none;
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 9px;
    font-size: 13px;
    font-weight: 600;
    color: rgba(255,255,255,0.4);
    cursor: pointer;
    font-family: 'Sora', sans-serif;
    text-decoration: none;
    transition: all 0.2s;
  }

  .btn-cancel:hover { border-color: rgba(255,255,255,0.2); color: rgba(255,255,255,0.7); }

  .btn-save {
    padding: 10px 24px;
    background: #2FCAF5;
    border: none;
    border-radius: 9px;
    font-size: 13px;
    font-weight: 600;
    color: #0f0f13;
    cursor: pointer;
    font-family: 'Sora', sans-serif;
    transition: opacity 0.2s;
  }

  .btn-save:hover { opacity: 0.85; }
</style>

<div class="form-card">
  <form method="POST" action="{{ route('admin.users.update', $user) }}">
    @csrf
    @method('PATCH')
    <div class="form-grid">

      {{-- Datos personales --}}
      <div class="section-title">Datos personales</div>

      <div class="form-group">
        <label class="form-label">Nombre completo</label>
        <input class="form-input" type="text" name="name"
               value="{{ old('name', $user->name) }}" placeholder="Ej: Laura García" required/>
        @error('name') <span class="form-error">{{ $message }}</span> @enderror
      </div>

      <div class="form-group">
        <label class="form-label">Correo electrónico</label>
        <input class="form-input" type="email" name="email"
               value="{{ old('email', $user->email) }}" placeholder="usuario@empresa.com" required/>
        @error('email') <span class="form-error">{{ $message }}</span> @enderror
      </div>

      <div class="form-group">
        <label class="form-label">Nueva contraseña</label>
        <input class="form-input" type="password" name="password" placeholder="Dejar vacío para no cambiar"/>
        <span class="password-hint">Solo completa si deseas cambiarla</span>
        @error('password') <span class="form-error">{{ $message }}</span> @enderror
      </div>

      <div class="form-group">
        <label class="form-label">Estado</label>
        <select class="form-select" name="status">
          <option value="activo"   {{ old('status', $user->status) == 'activo'   ? 'selected' : '' }}>Activo</option>
          <option value="inactivo" {{ old('status', $user->status) == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
        </select>
        @error('status') <span class="form-error">{{ $message }}</span> @enderror
      </div>

      <hr class="section-divider"/>
      <div class="section-title">Rol y jerarquía</div>

      <div class="form-group">
        <label class="form-label">Rol</label>
        <select class="form-select" name="role" id="roleSelect">
          @foreach(['admin','jefe','supervisor','asesor','mesa_control'] as $rol)
            <option value="{{ $rol }}" {{ old('role', $user->role) == $rol ? 'selected' : '' }}>
              {{ ucfirst(str_replace('_', ' ', $rol)) }}
            </option>
          @endforeach
        </select>
        @error('role') <span class="form-error">{{ $message }}</span> @enderror
      </div>

      <div class="form-group" id="supervisorField">
        <label class="form-label">Supervisor / Jefe</label>
        <select class="form-select" name="supervisor_id">
          <option value="">Sin supervisor</option>
          @foreach($supervisors as $sup)
            <option value="{{ $sup->id }}"
              {{ old('supervisor_id', $user->supervisor_id) == $sup->id ? 'selected' : '' }}>
              {{ $sup->name }} ({{ ucfirst($sup->role) }})
            </option>
          @endforeach
        </select>
        @error('supervisor_id') <span class="form-error">{{ $message }}</span> @enderror
      </div>

      <hr class="section-divider"/>
      <div class="section-title">Contrato y compensación</div>

      <div class="form-group">
        <label class="form-label">Fecha inicio contrato</label>
        <input class="form-input" type="date" name="contract_start"
               value="{{ old('contract_start', $user->contract_start?->format('Y-m-d')) }}"/>
        @error('contract_start') <span class="form-error">{{ $message }}</span> @enderror
      </div>

      <div class="form-group">
        <label class="form-label">Fecha fin contrato</label>
        <input class="form-input" type="date" name="contract_end"
               value="{{ old('contract_end', $user->contract_end?->format('Y-m-d')) }}"/>
        @error('contract_end') <span class="form-error">{{ $message }}</span> @enderror
      </div>

      <div class="form-group">
        <label class="form-label">Sueldo</label>
        <input class="form-input" type="number" name="salary" step="0.01" min="0"
               value="{{ old('salary', $user->salary) }}" placeholder="0.00"/>
        @error('salary') <span class="form-error">{{ $message }}</span> @enderror
      </div>

      <div class="form-group">
        <label class="form-label">Bono de movilidad</label>
        <input class="form-input" type="number" name="mobility_bonus" step="0.01" min="0"
               value="{{ old('mobility_bonus', $user->mobility_bonus) }}" placeholder="0.00"/>
        @error('mobility_bonus') <span class="form-error">{{ $message }}</span> @enderror
      </div>

    </div>

    <div class="form-actions">
      <a href="{{ route('admin.users.index') }}" class="btn-cancel">Cancelar</a>
      <button type="submit" class="btn-save">Guardar cambios</button>
    </div>
  </form>
</div>

<script>
  const roleSelect = document.getElementById('roleSelect');
  const supervisorField = document.getElementById('supervisorField');

  function toggleSupervisor() {
    const rolesConSupervisor = ['supervisor', 'asesor'];
    supervisorField.style.display = rolesConSupervisor.includes(roleSelect.value) ? 'flex' : 'none';
  }

  roleSelect.addEventListener('change', toggleSupervisor);
  toggleSupervisor();
</script>
@endsection