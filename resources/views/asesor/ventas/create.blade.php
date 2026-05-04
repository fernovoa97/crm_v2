@extends('layouts.app')

@section('title', 'Nueva Venta')
@section('subtitle', $lead->razon_social . ' — RUC ' . $lead->ruc)

@section('topbar-actions')
  <a href="{{ route('asesor.leads.index') }}#prospectos" style="
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(255,255,255,0.05); color: rgba(255,255,255,0.5);
    border: 1px solid rgba(255,255,255,0.08);
    padding: 8px 16px; border-radius: 8px;
    font-size: 13px; font-weight: 600;
    text-decoration: none; transition: all 0.2s;
  ">
    ← Volver a prospectos
  </a>
@endsection

@section('content')
<style>
  .venta-layout {
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
    gap: 10px;
  }

  .card-header-title {
    font-size: 13px;
    font-weight: 600;
    color: #fff;
  }

  .card-header-badge {
    font-size: 10px;
    padding: 2px 8px;
    border-radius: 20px;
    font-weight: 600;
  }

  .card-body { padding: 20px; }

  /* Tipo selector */
  .tipo-selector {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-bottom: 0;
  }

  .tipo-card {
    padding: 16px;
    border-radius: 12px;
    border: 2px solid rgba(255,255,255,0.07);
    background: rgba(255,255,255,0.02);
    cursor: pointer;
    transition: all .2s;
    text-align: center;
  }

  .tipo-card:hover { border-color: rgba(47,202,245,0.3); }
  .tipo-card.active {
    border-color: #2FCAF5;
    background: rgba(47,202,245,0.08);
  }

  .tipo-card-icon { font-size: 28px; margin-bottom: 6px; }
  .tipo-card-name { font-size: 14px; font-weight: 600; color: #fff; }
  .tipo-card-desc { font-size: 11px; color: rgba(255,255,255,0.35); margin-top: 3px; }

  /* Bubble selectors */
  .bubble-group {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 6px;
  }

  .bubble {
    padding: 6px 14px;
    border-radius: 20px;
    border: 1px solid rgba(255,255,255,0.1);
    background: rgba(255,255,255,0.03);
    color: rgba(255,255,255,0.5);
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: all .15s;
    font-family: 'Sora', sans-serif;
    user-select: none;
  }

  .bubble:hover { border-color: rgba(47,202,245,0.3); color: #fff; }
  .bubble.active {
    border-color: #2FCAF5;
    background: rgba(47,202,245,0.12);
    color: #2FCAF5;
  }

  .bubble.active-green {
    border-color: #5dcaa5;
    background: rgba(29,158,117,0.12);
    color: #5dcaa5;
  }

  .bubble.active-orange {
    border-color: #fac775;
    background: rgba(239,159,39,0.12);
    color: #fac775;
  }

  /* Form fields */
  .form-group { margin-bottom: 14px; }
  .form-label {
    font-size: 11px;
    color: rgba(255,255,255,0.4);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .4px;
    display: block;
    margin-bottom: 6px;
  }

  .form-label .required { color: #ff9090; margin-left: 2px; }
  .form-label .hint {
    font-size: 10px;
    color: #fac775;
    text-transform: none;
    letter-spacing: 0;
    font-weight: 500;
    margin-left: 6px;
  }

  .form-input {
    width: 100%;
    box-sizing: border-box;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 8px;
    padding: 9px 12px;
    font-size: 13px;
    color: #fff;
    font-family: 'Sora', sans-serif;
    outline: none;
    transition: border .2s;
  }

  .form-input:focus { border-color: rgba(47,202,245,0.4); }
  .form-input::placeholder { color: rgba(255,255,255,0.2); }
  .form-input[readonly] {
    background: rgba(255,255,255,0.02);
    color: rgba(255,255,255,0.4);
    cursor: not-allowed;
  }

  .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
  .form-row-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; }

  .section-title {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .6px;
    color: rgba(255,255,255,0.25);
    border-top: 1px solid rgba(255,255,255,0.06);
    padding-top: 14px;
    margin: 18px 0 14px;
  }

  /* Alert biometría */
  .alert-biometria {
    background: rgba(239,159,39,0.1);
    border: 1px solid rgba(239,159,39,0.25);
    border-radius: 8px;
    padding: 10px 14px;
    font-size: 12px;
    color: #fac775;
    margin-bottom: 14px;
    display: none;
  }

  /* Planes fija */
  .planes-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
    margin-top: 6px;
  }

  .plan-check {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    border-radius: 8px;
    border: 1px solid rgba(255,255,255,0.07);
    cursor: pointer;
    transition: all .15s;
  }

  .plan-check:hover { border-color: rgba(47,202,245,0.3); }
  .plan-check.active {
    border-color: #2FCAF5;
    background: rgba(47,202,245,0.08);
  }

  .plan-check input[type="checkbox"] { display: none; }
  .plan-check-dot {
    width: 14px; height: 14px;
    border-radius: 4px;
    border: 1.5px solid rgba(255,255,255,0.2);
    flex-shrink: 0;
    transition: all .15s;
    display: flex; align-items: center; justify-content: center;
  }
  .plan-check.active .plan-check-dot {
    background: #2FCAF5;
    border-color: #2FCAF5;
  }
  .plan-check.active .plan-check-dot::after {
    content: '✓';
    font-size: 9px;
    color: #0f0f13;
    font-weight: 700;
  }
  .plan-check-name { font-size: 11px; color: rgba(255,255,255,0.7); }

  /* Tabla líneas móvil */
  .lineas-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
  }

  .lineas-table th {
    font-size: 10px;
    font-weight: 600;
    color: rgba(255,255,255,0.25);
    text-transform: uppercase;
    letter-spacing: .5px;
    padding: 8px 10px;
    text-align: left;
    border-bottom: 1px solid rgba(255,255,255,0.06);
    white-space: nowrap;
  }

  .lineas-table td {
    padding: 8px 6px;
    border-bottom: 1px solid rgba(255,255,255,0.04);
    vertical-align: middle;
  }

  .lineas-table tr:last-child td { border-bottom: none; }

  .linea-input {
    width: 100%;
    box-sizing: border-box;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 6px;
    padding: 6px 8px;
    font-size: 11px;
    color: #fff;
    font-family: 'Sora', sans-serif;
    outline: none;
  }

  .linea-input:focus { border-color: rgba(47,202,245,0.3); }

  .linea-select {
    width: 100%;
    box-sizing: border-box;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 6px;
    padding: 6px 8px;
    font-size: 11px;
    color: #fff;
    font-family: 'Sora', sans-serif;
    outline: none;
  }

  .linea-select option { background: #1a1a24; }

  .btn-add-linea {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 14px;
    border-radius: 8px;
    border: 1px dashed rgba(47,202,245,0.3);
    background: rgba(47,202,245,0.04);
    color: #2FCAF5;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    font-family: 'Sora', sans-serif;
    transition: all .15s;
    margin-top: 10px;
  }

  .btn-add-linea:hover { background: rgba(47,202,245,0.1); }

  .btn-remove-linea {
    background: none;
    border: none;
    color: rgba(255,80,80,0.5);
    cursor: pointer;
    font-size: 16px;
    padding: 0 4px;
    transition: color .15s;
  }

  .btn-remove-linea:hover { color: #ff9090; }

  /* Documentos */
  .doc-drop {
    border: 2px dashed rgba(255,255,255,0.1);
    border-radius: 10px;
    padding: 24px;
    text-align: center;
    cursor: pointer;
    transition: all .2s;
  }

  .doc-drop:hover {
    border-color: rgba(47,202,245,0.3);
    background: rgba(47,202,245,0.02);
  }

  .doc-drop p { font-size: 12px; color: rgba(255,255,255,0.3); margin-top: 8px; }
  .doc-list { margin-top: 10px; display: flex; flex-direction: column; gap: 6px; }
  .doc-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background: rgba(255,255,255,0.03);
    border-radius: 8px;
    border: 1px solid rgba(255,255,255,0.06);
    font-size: 12px;
    color: rgba(255,255,255,0.6);
  }

  .doc-item-remove {
    margin-left: auto;
    background: none;
    border: none;
    color: rgba(255,80,80,0.4);
    cursor: pointer;
    font-size: 16px;
  }

  /* Sidebar */
  .sidebar-card {
    background: #15151c;
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 14px;
    padding: 18px;
    margin-bottom: 14px;
    position: sticky;
    top: 20px;
  }

  .sidebar-title {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .6px;
    color: rgba(255,255,255,0.25);
    margin-bottom: 14px;
  }

  .sidebar-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 8px;
    margin-bottom: 10px;
    font-size: 12px;
  }

  .sidebar-label { color: rgba(255,255,255,0.35); }
  .sidebar-value { color: #fff; font-weight: 500; text-align: right; }

  .btn-submit {
    width: 100%;
    padding: 12px;
    border-radius: 10px;
    background: #2FCAF5;
    color: #0f0f13;
    border: none;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    font-family: 'Sora', sans-serif;
    transition: opacity .2s;
    margin-top: 4px;
  }

  .btn-submit:hover { opacity: .88; }

  .btn-submit-green {
    background: #5dcaa5;
  }

  /* Condicionales */
  .field-porta { display: none; }
  .field-fullclaro { display: none; }
  .field-dcto-plantilla { display: none; }

  /* CAC Dropdown */
  .cac-option {
    padding: 10px 14px;
    cursor: pointer;
    border-bottom: 1px solid rgba(255,255,255,0.05);
    transition: background .15s;
  }
  .cac-option:last-child { border-bottom: none; }
  .cac-option:hover { background: rgba(47,202,245,0.08); }
  .cac-option-nombre { font-size: 13px; color: #fff; font-weight: 500; }
  .cac-option-dir { font-size: 11px; color: rgba(255,255,255,0.35); margin-top:2px; }

  /* Light mode */
  html.light .card { background: #fff; border-color: #d0eaf8; }
  html.light .card-header { border-bottom-color: #e8f3fb; }
  html.light .card-header-title { color: #0f0f13; }
  html.light .tipo-card { border-color: #d0eaf8; background: #f8fcff; }
  html.light .tipo-card-name { color: #0f0f13; }
  html.light .bubble { border-color: #d0eaf8; color: rgba(0,0,0,0.5); background: #f8fcff; }
  html.light .form-label { color: rgba(0,0,0,0.5); }
  html.light .form-input { background: #f0f7ff; border-color: #c0dff5; color: #0f0f13; }
  html.light .form-input[readonly] { background: #e8f3fb; color: rgba(0,0,0,0.4); }
  html.light .section-title { color: rgba(0,0,0,0.3); border-top-color: #e8f3fb; }
  html.light .plan-check { border-color: #d0eaf8; }
  html.light .plan-check-name { color: rgba(0,0,0,0.6); }
  html.light .lineas-table th { color: rgba(0,0,0,0.3); border-bottom-color: #e8f3fb; }
  html.light .lineas-table td { border-bottom-color: #f0f7ff; }
  html.light .linea-input, html.light .linea-select { background: #f0f7ff; border-color: #c0dff5; color: #0f0f13; }
  html.light .sidebar-card { background: #fff; border-color: #d0eaf8; }
  html.light .sidebar-label { color: rgba(0,0,0,0.4); }
  html.light .sidebar-value { color: #0f0f13; }
  html.light .doc-drop { border-color: #d0eaf8; }
  html.light .doc-item { background: #f8fcff; border-color: #e0eef8; color: rgba(0,0,0,0.6); }
</style>

<form method="POST" action="{{ route('asesor.ventas.store') }}" enctype="multipart/form-data" id="formVenta">
@csrf
<input type="hidden" name="lead_id" value="{{ $lead->id }}">
<input type="hidden" name="tipo" id="inputTipo" value="">

<div class="venta-layout">

  {{-- ── COLUMNA PRINCIPAL ── --}}
  <div>

    {{-- PASO 1: TIPO --}}
    <div class="card">
      <div class="card-header">
        <div class="card-header-title">Paso 1 — Tipo de servicio</div>
      </div>
      <div class="card-body">
        <div class="tipo-selector">
          <div class="tipo-card" id="tipoMovil" onclick="setTipo('movil')">
            <div class="tipo-card-icon">📱</div>
            <div class="tipo-card-name">Móvil</div>
            <div class="tipo-card-desc">Líneas, portabilidades, renovaciones</div>
          </div>
          <div class="tipo-card" id="tipoFija" onclick="setTipo('fija')">
            <div class="tipo-card-icon">🏢</div>
            <div class="tipo-card-name">Fija / Internet</div>
            <div class="tipo-card-desc">Internet, telefonía fija, cable</div>
          </div>
        </div>
      </div>
    </div>

    {{-- PASO 2: TIPO DE INGRESO Y TIPO DE VENTA --}}
    <div class="card" id="paso2" style="display:none;">
      <div class="card-header">
        <div class="card-header-title">Paso 2 — Ingreso y tipo de venta</div>
      </div>
      <div class="card-body">
        <div class="form-group">
          <label class="form-label">Tipo de ingreso <span class="required">*</span></label>
          <div class="bubble-group">
            <div class="bubble" onclick="setBubble(this, 'tipo_ingreso', 'pdv'); onTipoIngresoChange('pdv')">PDV</div>
            <div class="bubble" onclick="setBubble(this, 'tipo_ingreso', 'centralizado'); onTipoIngresoChange('centralizado')">Centralizado</div>
            <div class="bubble" onclick="setBubble(this, 'tipo_ingreso', 'almacen_propio'); onTipoIngresoChange('almacen_propio')">Almacén Propio</div>
          </div>
          <input type="hidden" name="tipo_ingreso" id="inputTipoIngreso">
        </div>

        {{-- Tipo venta móvil --}}
        <div class="form-group" id="grupoTipoVentaMovil" style="display:none;">
          <label class="form-label">Tipo de venta <span class="required">*</span></label>
          <div class="bubble-group">
            <div class="bubble" onclick="setBubble(this, 'tipo_venta_movil', 'alta'); togglePortaMovil('alta')">Alta nueva</div>
            <div class="bubble" onclick="setBubble(this, 'tipo_venta_movil', 'porta'); togglePortaMovil('porta')">Portabilidad</div>
            <div class="bubble" onclick="setBubble(this, 'tipo_venta_movil', 'renovacion'); togglePortaMovil('renovacion')">Renovación</div>
          </div>
          <input type="hidden" name="tipo_venta_movil" id="inputTipoVentaMovil">
        </div>

        {{-- Tipo venta fija --}}
        <div class="form-group" id="grupoTipoVentaFija" style="display:none;">
          <label class="form-label">Tipo de venta <span class="required">*</span></label>
          <div class="bubble-group">
            <div class="bubble" onclick="setBubble(this, 'tipo_venta_fija', 'alta'); togglePortaFija(false)">Alta</div>
            <div class="bubble" onclick="setBubble(this, 'tipo_venta_fija', 'porta'); togglePortaFija(true)">Portabilidad</div>
          </div>
          <input type="hidden" name="tipo_venta_fija" id="inputTipoVentaFija">
        </div>

        {{-- B5: Campos extra para porta fija --}}
        <div id="grupoPortaFija" style="display:none;">
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Operador cedente <span class="required">*</span></label>
              <select name="operador_cedente_fija" class="form-input" style="background:rgba(255,255,255,0.05);">
                <option value="">— Seleccionar —</option>
                <option value="movistar">Movistar</option>
                <option value="entel">Entel</option>
                <option value="win">Win</option>
                <option value="otros">Otros</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Teléfono fijo a migrar <span class="required">*</span></label>
              <input type="text" name="telefono_fijo_migrar" class="form-input" placeholder="01 XXXXXXX">
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- PASO 3: DATOS DEL CLIENTE --}}
    <div class="card" id="paso3" style="display:none;">
      <div class="card-header">
        <div class="card-header-title">Paso 3 — Datos del cliente</div>
      </div>
      <div class="card-body">

        <div class="alert-biometria" id="alertBiometria">
          ⚠️ <strong>Importante:</strong> El representante ingresado debe ser quien pasará la <strong>biometría</strong> al momento de la portabilidad o alta.
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">RUC</label>
            <input type="text" class="form-input" value="{{ $lead->ruc }}" readonly>
          </div>
          <div class="form-group">
            <label class="form-label">Razón Social</label>
            <input type="text" class="form-input" value="{{ $lead->razon_social }}" readonly>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">
            Nombre del representante <span class="required">*</span>
            <span class="hint" id="hintBiometria" style="display:none;">👆 Quien pasará la biometría</span>
          </label>
          <input type="text" name="nombre_representante" class="form-input"
                 value="{{ $lead->nombre_rl }}" placeholder="Nombre completo del representante">
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Tipo de documento <span class="required">*</span></label>
            <div class="bubble-group">
              <div class="bubble" onclick="setBubble(this, 'tipo_documento', 'dni'); setDocLimit('dni')">DNI</div>
              <div class="bubble" onclick="setBubble(this, 'tipo_documento', 'ce'); setDocLimit('ce')">CE</div>
            </div>
            <input type="hidden" name="tipo_documento" id="inputTipoDoc">
          </div>
          <div class="form-group">
            <label class="form-label">N° de documento <span class="required">*</span></label>
            <input type="text" name="nro_documento" id="inputNroDoc" class="form-input"
                   maxlength="8" placeholder="8 dígitos">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">
              Teléfono del representante / titular <span class="required">*</span>
            </label>
            <input type="text" name="telefono_representante" class="form-input"
                   value="{{ $lead->telf1 }}" placeholder="9XX XXX XXX">
          </div>
          <div class="form-group" id="grupoTelfSot" style="display:none;">
            <label class="form-label">Teléfono para SOT <span class="hint">(técnico instalación)</span></label>
            <input type="text" name="telefono_sot" class="form-input" placeholder="9XX XXX XXX">
          </div>
          <div class="form-group" id="grupoTelfBiometria" style="display:none;">
            <label class="form-label">Teléfono para motorizado de delivery</label>
            <input type="text" name="telefono_referencia_movil" class="form-input" placeholder="9XX XXX XXX">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Correo electrónico <span class="required">*</span></label>
            <input type="email" name="correo" class="form-input"
                   value="{{ $lead->correo_rl }}" placeholder="correo@empresa.com">
          </div>
          <div class="form-group" style="display:none;">
            {{-- N° SEC exclusivo para Mesa de Control --}}
            <label class="form-label">N° de SEC</label>
            <input type="text" name="nro_sec" class="form-input" placeholder="Número SEC" readonly>
          </div>
        </div>
      </div>
    </div>

    {{-- PASO 4A: CAMPOS FIJA --}}
    <div class="card" id="pasoFija" style="display:none;">
      <div class="card-header">
        <div class="card-header-title">Paso 4 — Datos del servicio fijo</div>
      </div>
      <div class="card-body">

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Coordenadas de cobertura <span class="hint">(factibilidad)</span></label>
            <input type="text" name="coordenadas_cobertura" class="form-input" placeholder="-12.0464, -77.0428">
          </div>
          <div class="form-group">
            <label class="form-label">Plano de cobertura <span class="hint">(factibilidad)</span></label>
            <input type="text" name="plano_cobertura" class="form-input" placeholder="URL o referencia">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Dirección de instalación <span class="required">*</span></label>
          <input type="text" name="direccion_instalacion" class="form-input" placeholder="Av. / Jr. / Calle...">
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Referencia de dirección</label>
            <input type="text" name="referencia_direccion_instalacion" class="form-input" placeholder="Cerca a...">
          </div>
          <div class="form-group">
            <label class="form-label">Dirección de facturación</label>
            <input type="text" name="direccion_facturacion_fija" class="form-input" placeholder="Si es diferente a instalación">
          </div>
        </div>

        <div class="section-title">Tecnología y campaña</div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Tecnología <span class="required">*</span></label>
            <div class="bubble-group">
              <div class="bubble" onclick="setBubble(this, 'tecnologia', 'hfc')">HFC</div>
              <div class="bubble" onclick="setBubble(this, 'tecnologia', 'ftth')">FTTH</div>
            </div>
            <input type="hidden" name="tecnologia" id="inputTecnologia">
          </div>
          <div class="form-group">
            <label class="form-label">Campaña <span class="required">*</span></label>
            <div class="bubble-group">
              <div class="bubble" onclick="setBubble(this, 'campana_fija', 'regular')">Regular</div>
              <div class="bubble" onclick="setBubble(this, 'campana_fija', '1_sol')">1 Sol</div>
              <div class="bubble" onclick="setBubble(this, 'campana_fija', 'empresas_medio')">Empresas Medio</div>
              <div class="bubble" onclick="setBubble(this, 'campana_fija', 'empresas_basico')">Empresas Básico</div>
              <div class="bubble" onclick="setBubble(this, 'campana_fija', 'empresas_grande')">Empresas Grande</div>
              <div class="bubble" onclick="setBubble(this, 'campana_fija', 'relampago')">Relámpago</div>
            </div>
            <input type="hidden" name="campana_fija" id="inputCampanaFija">
          </div>
        </div>

        <div class="section-title">Tipo de producto y planes</div>

        <div class="form-group">
          <label class="form-label">Tipo de producto <span class="required">*</span></label>
          <div class="bubble-group">
            <div class="bubble" onclick="setBubble(this, 'tipo_producto_fija', '1play'); renderCombosPlay('1play')">1Play</div>
            <div class="bubble" onclick="setBubble(this, 'tipo_producto_fija', '2play'); renderCombosPlay('2play')">2Play</div>
            <div class="bubble" onclick="setBubble(this, 'tipo_producto_fija', '3play'); renderCombosPlay('3play')">3Play</div>
          </div>
          <input type="hidden" name="tipo_producto_fija" id="inputTipoProducto">
          <div style="font-size:11px;color:rgba(255,255,255,0.25);margin-top:6px;" id="hintPlay">
            1Play: cualquier servicio · 2Play: internet + (telf o cable) · 3Play: internet + telf + cable
          </div>
        </div>

        {{-- Combos dinámicos según play --}}
        <div class="form-group" id="grupoCombosPlay" style="display:none;">
          <label class="form-label">Planes <span class="required">*</span></label>
          <div id="combosPlayContainer"></div>

          {{-- Checkboxes hidden — se activan via JS según selección de combos --}}
          <input type="hidden" name="plan_telefonia"      id="hPlanTelefonia"    value="0">
          <input type="hidden" name="plan_cable_standar"  id="hPlanCableStandar" value="0">
          <input type="hidden" name="plan_cable_superior" id="hPlanCableSup"     value="0">
          <input type="hidden" name="plan_internet_200"   id="hPlanInt200"       value="0">
          <input type="hidden" name="plan_internet_400"   id="hPlanInt400"       value="0">
          <input type="hidden" name="plan_internet_1500"  id="hPlanInt1500"      value="0">
        </div>

        <div class="section-title">Adicionales</div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Cantidad de DECOs</label>
            <input type="number" name="cantidad_decos" class="form-input" min="0" value="0" placeholder="0">
          </div>
          <div class="form-group">
            <label class="form-label">Cantidad de repetidores</label>
            <input type="number" name="cantidad_repetidores" class="form-input" min="0" value="0" placeholder="0">
          </div>
        </div>

        <div class="section-title">Otros datos</div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Bono</label>
            <input type="text" name="bono_fija" class="form-input" placeholder="Descripción del bono">
          </div>
          <div class="form-group">
            <label class="form-label">Descuento</label>
            <input type="text" name="descuento_fija" class="form-input" placeholder="Descripción del descuento">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Full Claro</label>
          <div class="bubble-group">
            <div class="bubble" onclick="setBubble(this, 'full_claro', 'aplica'); toggleFullClaro(true)">Aplica</div>
            <div class="bubble" onclick="setBubble(this, 'full_claro', 'no_aplica'); toggleFullClaro(false)">No aplica</div>
          </div>
          <input type="hidden" name="full_claro" id="inputFullClaro">
        </div>

        <div class="form-group field-fullclaro" id="grupoFullClaro">
          <label class="form-label">N° móvil Full Claro <span class="hint">(opcional)</span></label>
          <input type="text" name="nro_movil_fullclaro" class="form-input" placeholder="9XX XXX XXX">
        </div>

      </div>
    </div>

    {{-- PASO 4B: CAMPOS MÓVIL --}}
    <div class="card" id="pasoMovil" style="display:none;">
      <div class="card-header">
        <div class="card-header-title">Paso 4 — Datos del servicio móvil</div>
      </div>
      <div class="card-body">

        <div class="form-group">
          <label class="form-label">Tipo de entrega <span class="required">*</span></label>
          <div class="bubble-group">
            <div class="bubble" onclick="setBubble(this, 'tipo_entrega', 'delivery'); onTipoEntregaChange('delivery')">Delivery</div>
            <div class="bubble" onclick="setBubble(this, 'tipo_entrega', 'recojo_cac'); onTipoEntregaChange('recojo_cac')">Recojo en CAC</div>
          </div>
          <input type="hidden" name="tipo_entrega" id="inputTipoEntrega">
          <input type="hidden" name="cac_id" id="inputCacId">
        </div>

        {{-- Bloque DELIVERY --}}
        <div id="grupoDelivery">
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">
                Coordenadas de delivery
                <span class="hint">pega ambas y se separan solas</span>
              </label>
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                <input type="text" id="inputGeoX" class="form-input" placeholder="Lat: -12.0464"
                       oninput="syncGeodirCoords()" onpaste="setTimeout(splitGeodirPaste,10)">
                <input type="text" id="inputGeoY" class="form-input" placeholder="Lng: -77.0428"
                       oninput="syncGeodirCoords()">
              </div>
              <input type="hidden" name="coordenadas_geodir" id="inputCoordsGeodirFinal">
            </div>
            <div class="form-group">
              <label class="form-label">Plano de entrega de delivery</label>
              <input type="text" name="plano_geodir" class="form-input" placeholder="URL o referencia">
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Dirección de entrega <span class="required">*</span></label>
            <input type="text" name="direccion_entrega" class="form-input" placeholder="Av. / Jr. / Calle...">
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Referencias del punto de entrega</label>
              <input type="text" name="referencias_entrega" class="form-input" placeholder="Cerca a...">
            </div>
            <div class="form-group">
              <label class="form-label">Dirección de facturación</label>
              <input type="text" name="direccion_facturacion_movil" class="form-input" placeholder="Si es diferente">
            </div>
          </div>
        </div>

        {{-- Bloque RECOJO EN CAC --}}
        <div id="grupoCac" style="display:none;">
          <div class="form-group" style="position:relative;">
            <label class="form-label">Buscar CAC <span class="required">*</span></label>
            <input type="text" id="inputCacBusqueda" class="form-input"
                   placeholder="Escribe nombre o dirección del CAC..."
                   autocomplete="off" oninput="buscarCac(this.value)">
            <div id="cacDropdown" style="
              display:none; position:absolute; z-index:100;
              background:#1e1e2a; border:1px solid rgba(255,255,255,0.1);
              border-radius:8px; margin-top:4px; max-height:200px;
              overflow-y:auto; min-width:300px;
            "></div>
          </div>
          <div id="grupoCacSeleccionado" style="display:none;">
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">CAC seleccionado</label>
                <input type="text" id="inputCacNombre" class="form-input" readonly
                       style="background:rgba(29,158,117,0.08);border-color:rgba(29,158,117,0.25);color:#5dcaa5;">
              </div>
              <div class="form-group">
                <label class="form-label">Dirección del CAC</label>
                <input type="text" id="inputCacDireccion" class="form-input" readonly
                       style="background:rgba(255,255,255,0.02);color:rgba(255,255,255,0.5);">
              </div>
            </div>
          </div>
          <div class="form-group" style="margin-top:4px;">
            <label class="form-label">Dirección de facturación</label>
            <input type="text" name="direccion_facturacion_movil" class="form-input" placeholder="Si es diferente">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Campaña <span class="required">*</span></label>
          <div class="bubble-group">
            <div class="bubble" onclick="setBubble(this, 'campana_movil', 'claro_negocios')">Claro Negocios</div>
            <div class="bubble" onclick="setBubble(this, 'campana_movil', 'claro_emprendedor')">Claro Emprendedor</div>
          </div>
          <input type="hidden" name="campana_movil" id="inputCampanaMovil">
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Fecha de despacho <span class="required">*</span></label>
            <input type="date" name="fecha_despacho" class="form-input" id="inputFechaDespacho">
          </div>
          <div class="form-group">
            <label class="form-label">Rango horario <span class="required">*</span></label>
            <div class="bubble-group">
              <div class="bubble" id="bubbleSla3h" onclick="setBubble(this, 'rango_horario', 'sla_3h')">SLA 3H</div>
              <div class="bubble" onclick="setBubble(this, 'rango_horario', '9-11')">9–11am</div>
              <div class="bubble" onclick="setBubble(this, 'rango_horario', '11-1')">11am–1pm</div>
              <div class="bubble" onclick="setBubble(this, 'rango_horario', '2-4')">2–4pm</div>
              <div class="bubble" onclick="setBubble(this, 'rango_horario', '4-6')">4–6pm</div>
            </div>
            <input type="hidden" name="rango_horario" id="inputRangoHorario">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Comentario de despacho <span class="hint">casos solo SEC u observaciones</span></label>
          <input type="text" name="comentario_despacho" class="form-input" placeholder="Ej: Solo SEC, coordinar con...">
        </div>

        <div class="section-title">Líneas solicitadas</div>

        <table class="lineas-table" id="tablLineas">
          <thead>
            <tr>
              <th>N° a portar</th>
              <th>Plan</th>
              <th>Operador cedente</th>
              <th>Equipo / SIM</th>
              <th>Descuento</th>
              <th>N° WF</th>
              <th>Large</th>
              <th></th>
            </tr>
          </thead>
          <tbody id="lineasBody">
            {{-- Se agregan dinámicamente --}}
          </tbody>
        </table>

        <button type="button" class="btn-add-linea" onclick="addLinea()">
          + Agregar línea
        </button>

      </div>
    </div>

    {{-- PASO 5: DOCUMENTOS --}}
    <div class="card" id="paso5" style="display:none;">
      <div class="card-header">
        <div class="card-header-title">Paso 5 — Documentos adjuntos</div>
        <span class="card-header-badge" style="background:rgba(255,255,255,0.07);color:rgba(255,255,255,0.35);">Opcional</span>
      </div>
      <div class="card-body">
        <div class="doc-drop" onclick="document.getElementById('inputDocs').click()">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.2)" stroke-width="1.5" style="margin:0 auto;">
            <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
            <polyline points="17 8 12 3 7 8"/>
            <line x1="12" y1="3" x2="12" y2="15"/>
          </svg>
          <p>Haz clic para adjuntar DNI, PDFs, Excel, etc.</p>
          <input type="file" id="inputDocs" name="documentos[]" multiple
                 accept=".pdf,.xlsx,.xls,.csv,.jpg,.jpeg,.png,.doc,.docx"
                 style="display:none" onchange="previewDocs(this)">
        </div>
        <div class="doc-list" id="docList"></div>
      </div>
    </div>

  </div>

  {{-- ── SIDEBAR ── --}}
  <div>
    <div class="sidebar-card">
      <div class="sidebar-title">Resumen del lead</div>
      <div class="sidebar-row">
        <span class="sidebar-label">RUC</span>
        <span class="sidebar-value">{{ $lead->ruc }}</span>
      </div>
      <div class="sidebar-row">
        <span class="sidebar-label">Empresa</span>
        <span class="sidebar-value">{{ $lead->razon_social }}</span>
      </div>
      <div class="sidebar-row">
        <span class="sidebar-label">Representante</span>
        <span class="sidebar-value">{{ $lead->nombre_rl ?? '—' }}</span>
      </div>
      <div class="sidebar-row">
        <span class="sidebar-label">Teléfono</span>
        <span class="sidebar-value">{{ $lead->telf1 ?? '—' }}</span>
      </div>
      <div class="sidebar-row">
        <span class="sidebar-label">Segmento</span>
        <span class="sidebar-value">{{ ucfirst($lead->segmento ?? '—') }}</span>
      </div>
      <div class="sidebar-row">
        <span class="sidebar-label">Departamento</span>
        <span class="sidebar-value">{{ $lead->departamento ?? '—' }}</span>
      </div>
    </div>

    <div class="sidebar-card" id="sidebarVentas" style="display:none;">
      <div class="sidebar-title">Ventas anteriores</div>
      @forelse($lead->ventas as $v)
        <div class="sidebar-row">
          <span class="sidebar-label">{{ ucfirst($v->tipo) }} — {{ $v->created_at->format('d/m/Y') }}</span>
          <span class="sidebar-value" style="color:
            @if($v->estado === 'completada') #5dcaa5
            @elseif($v->estado === 'rechazada') #ff9090
            @else #fac775
            @endif
          ">{{ ucfirst($v->estado) }}</span>
        </div>
      @empty
        <div style="font-size:12px;color:rgba(255,255,255,0.25);">Sin ventas anteriores</div>
      @endforelse
    </div>

    <div class="sidebar-card">
      <button type="submit" class="btn-submit" id="btnSubmit" disabled>
        Enviar a Mesa de Control
      </button>
      <div style="font-size:11px;color:rgba(255,255,255,0.25);text-align:center;margin-top:8px;">
        Completa todos los campos requeridos
      </div>
    </div>
  </div>

</div>
</form>

<script>
// ── ESTADO ────────────────────────────────
let tipoActual = null;
const hiddenInputs = {};

// ── TIPO DE SERVICIO ─────────────────────
function setTipo(tipo) {
  tipoActual = tipo;
  document.getElementById('inputTipo').value = tipo;

  document.getElementById('tipoMovil').classList.toggle('active', tipo === 'movil');
  document.getElementById('tipoFija').classList.toggle('active',  tipo === 'fija');

  // Mostrar pasos
  document.getElementById('paso2').style.display   = 'block';
  document.getElementById('paso3').style.display   = 'block';
  document.getElementById('paso5').style.display   = 'block';
  document.getElementById('sidebarVentas').style.display = 'block';

  document.getElementById('pasoFija').style.display  = tipo === 'fija'  ? 'block' : 'none';
  document.getElementById('pasoMovil').style.display = tipo === 'movil' ? 'block' : 'none';

  document.getElementById('grupoTipoVentaMovil').style.display = tipo === 'movil' ? 'block' : 'none';
  document.getElementById('grupoTipoVentaFija').style.display  = tipo === 'fija'  ? 'block' : 'none';

  // B4: Fija solo admite PDV — ocultar Centralizado y Almacén Propio
  document.querySelector('.bubble[onclick*="centralizado"]').style.display  = tipo === 'fija' ? 'none' : '';
  document.querySelector('.bubble[onclick*="almacen_propio"]').style.display = tipo === 'fija' ? 'none' : '';

  // Teléfonos según tipo
  document.getElementById('grupoTelfSot').style.display       = tipo === 'fija'  ? 'block' : 'none';
  document.getElementById('grupoTelfBiometria').style.display = tipo === 'movil' ? 'block' : 'none';

  // Alerta biometría
  document.getElementById('alertBiometria').style.display = tipo === 'movil' ? 'block' : 'none';
  document.getElementById('hintBiometria').style.display  = tipo === 'movil' ? 'inline' : 'none';

  // Agregar primera línea si móvil
  if (tipo === 'movil' && document.getElementById('lineasBody').children.length === 0) {
    addLinea();
  }

  // ── DEFAULTS POR TIPO ─────────────────────
  if (tipo === 'fija') {
    // A4: PDV + ALTA por default en fija
    const pdvBubbleFija = document.querySelector('.bubble[onclick*="tipo_ingreso"][onclick*="pdv"]');
    if (pdvBubbleFija) setBubble(pdvBubbleFija, 'tipo_ingreso', 'pdv');
    const altaBubbleFija = document.querySelector('#grupoTipoVentaFija .bubble[onclick*="alta"]');
    if (altaBubbleFija) { setBubble(altaBubbleFija, 'tipo_venta_fija', 'alta'); togglePortaFija(false); }

    // A6: Tecnología FTTH + Campaña 1 Sol por default
    setTimeout(() => {
      const ftthBubble = document.querySelector('.bubble[onclick*="ftth"]');
      if (ftthBubble && !ftthBubble.classList.contains('active')) setBubble(ftthBubble, 'tecnologia', 'ftth');
      const solBubble = document.querySelector('.bubble[onclick*="1_sol"]');
      if (solBubble && !solBubble.classList.contains('active')) setBubble(solBubble, 'campana_fija', '1_sol');
    }, 50);
  }

  if (tipo === 'movil') {
    // A5: PDV + PORTA por default en móvil
    const pdvBubbleMovil = document.querySelector('.bubble[onclick*="tipo_ingreso"][onclick*="pdv"]');
    if (pdvBubbleMovil) setBubble(pdvBubbleMovil, 'tipo_ingreso', 'pdv');
    onTipoIngresoChange('pdv');
    const portaBubble = document.querySelector('#grupoTipoVentaMovil .bubble[onclick*="porta"]');
    if (portaBubble) { setBubble(portaBubble, 'tipo_venta_movil', 'porta'); togglePortaMovil('porta'); }

    // A3: Fecha despacho = hoy + SLA3H activo por default
    const hoy = new Date().toISOString().split('T')[0];
    document.getElementById('inputFechaDespacho').value = hoy;
    const sla3h = document.getElementById('bubbleSla3h');
    if (sla3h && !sla3h.classList.contains('active')) setBubble(sla3h, 'rango_horario', 'sla_3h');
  }

  checkSubmit();
}

// ── BUBBLES ───────────────────────────────
function setBubble(el, name, value) {
  // Desactivar hermanos del mismo grupo
  el.closest('.bubble-group').querySelectorAll('.bubble').forEach(b => {
    b.classList.remove('active', 'active-green', 'active-orange');
  });
  el.classList.add('active');

  // Actualizar hidden input
  let input = document.querySelector(`input[name="${name}"]`);
  if (input) input.value = value;

  checkSubmit();
}

// ── TIPO DOCUMENTO ────────────────────────
function setDocLimit(tipo) {
  const input = document.getElementById('inputNroDoc');
  if (tipo === 'dni') {
    input.maxLength = 8;
    input.placeholder = '8 dígitos';
  } else {
    input.maxLength = 9;
    input.placeholder = '00 + 7 dígitos';
  }
}

// ── COORDENADAS GEODIR (split X/Y) ──────────
function splitGeodirPaste() {
  const xInput = document.getElementById('inputGeoX');
  const yInput = document.getElementById('inputGeoY');
  const raw = xInput.value.trim();

  // Detectar si pegaron "lat, lng" juntos en el campo X
  const match = raw.match(/^(-?\d+\.?\d*)[,\s]+(-?\d+\.?\d*)$/);
  if (match) {
    xInput.value = match[1];
    yInput.value = match[2];
  }
  syncGeodirCoords();
}

function syncGeodirCoords() {
  const x = document.getElementById('inputGeoX').value.trim();
  const y = document.getElementById('inputGeoY').value.trim();
  document.getElementById('inputCoordsGeodirFinal').value = (x && y) ? `${x}, ${y}` : (x || y);
}

// ── PORTA MÓVIL ───────────────────────────
function togglePortaMovil(tipo) {
  // En porta: mostrar columna operador cedente y nro a portar
  const esPorta = tipo === 'porta';
  document.querySelectorAll('.col-porta').forEach(el => {
    el.style.display = esPorta ? '' : 'none';
  });
  // En alta: mostrar large asociada
  const esAlta = tipo === 'alta';
  document.querySelectorAll('.col-large').forEach(el => {
    el.style.display = esAlta ? '' : 'none';
  });
  // Actualizar filas existentes
  document.querySelectorAll('#lineasBody tr').forEach(tr => {
    tr.querySelector('.td-porta').style.display = esPorta ? '' : 'none';
    tr.querySelector('.td-large').style.display = esAlta ? '' : 'none';
  });
}

// ── TIPO ENTREGA (delivery vs recojo CAC) ────
function onTipoEntregaChange(valor) {
  const esCAC = valor === 'recojo_cac';
  document.getElementById('grupoDelivery').style.display = esCAC ? 'none' : 'block';
  document.getElementById('grupoCac').style.display      = esCAC ? 'block' : 'none';

  // Si cambia a delivery, limpiar selección CAC
  if (!esCAC) {
    document.getElementById('inputCacBusqueda').value = '';
    document.getElementById('inputCacId').value = '';
    document.getElementById('grupoCacSeleccionado').style.display = 'none';
    document.getElementById('cacDropdown').style.display = 'none';
  }
}

// ── BÚSQUEDA CAC ──────────────────────────
let cacTimer = null;

function buscarCac(q) {
  clearTimeout(cacTimer);
  const dropdown = document.getElementById('cacDropdown');

  if (q.length < 2) { dropdown.style.display = 'none'; return; }

  cacTimer = setTimeout(async () => {
    try {
      const res  = await fetch(`/admin/cacs/search?q=${encodeURIComponent(q)}`);
      const data = await res.json();
      dropdown.innerHTML = '';

      if (!data.length) {
        dropdown.innerHTML = '<div class="cac-option"><span class="cac-option-nombre" style="color:rgba(255,255,255,0.3);">Sin resultados</span></div>';
      } else {
        data.forEach(cac => {
          const div = document.createElement('div');
          div.className = 'cac-option';
          div.innerHTML = `<div class="cac-option-nombre">${cac.nombre}</div><div class="cac-option-dir">${cac.direccion}</div>`;
          div.onclick = () => seleccionarCac(cac);
          dropdown.appendChild(div);
        });
      }
      dropdown.style.display = 'block';
    } catch(e) {
      console.error('Error buscando CAC:', e);
    }
  }, 300);
}

function seleccionarCac(cac) {
  document.getElementById('inputCacId').value       = cac.id;
  document.getElementById('inputCacBusqueda').value = cac.nombre;
  document.getElementById('inputCacNombre').value   = cac.nombre;
  document.getElementById('inputCacDireccion').value = cac.direccion;
  document.getElementById('grupoCacSeleccionado').style.display = 'block';
  document.getElementById('cacDropdown').style.display = 'none';
}

// Cerrar dropdown al hacer click fuera
document.addEventListener('click', e => {
  if (!e.target.closest('#inputCacBusqueda') && !e.target.closest('#cacDropdown')) {
    document.getElementById('cacDropdown').style.display = 'none';
  }
});

// ── PORTA FIJA ────────────────────────────
function togglePortaFija(esPorta) {
  document.getElementById('grupoPortaFija').style.display = esPorta ? 'block' : 'none';
}

// ── FULL CLARO ────────────────────────────
function toggleFullClaro(aplica) {
  document.getElementById('grupoFullClaro').style.display = aplica ? 'block' : 'none';
}

// ── PLANES FIJA ───────────────────────────
function togglePlan(el, name) {
  el.classList.toggle('active');
  const cb = el.querySelector('input[type="checkbox"]');
  cb.checked = el.classList.contains('active');
  checkSubmit();
}

// ── COMBOS PLANES FIJA ───────────────────────
const PLANES = {
  internet:  [
    { label: 'Internet 200MB',  field: 'hPlanInt200'  },
    { label: 'Internet 400MB',  field: 'hPlanInt400'  },
    { label: 'Internet 1500MB', field: 'hPlanInt1500' },
  ],
  telefonia: [
    { label: 'Telefonía 5000',  field: 'hPlanTelefonia' },
  ],
  cable: [
    { label: 'Cable TV Estándar', field: 'hPlanCableStandar' },
    { label: 'Cable TV Superior', field: 'hPlanCableSup'     },
  ],
};

// Todos los campos de plan a 0 antes de recalcular
function resetPlanHiddens() {
  ['hPlanTelefonia','hPlanCableStandar','hPlanCableSup',
   'hPlanInt200','hPlanInt400','hPlanInt1500'].forEach(id => {
    document.getElementById(id).value = '0';
  });
}

function buildComboSelect(label, opciones, comboIndex, required) {
  const reqMark = required ? '<span style="color:#ff9090;margin-left:2px;">*</span>' : '';
  const opts = opciones.map(o =>
    `<option value="${o.field}">${o.label}</option>`
  ).join('');
  return `
    <div class="form-group" style="margin-bottom:10px;">
      <label class="form-label" style="font-size:10px;">${label}${reqMark}</label>
      <select class="form-input combo-plan" data-idx="${comboIndex}"
              style="background:rgba(255,255,255,0.05);"
              onchange="onComboChange()">
        <option value="">— Seleccionar —</option>
        ${opts}
      </select>
    </div>`;
}

function renderCombosPlay(play) {
  const container = document.getElementById('combosPlayContainer');
  const grupo     = document.getElementById('grupoCombosPlay');
  const hint      = document.getElementById('hintPlay');

  resetPlanHiddens();
  container.innerHTML = '';
  grupo.style.display = 'block';

  const hints = {
    '1play': '1Play — elige 1 servicio',
    '2play': '2Play — Internet obligatorio + Telefonía o Cable',
    '3play': '3Play — Internet + Telefonía + Cable',
  };
  hint.textContent = hints[play] || '';

  if (play === '1play') {
    // Un combo con todas las opciones
    const todas = [...PLANES.internet, ...PLANES.telefonia, ...PLANES.cable];
    container.innerHTML = buildComboSelect('Servicio', todas, 0, true);
  }

  if (play === '2play') {
    // Combo 1: Internet (obligatorio) | Combo 2: Telefonía o Cable
    container.innerHTML =
      buildComboSelect('Internet (obligatorio)', PLANES.internet, 0, true) +
      buildComboSelect('Telefonía o Cable', [...PLANES.telefonia, ...PLANES.cable], 1, true);
  }

  if (play === '3play') {
    // Combo 1: Internet | Combo 2: Telefonía | Combo 3: Cable
    container.innerHTML =
      buildComboSelect('Internet (obligatorio)', PLANES.internet,  0, true) +
      buildComboSelect('Telefonía',              PLANES.telefonia, 1, true) +
      buildComboSelect('Cable TV',               PLANES.cable,     2, true);
  }

  onComboChange();
}

function onComboChange() {
  resetPlanHiddens();
  document.querySelectorAll('.combo-plan').forEach(sel => {
    if (sel.value) {
      const el = document.getElementById(sel.value);
      if (el) el.value = '1';
    }
  });
  checkSubmit();
}

// ── LÍNEAS MÓVIL ──────────────────────────
let lineaCount = 0;

function addLinea() {
  const i = lineaCount++;
  const tipoVenta = document.getElementById('inputTipoVentaMovil')?.value || '';
  const esPorta   = tipoVenta === 'porta';
  const esAlta    = tipoVenta === 'alta';

  const tr = document.createElement('tr');
  tr.innerHTML = `
    <td class="td-porta" style="${esPorta ? '' : 'display:none'}">
      <input type="text" name="lineas[${i}][nro_portar]" class="linea-input" placeholder="9XX XXX XXX">
    </td>
    <td>
      <select name="lineas[${i}][plan]" class="linea-select">
        <option value="">— Plan —</option>
        <option value="max_negocios_29.90">MN +29.90</option>
        <option value="max_negocios_39.90">MN +39.90</option>
        <option value="max_negocios_49.90">MN +49.90</option>
        <option value="max_ilimitado_55.90">MI +55.90</option>
        <option value="max_ilimitado_69.90">MI +69.90</option>
        <option value="max_ilimitado_79.90">MI +79.90</option>
        <option value="max_ilimitado_95.90">MI +95.90</option>
        <option value="max_ilimitado_109.90">MI +109.90</option>
        <option value="max_ilimitado_125.00">MI +125.00</option>
        <option value="max_ilimitado_159.90">MI +159.90</option>
        <option value="max_ilimitado_189.90">MI +189.90</option>
        <option value="max_ilimitado_289.90">MI +289.90</option>
      </select>
    </td>
    <td class="td-porta" style="${esPorta ? '' : 'display:none'}">
      <select name="lineas[${i}][operador_cedente]" class="linea-select">
        <option value="">— Op. —</option>
        <option value="entel">Entel</option>
        <option value="movistar">Movistar</option>
        <option value="bitel">Bitel</option>
        <option value="otros">Otros</option>
      </select>
    </td>
    <td>
      <select name="lineas[${i}][equipo_sim]" class="linea-select">
        <option value="sim_card">SIM Card</option>
        <option value="equipo">Equipo</option>
        <option value="sim_card_equipo">SIM + Equipo</option>
      </select>
    </td>
    <td>
      <select name="lineas[${i}][descuento]" class="linea-select" onchange="toggleWf(this, ${i})">
        <option value="no_aplica">No aplica</option>
        <option value="50%">50%</option>
        <option value="bajo_plantilla" ${document.querySelector('input[name=\'tipo_ingreso\']')?.value === 'centralizado' ? '' : 'disabled'}>Bajo plantilla</option>
      </select>
    </td>
    <td>
      <input type="text" name="lineas[${i}][nro_wf]" class="linea-input" id="wf_${i}"
             placeholder="6 dígitos" maxlength="6" style="display:none">
    </td>
    <td class="td-large" style="${esAlta ? '' : 'display:none'}">
      <input type="text" name="lineas[${i}][large_asociada]" class="linea-input" placeholder="N° serie large">
    </td>
    <td>
      <button type="button" class="btn-remove-linea" onclick="removeLinea(this)">×</button>
    </td>
  `;
  document.getElementById('lineasBody').appendChild(tr);
}

function removeLinea(btn) {
  const tbody = document.getElementById('lineasBody');
  if (tbody.children.length > 1) {
    btn.closest('tr').remove();
  }
}

function toggleWf(select, i) {
  const wf = document.getElementById(`wf_${i}`);
  wf.style.display = select.value === 'bajo_plantilla' ? 'block' : 'none';
}

// ── TIPO INGRESO ──────────────────────────
function onTipoIngresoChange(valor) {
  const esCentralizado  = valor === 'centralizado';
  const esAlmacenPropio = valor === 'almacen_propio';

  // B1: "Bajo plantilla" solo disponible si es CENTRALIZADO
  document.querySelectorAll('select[name$="[descuento]"]').forEach(select => {
    const optPlantilla = select.querySelector('option[value="bajo_plantilla"]');
    if (!optPlantilla) return;
    optPlantilla.disabled = !esCentralizado;
    if (!esCentralizado && select.value === 'bajo_plantilla') {
      select.value = 'no_aplica';
      const i = select.name.match(/\[(\d+)\]/)?.[1];
      if (i !== undefined) toggleWf(select, i);
    }
  });

  // B3: Almacén Propio → ocultar campos de delivery (solo facturación)
  const camposDelivery = [
    'inputGeoX', 'inputGeoY',         // coordenadas
  ];
  const gruposDelivery = [
    document.querySelector('[name="plano_geodir"]')?.closest('.form-group'),
    document.querySelector('[name="direccion_entrega"]')?.closest('.form-group'),
    document.querySelector('[name="referencias_entrega"]')?.closest('.form-group'),
  ];
  // El row de coordenadas+plano
  const rowCoords = document.getElementById('inputGeoX')?.closest('.form-row');
  if (rowCoords) rowCoords.style.display = esAlmacenPropio ? 'none' : '';

  // El group de dirección entrega y referencia
  gruposDelivery.forEach(g => { if (g) g.style.display = esAlmacenPropio ? 'none' : ''; });

  // El group de motorizado también se oculta (no hay delivery)
  const grupoMotorizado = document.querySelector('[name="telefono_referencia_movil"]')?.closest('.form-group');
  if (grupoMotorizado) grupoMotorizado.style.display = esAlmacenPropio ? 'none' : '';
}

// ── DOCUMENTOS ────────────────────────────
function previewDocs(input) {
  const list = document.getElementById('docList');
  Array.from(input.files).forEach(file => {
    const item = document.createElement('div');
    item.className = 'doc-item';
    item.innerHTML = `
      <span>📄</span>
      <span>${file.name}</span>
      <span style="color:rgba(255,255,255,0.25);font-size:11px;">${(file.size/1024).toFixed(0)} KB</span>
      <button type="button" class="doc-item-remove" onclick="this.parentElement.remove()">×</button>
    `;
    list.appendChild(item);
  });
}

// ── VALIDACIÓN SUBMIT ─────────────────────
function checkSubmit() {
  const tipo = document.getElementById('inputTipo').value;
  const tipoIngreso = document.querySelector('input[name="tipo_ingreso"]')?.value;
  let ok = tipo && tipoIngreso;
  document.getElementById('btnSubmit').disabled = !ok;
}

// Escuchar cambios en inputs
document.getElementById('formVenta').addEventListener('input', checkSubmit);
</script>
@endsection