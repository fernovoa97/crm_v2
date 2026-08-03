{{--
    Partial compartido por asesor.ventas.create y asesor.ventas.edit.
    - En creación: $venta es null, $lead trae los datos del prospecto.
    - En edición:  $venta trae la venta existente, $lead es la venta->lead (puede ser null
                    si la venta se registró como "venta directa" sin guardarse como lead).
    Prioridad de datos para prellenar: old() del intento anterior > $venta > $lead.
--}}
@php
    $venta = $venta ?? null;
    $lead  = $lead ?? null;

    // Base de datos para prellenar el wizard. Si es edición, partimos de las
    // columnas de la venta (los nombres de columna coinciden 1:1 con los name="" del form).
    $formBase = $venta ? $venta->toArray() : [];
    if ($venta) {
        // Los campos boolean vienen de $casts como true/false nativos de PHP,
        // pero el JS de restauración compara contra strings ('1'/'0'), tal como
        // llegan los valores de un <input> HTML real. Normalizamos para que coincidan.
        foreach ($formBase as $campo => $valor) {
            if (is_bool($valor)) {
                $formBase[$campo] = $valor ? '1' : '0';
            }
        }
        $formBase['lineas'] = $venta->lineas->map(fn ($l) => $l->only([
            'nro_portar', 'plan', 'operador_cedente', 'operador_cedente_otro',
            'equipo_sim', 'modelo_equipo', 'descuento', 'nro_wf',
        ]))->values()->all();
    }
    // old() gana siempre que exista (venimos de un error de validación).
    $formData = old() ? array_merge($formBase, old()) : $formBase;

    // Helper local para no repetir old('campo', $formData['campo'] ?? '') en cada input.
    $fv = fn (string $campo, $default = '') => old($campo, $formData[$campo] ?? $default);
@endphp
<style>
/* ── RESET & VARIABLES ─────────────────────────────── */
:root {
  --accent:       #2FCAF5;
  --accent-dim:   rgba(47,202,245,0.12);
  --accent-border:rgba(47,202,245,0.3);
  --green:        #5dcaa5;
  --green-dim:    rgba(93,202,165,0.12);
  --orange:       #fac775;
  --orange-dim:   rgba(250,199,117,0.12);
  --red:          #ff9090;
  --red-dim:      rgba(255,80,80,0.1);
  --surface:      #15151c;
  --surface2:     rgba(255,255,255,0.03);
  --border:       rgba(255,255,255,0.07);
  --border2:      rgba(255,255,255,0.04);
  --text:         #fff;
  --text-muted:   rgba(255,255,255,0.4);
  --text-faint:   rgba(255,255,255,0.2);
  --radius-lg:    16px;
  --radius-md:    10px;
  --radius-sm:    7px;
}

html.light {
  --surface:      #ffffff;
  --surface2:     rgba(0,0,0,0.02);
  --border:       #d8eaf8;
  --border2:      #eef5fb;
  --text:         #0f0f13;
  --text-muted:   rgba(0,0,0,0.45);
  --text-faint:   rgba(0,0,0,0.25);
  --accent-dim:   rgba(47,202,245,0.1);
  --accent-border:rgba(47,202,245,0.4);
  --green-dim:    rgba(93,202,165,0.1);
  --orange-dim:   rgba(250,199,117,0.1);
  --red-dim:      rgba(255,80,80,0.08);
}

/* ── WIZARD LAYOUT ─────────────────────────────────── */
.wizard-wrap {
  display: grid;
  grid-template-columns: 200px 1fr 280px;
  gap: 24px;
  align-items: start;
  max-width: 1200px;
}

/* ── STEPPER (izquierda) ───────────────────────────── */
.stepper {
  position: sticky;
  top: 24px;
}

.stepper-item {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 8px 0;
  cursor: pointer;
  position: relative;
}

.stepper-item:not(:last-child)::after {
  content: '';
  position: absolute;
  left: 15px;
  top: 36px;
  width: 2px;
  height: calc(100% - 8px);
  background: var(--border);
  transition: background .3s;
}

.stepper-item.done::after    { background: var(--green); opacity: .4; }
.stepper-item.active::after  { background: var(--accent); opacity: .3; }

.step-dot {
  width: 32px; height: 32px;
  border-radius: 50%;
  border: 2px solid var(--border);
  background: var(--surface);
  display: flex; align-items: center; justify-content: center;
  font-size: 12px; font-weight: 700;
  color: var(--text-faint);
  flex-shrink: 0;
  transition: all .3s;
  position: relative;
  z-index: 1;
}

.stepper-item.done  .step-dot { border-color: var(--green); color: var(--green); background: var(--green-dim); }
.stepper-item.active .step-dot { border-color: var(--accent); color: var(--accent); background: var(--accent-dim); box-shadow: 0 0 0 4px rgba(47,202,245,0.08); }

.step-info { padding-top: 4px; }
.step-label { font-size: 12px; font-weight: 600; color: var(--text-faint); transition: color .3s; }
.stepper-item.active .step-label { color: var(--text); }
.stepper-item.done  .step-label  { color: var(--text-muted); }
.step-sub { font-size: 10px; color: var(--text-faint); margin-top: 1px; }

/* ── CARDS ─────────────────────────────────────────── */
.wcard {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  overflow: hidden;
  margin-bottom: 14px;
  transition: border-color .2s;
}

.wcard.active-card { border-color: rgba(47,202,245,0.18); }

.wcard-head {
  padding: 16px 22px;
  border-bottom: 1px solid var(--border);
  display: flex;
  align-items: center;
  gap: 12px;
}

.wcard-num {
  width: 24px; height: 24px;
  border-radius: 50%;
  background: var(--accent-dim);
  border: 1px solid var(--accent-border);
  color: var(--accent);
  font-size: 11px; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}

.wcard-title { font-size: 13px; font-weight: 700; color: var(--text); }
.wcard-badge {
  margin-left: auto;
  font-size: 10px; font-weight: 600;
  padding: 3px 10px; border-radius: 20px;
  background: var(--surface2);
  color: var(--text-faint);
  border: 1px solid var(--border);
}

.wcard-body { padding: 22px; }

/* ── TIPO SELECTOR ─────────────────────────────────── */
.tipo-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.tipo-opt {
  padding: 20px 16px;
  border-radius: var(--radius-md);
  border: 2px solid var(--border);
  background: var(--surface2);
  cursor: pointer;
  transition: all .2s;
  text-align: center;
  position: relative;
  overflow: hidden;
}

.tipo-opt::before {
  content: '';
  position: absolute;
  inset: 0;
  background: radial-gradient(ellipse at 50% 0%, rgba(47,202,245,0.06) 0%, transparent 70%);
  opacity: 0;
  transition: opacity .3s;
}

.tipo-opt:hover { border-color: var(--accent-border); }
.tipo-opt:hover::before { opacity: 1; }

.tipo-opt.active {
  border-color: var(--accent);
  background: var(--accent-dim);
}
.tipo-opt.active::before { opacity: 1; }

.tipo-icon { font-size: 32px; margin-bottom: 8px; }
.tipo-name { font-size: 15px; font-weight: 700; color: var(--text); margin-bottom: 4px; }
.tipo-desc { font-size: 11px; color: var(--text-muted); line-height: 1.4; }

.tipo-check {
  position: absolute;
  top: 10px; right: 10px;
  width: 18px; height: 18px;
  border-radius: 50%;
  background: var(--accent);
  display: none;
  align-items: center; justify-content: center;
}
.tipo-check::after { content: '✓'; font-size: 10px; color: #0f0f13; font-weight: 700; }
.tipo-opt.active .tipo-check { display: flex; }

/* ── BUBBLES ────────────────────────────────────────── */
.bgroup { display: flex; flex-wrap: wrap; gap: 7px; margin-top: 7px; }

.bubble {
  padding: 6px 14px;
  border-radius: 20px;
  border: 1px solid var(--border);
  background: transparent;
  color: var(--text-muted);
  font-size: 12px; font-weight: 600;
  cursor: pointer;
  transition: all .15s;
  font-family: 'Sora', sans-serif;
  user-select: none;
  line-height: 1;
}
.bubble:hover { border-color: var(--accent-border); color: var(--text); }
.bubble.active { border-color: var(--accent); background: var(--accent-dim); color: var(--accent); }
.bubble.active-green { border-color: var(--green); background: var(--green-dim); color: var(--green); }
.bubble.active-orange { border-color: var(--orange); background: var(--orange-dim); color: var(--orange); }

/* ── FORM FIELDS ────────────────────────────────────── */
.fgroup { margin-bottom: 16px; }
.fgroup:last-child { margin-bottom: 0; }

.flabel {
  display: block;
  font-size: 11px; font-weight: 700;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: .5px;
  margin-bottom: 7px;
}
.flabel .req { color: var(--red); margin-left: 2px; font-weight: 700; }
.flabel .hint {
  font-size: 10px; color: var(--orange);
  text-transform: none; letter-spacing: 0; font-weight: 500; margin-left: 6px;
}

.finput {
  width: 100%; box-sizing: border-box;
  background: rgba(255,255,255,0.04);
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  padding: 10px 13px;
  font-size: 13px; color: var(--text);
  font-family: 'Sora', sans-serif;
  outline: none;
  transition: border .2s, background .2s;
}
.finput:focus { border-color: var(--accent-border); background: rgba(47,202,245,0.03); }
.finput::placeholder { color: var(--text-faint); }
.finput[readonly] { background: var(--surface2); color: var(--text-muted); cursor: not-allowed; }
.finput option { background: #1a1a24; }

html.light .finput { background: #f2f7fd; border-color: var(--border); color: var(--text); }
html.light .finput:focus { background: #eaf4ff; }
html.light .finput[readonly] { background: #eef5fb; }

.frow   { display: grid; grid-template-columns: 1fr 1fr;     gap: 14px; }
.frow-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px; }

.fsep {
  font-size: 10px; font-weight: 700;
  text-transform: uppercase; letter-spacing: .6px;
  color: var(--text-faint);
  border-top: 1px solid var(--border2);
  padding-top: 16px;
  margin: 20px 0 16px;
}

/* ── ALERT BIOMETRÍA ────────────────────────────────── */
.alert-bio {
  display: none;
  background: var(--orange-dim);
  border: 1px solid rgba(250,199,117,0.25);
  border-radius: var(--radius-sm);
  padding: 10px 14px;
  font-size: 12px; color: var(--orange);
  margin-bottom: 16px;
  line-height: 1.5;
}

/* ── PLANES FIJA ────────────────────────────────────── */
.plan-check {
  display: flex; align-items: center; gap: 10px;
  padding: 9px 13px;
  border-radius: var(--radius-sm);
  border: 1px solid var(--border);
  cursor: pointer; transition: all .15s;
  background: var(--surface2);
}
.plan-check:hover { border-color: var(--accent-border); }
.plan-check.active { border-color: var(--accent); background: var(--accent-dim); }
.plan-check input[type="checkbox"] { display: none; }

.plan-dot {
  width: 16px; height: 16px;
  border-radius: 4px;
  border: 1.5px solid var(--border);
  flex-shrink: 0; transition: all .15s;
  display: flex; align-items: center; justify-content: center;
}
.plan-check.active .plan-dot {
  background: var(--accent); border-color: var(--accent);
}
.plan-check.active .plan-dot::after {
  content: '✓'; font-size: 9px; color: #0f0f13; font-weight: 700;
}
.plan-name { font-size: 12px; color: var(--text-muted); }
.plan-check.active .plan-name { color: var(--text); }

.planes-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-top: 8px; }

/* ── TABLA LÍNEAS ───────────────────────────────────── */
.lineas-wrap {
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  overflow: hidden;
  margin-top: 10px;
}

.lineas-table { width: 100%; border-collapse: collapse; }
.lineas-table th {
  font-size: 10px; font-weight: 700;
  color: var(--text-faint);
  text-transform: uppercase; letter-spacing: .5px;
  padding: 10px 10px;
  text-align: left;
  background: var(--surface2);
  border-bottom: 1px solid var(--border);
  white-space: nowrap;
}
.lineas-table td {
  padding: 8px 6px;
  border-bottom: 1px solid var(--border2);
  vertical-align: middle;
}
.lineas-table tr:last-child td { border-bottom: none; }
.lineas-table tr:hover td { background: rgba(255,255,255,0.01); }

.linea-input, .linea-select {
  width: 100%; box-sizing: border-box;
  background: rgba(255,255,255,0.03);
  border: 1px solid transparent;
  border-radius: 6px;
  padding: 6px 8px;
  font-size: 11px; color: var(--text);
  font-family: 'Sora', sans-serif;
  outline: none; transition: border .15s;
}
.linea-input:focus, .linea-select:focus { border-color: var(--accent-border); background: rgba(47,202,245,0.04); }
.linea-select option { background: #1a1a24; }

html.light .linea-input, html.light .linea-select {
  background: #f2f7fd; border-color: #e0edf8; color: var(--text);
}

.btn-add-linea {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 8px 16px; margin-top: 12px;
  border-radius: var(--radius-sm);
  border: 1px dashed var(--accent-border);
  background: var(--accent-dim);
  color: var(--accent);
  font-size: 12px; font-weight: 700;
  cursor: pointer; font-family: 'Sora', sans-serif;
  transition: all .15s;
}
.btn-add-linea:hover { background: rgba(47,202,245,0.2); }

.btn-remove-linea {
  background: none; border: none;
  color: rgba(255,80,80,0.4);
  cursor: pointer; font-size: 18px; padding: 0 4px;
  transition: color .15s; line-height: 1;
}
.btn-remove-linea:hover { color: var(--red); }

/* ── DOCUMENTOS ─────────────────────────────────────── */
.doc-drop {
  border: 2px dashed var(--border);
  border-radius: var(--radius-md);
  padding: 28px 20px;
  text-align: center;
  cursor: pointer; transition: all .2s;
  background: var(--surface2);
}
.doc-drop:hover { border-color: var(--accent-border); background: var(--accent-dim); }
.doc-drop-icon { color: var(--text-faint); margin-bottom: 8px; }
.doc-drop p { font-size: 12px; color: var(--text-faint); margin-top: 4px; }
.doc-drop span { font-size: 11px; color: var(--text-faint); opacity: .7; }

.doc-list { margin-top: 10px; display: flex; flex-direction: column; gap: 6px; }
.doc-item {
  display: flex; align-items: center; gap: 8px;
  padding: 9px 13px;
  background: var(--surface2);
  border: 1px solid var(--border); border-radius: var(--radius-sm);
  font-size: 12px; color: var(--text-muted);
}
.doc-item-remove {
  margin-left: auto; background: none; border: none;
  color: rgba(255,80,80,0.4); cursor: pointer; font-size: 16px;
}

.wcard,
.wcard-body,
.fgroup {
  overflow: visible !important;
}

/* ── CAC DROPDOWN ───────────────────────────────────── */
.cac-drop {
  position: absolute;
  top: calc(100% + 4px);
  left: 0;
  width: 100%;

  z-index: 99999;

  background: #1c1c26;
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: var(--radius-md);

  max-height: 220px;
  overflow-y: auto;
  overflow-x: hidden;

  box-shadow: 0 8px 32px rgba(0,0,0,0.4);
}

html.light .cac-drop {
  background: #fff;
  box-shadow: 0 8px 32px rgba(0,0,0,0.12);
}

.cac-opt {
  padding: 10px 14px;
  cursor: pointer;
  border-bottom: 1px solid var(--border2);
  transition: background .15s;
}

.cac-opt:last-child {
  border-bottom: none;
}

.cac-opt:hover {
  background: var(--accent-dim);
}

.cac-opt-name {
  font-size: 13px;
  color: var(--text);
  font-weight: 600;
}

.cac-opt-dir {
  font-size: 11px;
  color: var(--text-muted);
  margin-top: 2px;
}

/* ── SIDEBAR PANEL ──────────────────────────────────── */
.side-panel {
  position: sticky; top: 24px;
  display: flex; flex-direction: column; gap: 14px;
}

.scard {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  padding: 18px 20px;
}

.scard-title {
  font-size: 10px; font-weight: 700;
  text-transform: uppercase; letter-spacing: .8px;
  color: var(--text-faint);
  margin-bottom: 14px;
}

.srow {
  display: flex; justify-content: space-between; align-items: flex-start;
  gap: 8px; margin-bottom: 10px; font-size: 12px;
}
.srow:last-child { margin-bottom: 0; }
.slabel { color: var(--text-muted); flex-shrink: 0; }
.sval { color: var(--text); font-weight: 600; text-align: right; max-width: 160px; word-break: break-word; }

/* ── PROGRESO ───────────────────────────────────────── */
.progress-bar-wrap {
  height: 3px;
  background: var(--border);
  border-radius: 2px;
  margin-bottom: 14px;
  overflow: hidden;
}
.progress-bar-fill {
  height: 100%;
  background: linear-gradient(90deg, var(--accent), var(--green));
  border-radius: 2px;
  transition: width .4s ease;
  width: 0%;
}

/* ── BTN SUBMIT ─────────────────────────────────────── */
.btn-submit {
  width: 100%; padding: 13px;
  border-radius: var(--radius-md);
  background: var(--accent);
  color: #0f0f13;
  border: none;
  font-size: 14px; font-weight: 700;
  cursor: pointer; font-family: 'Sora', sans-serif;
  transition: opacity .2s, transform .1s;
  letter-spacing: .2px;
}
.btn-submit:hover { opacity: .9; transform: translateY(-1px); }
.btn-submit:active { transform: translateY(0); }
.btn-submit:disabled { opacity: .45; cursor: not-allowed; transform: none; }

.submit-hint {
  font-size: 11px; color: var(--text-faint);
  text-align: center; margin-top: 8px;
}

/* ── ERRORES ────────────────────────────────────────── */
.err-box {
  background: var(--red-dim);
  border: 1px solid rgba(255,80,80,0.3);
  border-radius: var(--radius-sm);
  padding: 12px 14px;
  margin-bottom: 12px;
}
.err-box-title { font-size: 12px; font-weight: 700; color: var(--red); margin-bottom: 6px; }
.err-box ul { margin: 0; padding-left: 16px; }
.err-box li { font-size: 11px; color: var(--red); margin-bottom: 3px; }

/* ── ANIMATE IN ─────────────────────────────────────── */
@keyframes slideDown {
  from { opacity:0; transform: translateY(-8px); }
  to   { opacity:1; transform: translateY(0); }
}
.wcard { animation: slideDown .25s ease; }

/* ── SECTION DIVIDER ────────────────────────────────── */
.inline-section {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
}

/* ── CAMPOS CONDICIONALES ───────────────────────────── */
.field-porta     { display: none; }
.field-fullclaro { display: none; }
</style>
<input type="hidden" name="tipo"    id="inputTipo" value="">

<div class="wizard-wrap">

  {{-- ── STEPPER ── --}}
  <div class="stepper" id="stepper">
    @if(!$venta && !$lead?->id)
    <div class="stepper-item" id="si0" onclick="scrollToCard('c0')">
      <div class="step-dot">0</div>
      <div class="step-info">
        <div class="step-label">Empresa</div>
        <div class="step-sub">RUC y razón social</div>
      </div>
    </div>
    @endif
    <div class="stepper-item" id="si1" onclick="scrollToCard('c1')">
      <div class="step-dot">1</div>
      <div class="step-info">
        <div class="step-label">Servicio</div>
        <div class="step-sub">Móvil o Fija</div>
      </div>
    </div>
    <div class="stepper-item" id="si2" onclick="scrollToCard('c2')">
      <div class="step-dot">2</div>
      <div class="step-info">
        <div class="step-label">Ingreso y venta</div>
        <div class="step-sub">PDV, porta, alta…</div>
      </div>
    </div>
    <div class="stepper-item" id="si3" onclick="scrollToCard('c3')">
      <div class="step-dot">3</div>
      <div class="step-info">
        <div class="step-label">Cliente</div>
        <div class="step-sub">Datos del rep.</div>
      </div>
    </div>
    <div class="stepper-item" id="si4" onclick="scrollToCard('c4')">
      <div class="step-dot">4</div>
      <div class="step-info">
        <div class="step-label">Servicio</div>
        <div class="step-sub">Detalles técnicos</div>
      </div>
    </div>
    <div class="stepper-item" id="si5" onclick="scrollToCard('c5')">
      <div class="step-dot">5</div>
      <div class="step-info">
        <div class="step-label">Documentos</div>
        <div class="step-sub">Adjuntos opcionales</div>
      </div>
    </div>
  </div>

  {{-- ── COLUMNA CENTRAL ── --}}
  <div id="mainCol">

{{-- PASO 0: solo si es venta directa (sin lead previo) --}}
@if(!$venta && !$lead?->id)

<div class="wcard active-card" id="c0">
    <div class="wcard-head">
        <div class="wcard-num">0</div>
        <div class="wcard-title">
            Datos de nueva empresa
        </div>
    </div>

    <div class="wcard-body">

        <div class="frow">
            <div class="fgroup">
                <label class="flabel">RUC <span class="req">*</span></label>
                <input
    type="text"
    name="ruc"
    maxlength="11"
    id="rucEmpresa"
    class="finput"
    value="{{ old('ruc', $lead->ruc) }}"
    required
>
            </div>

            <div class="fgroup">
                <label class="flabel">Razón social <span class="req">*</span></label>
                <input
    type="text"
    name="razon_social"
    id="razonEmpresa"
    class="finput"
    value="{{ old('razon_social', $lead->razon_social) }}"
    required
>
            </div>
        </div>

        <div class="frow">
            <div class="fgroup">
                <label class="flabel">Segmento</label>
                <select name="segmento" class="finput">
                    <option value="">— Seleccionar —</option>
                    <option value="micro" {{ $fv('segmento') === 'micro' ? 'selected' : '' }}>Microempresa</option>
                    <option value="pyme" {{ $fv('segmento') === 'pyme' ? 'selected' : '' }}>Pyme</option>
                    <option value="nuevo" {{ $fv('segmento') === 'nuevo' ? 'selected' : '' }}>Nuevo</option>
                    <option value="mayores" {{ $fv('segmento') === 'mayores' ? 'selected' : '' }}>Mayores</option>
                </select>
            </div>

            <div class="fgroup">
                <label class="flabel">Departamento</label>
                <input
                    type="text"
                    name="departamento"
                    class="finput"
                    value="{{ $fv('departamento') }}"
                >
            </div>
        </div>

        <div class="fgroup" style="margin-top:16px;">
    <label style="
        display:flex;
        align-items:center;
        gap:10px;
        cursor:pointer;
        font-size:13px;
        color:var(--text);
        user-select:none;
    ">
        <input
            type="checkbox"
            name="guardar_como_lead"
            value="1"
            {{ $fv('guardar_como_lead', true) ? 'checked' : '' }}
            style="
                width:16px;
                height:16px;
                accent-color:#2FCAF5;
                cursor:pointer;
            "
        >

        Guardar también como lead en mi CRM
    </label>
</div>

    </div>
</div>

@endif

    {{-- PASO 1: TIPO --}}
    <div class="wcard active-card" id="c1">
      <div class="wcard-head">
        <div class="wcard-num">1</div>
        <div class="wcard-title">¿Qué tipo de servicio vas a vender?</div>
      </div>
      <div class="wcard-body">
        <div class="tipo-grid">
          <div class="tipo-opt" id="tipoMovil" onclick="setTipo('movil')">
            <div class="tipo-check"></div>
            <div class="tipo-icon">📱</div>
            <div class="tipo-name">Móvil</div>
            <div class="tipo-desc">Líneas, portabilidades y renovaciones</div>
          </div>
          <div class="tipo-opt" id="tipoFija" onclick="setTipo('fija')">
            <div class="tipo-check"></div>
            <div class="tipo-icon">🏢</div>
            <div class="tipo-name">Fija / Internet</div>
            <div class="tipo-desc">Internet, telefonía fija y cable</div>
          </div>
        </div>
      </div>
    </div>

    {{-- PASO 2: INGRESO Y TIPO DE VENTA --}}
    <div class="wcard" id="c2" style="display:none;">
      <div class="wcard-head">
        <div class="wcard-num">2</div>
        <div class="wcard-title">Ingreso y tipo de venta</div>
      </div>
      <div class="wcard-body">

        <div class="fgroup">
          <label class="flabel">Tipo de ingreso <span class="req">*</span></label>
          <div class="bgroup">
            <div class="bubble" onclick="setBubble(this,'tipo_ingreso','pdv'); onTipoIngresoChange('pdv')">PDV</div>
            <div class="bubble" onclick="setBubble(this,'tipo_ingreso','centralizado'); onTipoIngresoChange('centralizado')">Centralizado</div>
            <div class="bubble" onclick="setBubble(this,'tipo_ingreso','almacen_propio'); onTipoIngresoChange('almacen_propio')">Almacén Propio</div>
          </div>
          <input type="hidden" name="tipo_ingreso" id="inputTipoIngreso">
        </div>

        {{-- Tipo venta móvil --}}
        <div class="fgroup" id="grupoTipoVentaMovil" style="display:none;">
          <label class="flabel">Tipo de venta <span class="req">*</span></label>
          <div class="bgroup">
            <div class="bubble" onclick="setBubble(this,'tipo_venta_movil','alta'); togglePortaMovil('alta')">Alta nueva</div>
            <div class="bubble" onclick="setBubble(this,'tipo_venta_movil','porta'); togglePortaMovil('porta')">Portabilidad</div>
            <div class="bubble" onclick="setBubble(this,'tipo_venta_movil','renovacion'); togglePortaMovil('renovacion')">Renovación</div>
          </div>
          <input type="hidden" name="tipo_venta_movil" id="inputTipoVentaMovil">
        </div>

        {{-- Tipo venta fija --}}
        <div class="fgroup" id="grupoTipoVentaFija" style="display:none;">
          <label class="flabel">Tipo de venta <span class="req">*</span></label>
          <div class="bgroup">
            <div class="bubble" onclick="setBubble(this,'tipo_venta_fija','alta'); togglePortaFija(false)">Alta</div>
            <div class="bubble" onclick="setBubble(this,'tipo_venta_fija','porta'); togglePortaFija(true)">Portabilidad</div>
          </div>
          <input type="hidden" name="tipo_venta_fija" id="inputTipoVentaFija">
        </div>

        {{-- Porta fija extra --}}
        <div id="grupoPortaFija" style="display:none;">
          <div class="frow">
            <div class="fgroup">
              <label class="flabel">Operador cedente <span class="req">*</span></label>
              <select name="operador_cedente_fija" class="finput">
                <option value="">— Seleccionar —</option>
                <option value="movistar">Movistar</option>
                <option value="entel">Entel</option>
                <option value="win">Win</option>
                <option value="otros">Otros</option>
              </select>
            </div>
            <div class="fgroup">
              <label class="flabel">Teléfono fijo a migrar <span class="req">*</span></label>
              <input type="text" name="telefono_fijo_migrar" class="finput" value="{{ $fv('telefono_fijo_migrar') }}" placeholder="01 XXXXXXX">
            </div>
          </div>
        </div>

      </div>
    </div>

    {{-- PASO 3: DATOS DEL CLIENTE --}}
    <div class="wcard" id="c3" style="display:none;">
      <div class="wcard-head">
        <div class="wcard-num">3</div>
        <div class="wcard-title">Datos del cliente</div>
      </div>
      <div class="wcard-body">

        <div class="alert-bio" id="alertBiometria">
          ⚠️ <strong>Importante:</strong> El representante ingresado debe ser quien pasará la <strong>biometría</strong> al momento de la portabilidad o alta.
        </div>

        <div class="frow" style="margin-bottom:16px;">

    <div class="fgroup">
        <label class="flabel">RUC</label>
        <input
            type="text"
            class="finput"
            id="previewRuc"
            value="{{ $fv('ruc', $lead?->ruc ?? '') }}"
            readonly
        >
    </div>

    <div class="fgroup">
        <label class="flabel">Razón Social</label>
        <input
            type="text"
            class="finput"
            id="previewRazon"
            value="{{ $fv('razon_social', $lead?->razon_social ?? '') }}"
            readonly
        >
    </div>

</div>

        <div class="fgroup">
          <label class="flabel">
            Nombre del representante <span class="req">*</span>
            <span class="hint" id="hintBiometria" style="display:none;">👆 Quien pasará la biometría</span>
          </label>
          <input type="text" name="nombre_representante" class="finput"
                 value="{{ $fv('nombre_representante', $lead?->nombre_rl ?? '') }}" placeholder="Nombre completo">
        </div>

        <div class="frow">
          <div class="fgroup">
            <label class="flabel">Tipo de documento <span class="req">*</span></label>
            <div class="bgroup">
              <div class="bubble" onclick="setBubble(this,'tipo_documento','dni'); setDocLimit('dni')">DNI</div>
              <div class="bubble" onclick="setBubble(this,'tipo_documento','ce'); setDocLimit('ce')">CE</div>
            </div>
            <input type="hidden" name="tipo_documento" id="inputTipoDoc">
          </div>
          <div class="fgroup">
            <label class="flabel">N° de documento <span class="req">*</span></label>
            <input type="text" name="nro_documento" id="inputNroDoc" class="finput"
                   value="{{ $fv('nro_documento') }}" maxlength="8" placeholder="8 dígitos">
          </div>
        </div>

        <div class="frow">
          <div class="fgroup">
            <label class="flabel">Teléfono del representante <span class="req">*</span></label>
            <input type="text" name="telefono_representante" class="finput"
                   value="{{ $fv('telefono_representante', $lead?->telf1 ?? '') }}" placeholder="9XX XXX XXX">
          </div>
          <div class="fgroup" id="grupoTelfSot" style="display:none;">
            <label class="flabel">Teléfono para SOT <span class="hint">(técnico)</span></label>
            <input type="text" name="telefono_sot" class="finput" value="{{ $fv('telefono_sot') }}" placeholder="9XX XXX XXX">
          </div>
          <div class="fgroup" id="grupoTelfBiometria" style="display:none;">
            <label class="flabel">Teléfono motorizado de delivery</label>
            <input type="text" name="telefono_referencia_movil" class="finput" value="{{ $fv('telefono_referencia_movil') }}" placeholder="9XX XXX XXX">
          </div>
        </div>

        <div class="fgroup">
          <label class="flabel">Correo electrónico <span class="req">*</span></label>
          <input type="email" name="correo" class="finput"
                 value="{{ $fv('correo', $lead?->correo_rl ?? '') }}" placeholder="correo@empresa.com">
        </div>

      </div>
    </div>

    {{-- PASO 4A: FIJA --}}
    <div class="wcard" id="pasoFija" style="display:none;">
      <div class="wcard-head">
        <div class="wcard-num">4</div>
        <div class="wcard-title">Datos del servicio fijo</div>
      </div>
      <div class="wcard-body">

        <div class="frow">
          <div class="fgroup">
            <label class="flabel">Coordenadas de cobertura <span class="hint">(factibilidad)</span></label>
            <input type="text" name="coordenadas_cobertura" class="finput" value="{{ $fv('coordenadas_cobertura') }}" placeholder="-12.0464, -77.0428">
          </div>
          <div class="fgroup">
            <label class="flabel">Plano de cobertura <span class="hint">(factibilidad)</span></label>
            <input type="text" name="plano_cobertura" class="finput" value="{{ $fv('plano_cobertura') }}" placeholder="URL o referencia">
          </div>
        </div>

        <div class="fgroup">
          <label class="flabel">Dirección de instalación <span class="req">*</span></label>
          <input type="text" name="direccion_instalacion" class="finput" value="{{ $fv('direccion_instalacion') }}" placeholder="Av. / Jr. / Calle...">
        </div>

        <div class="frow">
          <div class="fgroup">
            <label class="flabel">Referencia de dirección</label>
            <input type="text" name="referencia_direccion_instalacion" class="finput" value="{{ $fv('referencia_direccion_instalacion') }}" placeholder="Cerca a...">
          </div>
          <div class="fgroup">
            <label class="flabel">Dirección de facturación</label>
            <input type="text" name="direccion_facturacion_fija" class="finput" value="{{ $fv('direccion_facturacion_fija') }}" placeholder="Si difiere de instalación">
          </div>
        </div>

        <div class="fsep">Tecnología y campaña</div>

        <div class="frow">
          <div class="fgroup">
            <label class="flabel">Tecnología <span class="req">*</span></label>
            <div class="bgroup">
              <div class="bubble" onclick="setBubble(this,'tecnologia','hfc')">HFC</div>
              <div class="bubble" onclick="setBubble(this,'tecnologia','ftth')">FTTH</div>
            </div>
            <input type="hidden" name="tecnologia" id="inputTecnologia">
          </div>
          <div class="fgroup">
            <label class="flabel">Campaña <span class="req">*</span></label>
            <div class="bgroup">
              <div class="bubble" onclick="setBubble(this,'campana_fija','regular')">Regular</div>
              <div class="bubble" onclick="setBubble(this,'campana_fija','1_sol')">1 Sol</div>
              <div class="bubble" onclick="setBubble(this,'campana_fija','empresas_medio')">Empresas Medio</div>
              <div class="bubble" onclick="setBubble(this,'campana_fija','empresas_basico')">Empresas Básico</div>
              <div class="bubble" onclick="setBubble(this,'campana_fija','empresas_grande')">Empresas Grande</div>
              <div class="bubble" onclick="setBubble(this,'campana_fija','relampago')">Relámpago</div>
            </div>
            <input type="hidden" name="campana_fija" id="inputCampanaFija">
          </div>
        </div>

        <div class="fsep">Tipo de producto y planes</div>

        <div class="fgroup">
          <label class="flabel">Tipo de producto <span class="req">*</span></label>
          <div class="bgroup">
            <div class="bubble" onclick="setBubble(this,'tipo_producto_fija','1play'); renderCombosPlay('1play')">1Play</div>
            <div class="bubble" onclick="setBubble(this,'tipo_producto_fija','2play'); renderCombosPlay('2play')">2Play</div>
            <div class="bubble" onclick="setBubble(this,'tipo_producto_fija','3play'); renderCombosPlay('3play')">3Play</div>
          </div>
          <input type="hidden" name="tipo_producto_fija" id="inputTipoProducto">
          <div style="font-size:10px;color:var(--text-faint);margin-top:6px;" id="hintPlay">
            1Play: cualquier servicio · 2Play: internet + (telf o cable) · 3Play: internet + telf + cable
          </div>
        </div>

        <div class="fgroup" id="grupoCombosPlay" style="display:none;">
          <label class="flabel">Planes <span class="req">*</span></label>
          <div id="combosPlayContainer"></div>
          <input type="hidden" name="plan_telefonia"      id="hPlanTelefonia"    value="0">
          <input type="hidden" name="plan_cable_standar"  id="hPlanCableStandar" value="0">
          <input type="hidden" name="plan_cable_superior" id="hPlanCableSup"     value="0">
          <input type="hidden" name="plan_internet_200"   id="hPlanInt200"       value="0">
          <input type="hidden" name="plan_internet_400"   id="hPlanInt400"       value="0">
          <input type="hidden" name="plan_internet_1500"  id="hPlanInt1500"      value="0">
        </div>

        <div class="fsep">Adicionales</div>

        <div class="frow">
          <div class="fgroup">
            <label class="flabel">Cantidad de DECOs</label>
            <input type="number" name="cantidad_decos" class="finput" min="0" value="{{ $fv('cantidad_decos', 0) }}">
          </div>
          <div class="fgroup">
            <label class="flabel">Cantidad de repetidores</label>
            <input type="number" name="cantidad_repetidores" class="finput" min="0" value="{{ $fv('cantidad_repetidores', 0) }}">
          </div>
        </div>

        <div class="fsep">Otros datos</div>

        <div class="frow">
          <div class="fgroup">
            <label class="flabel">Bono</label>
            <input type="text" name="bono_fija" class="finput" value="{{ $fv('bono_fija') }}" placeholder="Descripción del bono">
          </div>
          <div class="fgroup">
            <label class="flabel">Descuento</label>
            <input type="text" name="descuento_fija" class="finput" value="{{ $fv('descuento_fija') }}" placeholder="Descripción del descuento">
          </div>
        </div>

        <div class="fgroup">
          <label class="flabel">Full Claro</label>
          <div class="bgroup">
            <div class="bubble" onclick="setBubble(this,'full_claro','aplica'); toggleFullClaro(true)">Aplica</div>
            <div class="bubble" onclick="setBubble(this,'full_claro','no_aplica'); toggleFullClaro(false)">No aplica</div>
          </div>
          <input type="hidden" name="full_claro" id="inputFullClaro">
        </div>

        <div class="fgroup field-fullclaro" id="grupoFullClaro">
          <label class="flabel">N° móvil Full Claro <span class="hint">(opcional)</span></label>
          <input type="text" name="nro_movil_fullclaro" class="finput" value="{{ $fv('nro_movil_fullclaro') }}" placeholder="9XX XXX XXX">
        </div>

      </div>
    </div>

    {{-- PASO 4B: MÓVIL --}}
    <div class="wcard" id="pasoMovil" style="display:none;">
      <div class="wcard-head">
        <div class="wcard-num">4</div>
        <div class="wcard-title">Datos del servicio móvil</div>
      </div>
      <div class="wcard-body">

        <div class="fgroup">
          <label class="flabel">Tipo de entrega <span class="req">*</span></label>
          <div class="bgroup">
            <div class="bubble" onclick="setBubble(this,'tipo_entrega','delivery'); onTipoEntregaChange('delivery')">Delivery</div>
            <div class="bubble" onclick="setBubble(this,'tipo_entrega','recojo_cac'); onTipoEntregaChange('recojo_cac')">Recojo en CAC</div>
          </div>
          <input type="hidden" name="tipo_entrega" id="inputTipoEntrega">
          <input type="hidden" name="cac_id"        id="inputCacId" value="{{ $fv('cac_id') }}">
        </div>

        {{-- DELIVERY --}}
        <div id="grupoDelivery">
          <div class="frow">
            <div class="fgroup">
              <label class="flabel">Coordenadas de delivery <span class="hint">pega ambas y se separan</span></label>
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                <input type="text" id="inputGeoX" class="finput" placeholder="Lat: -12.0464"
                       oninput="syncGeodirCoords()" onpaste="setTimeout(splitGeodirPaste,10)">
                <input type="text" id="inputGeoY" class="finput" placeholder="Lng: -77.0428"
                       oninput="syncGeodirCoords()">
              </div>
              <input type="hidden" name="coordenadas_geodir" id="inputCoordsGeodirFinal" value="{{ $fv('coordenadas_geodir') }}">
            </div>
            <div class="fgroup">
              <label class="flabel">Plano de entrega</label>
              <input type="text" name="plano_geodir" class="finput" value="{{ $fv('plano_geodir') }}" placeholder="URL o referencia">
            </div>
          </div>

          <div class="fgroup">
            <label class="flabel">Dirección de entrega <span class="req">*</span></label>
            <input type="text" name="direccion_entrega" class="finput" value="{{ $fv('direccion_entrega') }}" placeholder="Av. / Jr. / Calle...">
          </div>

          <div class="frow">
            <div class="fgroup">
              <label class="flabel">Referencia del punto</label>
              <input type="text" name="referencias_entrega" class="finput" value="{{ $fv('referencias_entrega') }}" placeholder="Cerca a...">
            </div>
            <div class="fgroup">
              <label class="flabel">Dirección de facturación</label>
              <input type="text" name="direccion_facturacion_movil" class="finput" value="{{ $fv('direccion_facturacion_movil') }}" placeholder="Si difiere">
            </div>
          </div>
        </div>

        {{-- RECOJO EN CAC --}}
        <div id="grupoCac" style="display:none;">
          <div class="fgroup" style="position:relative;">
            <label class="flabel">Buscar CAC <span class="req">*</span></label>
            <input type="text" id="inputCacBusqueda" class="finput"
                   placeholder="Nombre o dirección del CAC..."
                   autocomplete="off" oninput="buscarCac(this.value)">
            <div id="cacDropdown" class="cac-drop" style="display:none;"></div>
          </div>
          <div id="grupoCacSeleccionado" style="display:none;">
            <div class="frow">
              <div class="fgroup">
                <label class="flabel">CAC seleccionado</label>
                <input type="text" id="inputCacNombre" class="finput" readonly
                       style="border-color:rgba(93,202,165,0.3);color:var(--green);">
              </div>
              <div class="fgroup">
                <label class="flabel">Dirección del CAC</label>
                <input type="text" id="inputCacDireccion" class="finput" readonly>
              </div>
            </div>
          </div>
          <div class="fgroup">
            <label class="flabel">Dirección de facturación</label>
            <input type="text" name="direccion_facturacion_movil" class="finput" placeholder="Si difiere">
          </div>
        </div>

        <div class="fgroup">
          <label class="flabel">Campaña <span class="req">*</span></label>
          <div class="bgroup">
            <div class="bubble" onclick="setBubble(this,'campana_movil','claro_negocios')">Claro Negocios</div>
            <div class="bubble" onclick="setBubble(this,'campana_movil','claro_emprendedor')">Claro Emprendedor</div>
          </div>
          <input type="hidden" name="campana_movil" id="inputCampanaMovil">
        </div>

        <div class="frow">
          <div class="fgroup">
            <label class="flabel">Fecha de despacho <span class="req">*</span></label>
            <input type="date" name="fecha_despacho" class="finput" id="inputFechaDespacho" value="{{ $fv('fecha_despacho') }}">
          </div>
          <div class="fgroup">
            <label class="flabel">Rango horario <span class="req">*</span></label>
            <div class="bgroup">
              <div class="bubble" id="bubbleSla3h" onclick="setBubble(this,'rango_horario','sla_3h')">SLA 3H</div>
              <div class="bubble" onclick="setBubble(this,'rango_horario','9-11')">9–11am</div>
              <div class="bubble" onclick="setBubble(this,'rango_horario','11-1')">11am–1pm</div>
              <div class="bubble" onclick="setBubble(this,'rango_horario','2-4')">2–4pm</div>
              <div class="bubble" onclick="setBubble(this,'rango_horario','4-6')">4–6pm</div>
            </div>
            <input type="hidden" name="rango_horario" id="inputRangoHorario">
          </div>
        </div>

        <div class="fgroup">
          <label class="flabel">Comentario de despacho <span class="hint">casos solo SEC u observaciones</span></label>
          <input type="text" name="comentario_despacho" class="finput" value="{{ $fv('comentario_despacho') }}" placeholder="Ej: Solo SEC, coordinar con...">
        </div>

        <div class="fsep">Líneas solicitadas</div>

        <div class="lineas-wrap">
          <table class="lineas-table" id="tablLineas">
            <thead>
              <tr>
                <th class="col-porta">N° a portar</th>
                <th>Plan</th>
                <th class="col-porta">Op. cedente</th>
                <th>Equipo / SIM</th>
                <th>Modelo de equipo</th>
                <th>Descuento</th>
                <th>N° WF</th>
                <th style="width:32px;"></th>
              </tr>
            </thead>
            <tbody id="lineasBody"></tbody>
          </table>
        </div>
        <button type="button" class="btn-add-linea" onclick="addLinea()">+ Agregar línea</button>

        <div id="grupoLarge" style="display:none; margin-top:14px;">
          <div class="fgroup">
            <label class="flabel">Large asociada <span class="hint">(alta nueva)</span></label>
            <input type="text" name="large_asociada" class="finput" value="{{ $fv('large_asociada') }}" placeholder="N° serie">
          </div>
        </div>

      </div>
    </div>

    {{-- PASO 5: DOCUMENTOS --}}
    <div class="wcard" id="c5" style="display:none;">
      <div class="wcard-head">
        <div class="wcard-num">5</div>
        <div class="wcard-title">Documentos adjuntos</div>
        <span class="wcard-badge">Opcional</span>
      </div>
      <div class="wcard-body">
        <div class="doc-drop" onclick="document.getElementById('inputDocs').click()">
          <div class="doc-drop-icon">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
              <polyline points="17 8 12 3 7 8"/>
              <line x1="12" y1="3" x2="12" y2="15"/>
            </svg>
          </div>
          <p>Haz clic para adjuntar archivos</p>
          <span>PDF, Excel, imágenes, Word…</span>
          <input type="file" id="inputDocs" name="documentos[]" multiple
                 accept=".pdf,.xlsx,.xls,.csv,.jpg,.jpeg,.png,.doc,.docx"
                 onchange="previewDocs(this); updateProgress()"
                 style="display:none">
        </div>
        <div class="doc-list" id="docList"></div>
        <div id="docsRestoreNotice" style="display:none;margin-top:10px;padding:10px 14px;background:var(--orange-dim);border:1px solid rgba(250,199,117,0.25);border-radius:var(--radius-sm);font-size:12px;color:var(--orange);line-height:1.5;">
          ⚠️ Por seguridad, el navegador no puede volver a adjuntar automáticamente los archivos que habías seleccionado antes de este error. Por favor, vuelve a adjuntarlos.
        </div>
      </div>
    </div>

  </div>{{-- /mainCol --}}

  {{-- ── SIDEBAR ── --}}
  <div class="side-panel">

    {{-- Progreso --}}
    <div class="scard">
      <div class="scard-title">Progreso del formulario</div>
      <div class="progress-bar-wrap">
        <div class="progress-bar-fill" id="progressFill"></div>
      </div>
      <div style="font-size:11px;color:var(--text-faint);text-align:right;" id="progressLabel">0 de 5 pasos</div>
    </div>

    {{-- Datos de empresa: si editamos, mostramos los de la venta; si creamos, los del lead --}}
    <div class="scard">
      <div class="scard-title">{{ $venta ? 'Datos de la venta' : 'Datos del lead' }}</div>
      <div class="srow"><span class="slabel">RUC</span><span class="sval">{{ ($venta->ruc ?? $lead?->ruc) ?: '—' }}</span></div>
      <div class="srow"><span class="slabel">Empresa</span><span class="sval">{{ ($venta->razon_social ?? $lead?->razon_social) ?: '—' }}</span></div>
      <div class="srow"><span class="slabel">Representante</span><span class="sval">{{ ($venta->nombre_representante ?? $lead?->nombre_rl) ?? '—' }}</span></div>
      <div class="srow"><span class="slabel">Teléfono</span><span class="sval">{{ ($venta->telefono_representante ?? $lead?->telf1) ?? '—' }}</span></div>
      @unless($venta)
      <div class="srow"><span class="slabel">Segmento</span><span class="sval">{{ ucfirst($lead?->segmento ?? '—') }}</span></div>
      <div class="srow"><span class="slabel">Depto.</span><span class="sval">{{ $lead?->departamento ?? '—' }}</span></div>
      @endunless
    </div>

    {{-- Ventas anteriores — solo al crear, y si el lead ya existe en BD --}}
    @if(!$venta && $lead?->id)
    <div class="scard" id="sidebarVentas" style="display:none;">
      <div class="scard-title">Ventas anteriores</div>
      @forelse($lead->ventas as $v)
        <div class="srow">
          <span class="slabel">{{ ucfirst($v->tipo) }} — {{ $v->created_at->format('d/m/Y') }}</span>
          <span class="sval" style="color:
            @if($v->estado === 'completada') var(--green)
            @elseif($v->estado === 'rechazada') var(--red)
            @else var(--orange)
            @endif
          ">{{ ucfirst($v->estado) }}</span>
        </div>
      @empty
        <div style="font-size:12px;color:var(--text-faint);">Sin ventas anteriores</div>
      @endforelse
    </div>
    @endif

    {{-- Errores servidor --}}
    @if($errors->any())
    <div class="err-box" id="serverErrors">
      <div class="err-box-title">⚠ Corrige estos campos:</div>
      <ul>
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
    @endif

    {{-- Submit --}}
    <div class="scard">
      <button type="submit" class="btn-submit" id="btnSubmit">
        Enviar a Mesa de Control
      </button>
      <div class="submit-hint" id="submitHint">Completa todos los campos requeridos</div>
    </div>

  </div>

</div>
<script>
// ── DATOS PARA RESTAURAR EL WIZARD (old() de un error previo, o la venta si estamos editando) ──
const OLD_DATA = @json($formData);
const IS_EDIT = @json((bool) $venta);
const HAS_SERVER_ERRORS = @json($errors->any());

// ── ESTADO ─────────────────────────────────────────
let tipoActual = null;

// ── TIPO DE SERVICIO ────────────────────────────────
function setTipo(tipo) {
  tipoActual = tipo;
  document.getElementById('inputTipo').value = tipo;

  document.getElementById('tipoMovil').classList.toggle('active', tipo === 'movil');
  document.getElementById('tipoFija').classList.toggle('active',  tipo === 'fija');

  // Mostrar pasos
  ['c2','c3','c5'].forEach(id => {
    const el = document.getElementById(id);
    el.style.display = 'block';
  });

  const sidebarVentas = document.getElementById('sidebarVentas');
  if (sidebarVentas) sidebarVentas.style.display = 'block';

  document.getElementById('pasoFija').style.display  = tipo === 'fija'  ? 'block' : 'none';
  document.getElementById('pasoMovil').style.display = tipo === 'movil' ? 'block' : 'none';

  document.getElementById('grupoTipoVentaMovil').style.display = tipo === 'movil' ? 'block' : 'none';
  document.getElementById('grupoTipoVentaFija').style.display  = tipo === 'fija'  ? 'block' : 'none';

  // Ocultar opciones que no aplican a fija
  document.querySelector('.bubble[onclick*="centralizado"]').style.display  = tipo === 'fija' ? 'none' : '';
  document.querySelector('.bubble[onclick*="almacen_propio"]').style.display = tipo === 'fija' ? 'none' : '';

  // Teléfonos condicionales
  document.getElementById('grupoTelfSot').style.display       = tipo === 'fija'  ? 'block' : 'none';
  document.getElementById('grupoTelfBiometria').style.display = tipo === 'movil' ? 'block' : 'none';

  // Biometría
  document.getElementById('alertBiometria').style.display = tipo === 'movil' ? 'block' : 'none';
  document.getElementById('hintBiometria').style.display  = tipo === 'movil' ? 'inline' : 'none';

  // Defaults por tipo (solo si no estamos restaurando un envío previo)
  if (!HAS_SERVER_ERRORS) {
    if (tipo === 'fija') {
      const pdv = document.querySelector('.bubble[onclick*="tipo_ingreso"][onclick*="pdv"]');
      if (pdv) setBubble(pdv, 'tipo_ingreso', 'pdv');
      const alta = document.querySelector('#grupoTipoVentaFija .bubble[onclick*="alta"]');
      if (alta) { setBubble(alta, 'tipo_venta_fija', 'alta'); togglePortaFija(false); }
      setTimeout(() => {
        const ftth = document.querySelector('.bubble[onclick*="ftth"]');
        if (ftth && !ftth.classList.contains('active')) setBubble(ftth, 'tecnologia', 'ftth');
        const sol = document.querySelector('.bubble[onclick*="1_sol"]');
        if (sol && !sol.classList.contains('active')) setBubble(sol, 'campana_fija', '1_sol');
      }, 50);
    }

    if (tipo === 'movil') {
      const pdv = document.querySelector('.bubble[onclick*="tipo_ingreso"][onclick*="pdv"]');
      if (pdv) setBubble(pdv, 'tipo_ingreso', 'pdv');
      onTipoIngresoChange('pdv');
      const hoy = new Date().toISOString().split('T')[0];
      document.getElementById('inputFechaDespacho').value = hoy;
      const sla = document.getElementById('bubbleSla3h');
      if (sla && !sla.classList.contains('active')) setBubble(sla, 'rango_horario', 'sla_3h');
      setTimeout(() => {
        const porta = document.querySelector('#grupoTipoVentaMovil .bubble[onclick*="porta"]');
        if (porta) { setBubble(porta, 'tipo_venta_movil', 'porta'); togglePortaMovil('porta'); }
        if (document.getElementById('lineasBody').children.length === 0) addLinea();
      }, 50);
    }
  }

  updateProgress();
  checkSubmit();

  // Scroll suave al paso 2
  if (!HAS_SERVER_ERRORS) setTimeout(() => scrollToCard('c2'), 100);
}

// ── BUBBLES ─────────────────────────────────────────
function setBubble(el, name, value) {
  el.closest('.bgroup').querySelectorAll('.bubble').forEach(b =>
    b.classList.remove('active','active-green','active-orange')
  );
  el.classList.add('active');
  const input = document.querySelector(`input[name="${name}"]`);
  if (input) input.value = value;
  updateProgress();
  checkSubmit();
}

// ── DOCUMENTO ───────────────────────────────────────
function setDocLimit(tipo) {
  const input = document.getElementById('inputNroDoc');
  input.maxLength  = tipo === 'dni' ? 8 : 9;
  input.placeholder = tipo === 'dni' ? '8 dígitos' : '00 + 7 dígitos';
}

// ── COORDS GEODIR ────────────────────────────────────
function splitGeodirPaste() {
  const xInput = document.getElementById('inputGeoX');
  const yInput = document.getElementById('inputGeoY');
  const raw = xInput.value.trim();
  const match = raw.match(/^(-?\d+\.?\d*)[,\s]+(-?\d+\.?\d*)$/);
  if (match) { xInput.value = match[1]; yInput.value = match[2]; }
  syncGeodirCoords();
}
function syncGeodirCoords() {
  const x = document.getElementById('inputGeoX').value.trim();
  const y = document.getElementById('inputGeoY').value.trim();
  document.getElementById('inputCoordsGeodirFinal').value = (x && y) ? `${x}, ${y}` : (x || y);
}

// ── PORTA MÓVIL ──────────────────────────────────────
function togglePortaMovil(tipo) {
  const esPorta = tipo === 'porta';
  const esAlta  = tipo === 'alta';

  document.querySelectorAll('.col-porta').forEach(el => el.style.display = esPorta ? '' : 'none');
  document.querySelectorAll('#lineasBody tr').forEach(tr => {
    tr.querySelectorAll('.td-porta').forEach(td => td.style.display = esPorta ? '' : 'none');
  });

  document.getElementById('grupoLarge').style.display = esAlta ? 'block' : 'none';
}

// ── TIPO ENTREGA ─────────────────────────────────────
function onTipoEntregaChange(valor) {
  const esCAC = valor === 'recojo_cac';
  document.getElementById('grupoDelivery').style.display = esCAC ? 'none' : 'block';
  document.getElementById('grupoCac').style.display      = esCAC ? 'block' : 'none';
  if (!esCAC) {
    document.getElementById('inputCacBusqueda').value = '';
    document.getElementById('inputCacId').value = '';
    document.getElementById('grupoCacSeleccionado').style.display = 'none';
    document.getElementById('cacDropdown').style.display = 'none';
  }
}

// ── CAC ──────────────────────────────────────────────
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
        dropdown.innerHTML = '<div class="cac-opt"><span class="cac-opt-name" style="color:var(--text-faint);">Sin resultados</span></div>';
      } else {
        data.forEach(cac => {
          const div = document.createElement('div');
          div.className = 'cac-opt';
          div.innerHTML = `<div class="cac-opt-name">${cac.nombre}</div><div class="cac-opt-dir">${cac.direccion}</div>`;
          div.onclick = () => seleccionarCac(cac);
          dropdown.appendChild(div);
        });
      }
      dropdown.style.display = 'block';
    } catch(e) { console.error(e); }
  }, 300);
}
function seleccionarCac(cac) {
  document.getElementById('inputCacId').value        = cac.id;
  document.getElementById('inputCacBusqueda').value  = cac.nombre;
  document.getElementById('inputCacNombre').value    = cac.nombre;
  document.getElementById('inputCacDireccion').value = cac.direccion;
  document.getElementById('grupoCacSeleccionado').style.display = 'block';
  document.getElementById('cacDropdown').style.display = 'none';
  document.querySelector('input[name="direccion_facturacion_movil"]').value = cac.direccion;
}
document.addEventListener('click', e => {
  if (!e.target.closest('#inputCacBusqueda') && !e.target.closest('#cacDropdown'))
    document.getElementById('cacDropdown').style.display = 'none';
});

// ── PORTA FIJA ───────────────────────────────────────
function togglePortaFija(esPorta) {
  document.getElementById('grupoPortaFija').style.display = esPorta ? 'block' : 'none';
}

// ── FULL CLARO ───────────────────────────────────────
function toggleFullClaro(aplica) {
  document.getElementById('grupoFullClaro').style.display = aplica ? 'block' : 'none';
}

// ── PLANES FIJA ──────────────────────────────────────
const PLANES = {
  internet:  [
    { label: 'Internet 200MB',  field: 'hPlanInt200'  },
    { label: 'Internet 400MB',  field: 'hPlanInt400'  },
    { label: 'Internet 1500MB', field: 'hPlanInt1500' },
  ],
  telefonia: [{ label: 'Telefonía 5000', field: 'hPlanTelefonia' }],
  cable:     [
    { label: 'Cable TV Estándar', field: 'hPlanCableStandar' },
    { label: 'Cable TV Superior', field: 'hPlanCableSup' },
  ],
};

// Mapa id-de-hidden -> name-del-campo, para poder restaurar desde OLD_DATA
const PLAN_FIELD_BY_ID = {
  hPlanTelefonia:    'plan_telefonia',
  hPlanCableStandar: 'plan_cable_standar',
  hPlanCableSup:     'plan_cable_superior',
  hPlanInt200:       'plan_internet_200',
  hPlanInt400:       'plan_internet_400',
  hPlanInt1500:      'plan_internet_1500',
};

function resetPlanHiddens() {
  ['hPlanTelefonia','hPlanCableStandar','hPlanCableSup',
   'hPlanInt200','hPlanInt400','hPlanInt1500'].forEach(id => {
    document.getElementById(id).value = '0';
  });
}

function buildComboSelect(label, opciones, idx, required) {
  const req = required ? '<span style="color:var(--red);margin-left:2px;">*</span>' : '';
  const opts = opciones.map(o => `<option value="${o.field}">${o.label}</option>`).join('');
  return `
    <div class="fgroup" style="margin-bottom:10px;">
      <label class="flabel" style="font-size:10px;">${label}${req}</label>
      <select class="finput combo-plan" data-idx="${idx}" onchange="onComboChange()">
        <option value="">— Seleccionar —</option>
        ${opts}
      </select>
    </div>`;
}

function renderCombosPlay(play, presetFields) {
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
    container.innerHTML = buildComboSelect('Servicio', [...PLANES.internet, ...PLANES.telefonia, ...PLANES.cable], 0, true);
  }
  if (play === '2play') {
    container.innerHTML =
      buildComboSelect('Internet (obligatorio)', PLANES.internet, 0, true) +
      buildComboSelect('Telefonía o Cable', [...PLANES.telefonia, ...PLANES.cable], 1, true);
  }
  if (play === '3play') {
    container.innerHTML =
      buildComboSelect('Internet (obligatorio)', PLANES.internet,  0, true) +
      buildComboSelect('Telefonía',              PLANES.telefonia, 1, true) +
      buildComboSelect('Cable TV',               PLANES.cable,     2, true);
  }

  // Restaurar selección previa (old() o venta) si corresponde.
  // Buscamos, para cada campo preseleccionado, el primer select libre que
  // realmente tenga esa opción — no por posición, porque el orden de
  // PLAN_FIELD_BY_ID no coincide con el orden de los selects para 2play/3play.
  if (presetFields && presetFields.length) {
    const selects = Array.from(document.querySelectorAll('.combo-plan'));
    presetFields.forEach(fieldId => {
      const sel = selects.find(s => !s.value && Array.from(s.options).some(o => o.value === fieldId));
      if (sel) sel.value = fieldId;
    });
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

// ── LÍNEAS MÓVIL ─────────────────────────────────────
let lineaCount = 0;
function addLinea(datos) {
  const i = lineaCount++;
  const tipoVenta = document.getElementById('inputTipoVentaMovil')?.value || '';
  const esPorta   = tipoVenta === 'porta';
  const d = datos || {};

  const tr = document.createElement('tr');
  tr.innerHTML = `
    <td class="td-porta" style="${esPorta ? '' : 'display:none'}">
      <input type="text" name="lineas[${i}][nro_portar]" class="linea-input" value="${d.nro_portar ?? ''}" placeholder="9XX XXX XXX">
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
      <select name="lineas[${i}][operador_cedente]" class="linea-select" onchange="toggleOperadorOtro(this,${i})">
        <option value="">— Op. —</option>
        <option value="entel">Entel</option>
        <option value="movistar">Movistar</option>
        <option value="bitel">Bitel</option>
        <option value="otros">Otros</option>
      </select>
      <input type="text" name="lineas[${i}][operador_cedente_otro]" class="linea-input" id="opOtro_${i}"
             value="${d.operador_cedente_otro ?? ''}" placeholder="¿Cuál?" style="display:none;margin-top:4px;">
    </td>
    <td>
      <select name="lineas[${i}][equipo_sim]" class="linea-select" onchange="toggleModeloEquipo(this,${i})">
        <option value="sim_card">SIM Card</option>
        <option value="sim_card_equipo">SIM + Equipo</option>
      </select>
    </td>
    <td>
      <input type="text" name="lineas[${i}][modelo_equipo]" class="linea-input" id="modeloEquipo_${i}"
             value="${d.modelo_equipo ?? ''}" placeholder="Ej: iPhone 13" style="display:none">
    </td>
    <td>
      <select name="lineas[${i}][descuento]" class="linea-select" onchange="toggleWf(this,${i})">
        <option value="no_aplica">No aplica</option>
        <option value="50%">50%</option>
        <option value="bajo_plantilla" ${document.querySelector('input[name=\'tipo_ingreso\']')?.value === 'centralizado' ? '' : 'disabled'}>Bajo plantilla</option>
      </select>
    </td>
    <td>
      <input type="text" name="lineas[${i}][nro_wf]" class="linea-input" id="wf_${i}"
             value="${d.nro_wf ?? ''}" placeholder="6 dígitos" maxlength="6" style="display:none">
    </td>
    <td>
      <button type="button" class="btn-remove-linea" onclick="removeLinea(this)">×</button>
    </td>
  `;
  document.getElementById('lineasBody').appendChild(tr);

  // Restaurar selects (plan, operador_cedente, equipo_sim, descuento)
  if (d.plan) tr.querySelector(`select[name="lineas[${i}][plan]"]`).value = d.plan;
  if (d.operador_cedente) {
    const selOp = tr.querySelector(`select[name="lineas[${i}][operador_cedente]"]`);
    selOp.value = d.operador_cedente;
    toggleOperadorOtro(selOp, i);
  }
  if (d.equipo_sim) {
    const selEquipo = tr.querySelector(`select[name="lineas[${i}][equipo_sim]"]`);
    selEquipo.value = d.equipo_sim;
    toggleModeloEquipo(selEquipo, i);
  }
  if (d.descuento) {
    const selDesc = tr.querySelector(`select[name="lineas[${i}][descuento]"]`);
    selDesc.value = d.descuento;
    toggleWf(selDesc, i);
  }
}

function removeLinea(btn) {
  const tbody = document.getElementById('lineasBody');
  if (tbody.children.length > 1) btn.closest('tr').remove();
}

function toggleWf(select, i) {
  document.getElementById(`wf_${i}`).style.display = select.value === 'bajo_plantilla' ? 'block' : 'none';
}

function toggleOperadorOtro(select, i) {
  document.getElementById(`opOtro_${i}`).style.display = select.value === 'otros' ? 'block' : 'none';
}

function toggleModeloEquipo(select, i) {
  document.getElementById(`modeloEquipo_${i}`).style.display = select.value === 'sim_card_equipo' ? 'block' : 'none';
}

// ── TIPO INGRESO ─────────────────────────────────────
function onTipoIngresoChange(valor) {
  const esCentralizado  = valor === 'centralizado';
  const esAlmacenPropio = valor === 'almacen_propio';

  document.querySelectorAll('select[name$="[descuento]"]').forEach(select => {
    const opt = select.querySelector('option[value="bajo_plantilla"]');
    if (!opt) return;
    opt.disabled = !esCentralizado;
    if (!esCentralizado && select.value === 'bajo_plantilla') {
      select.value = 'no_aplica';
      const i = select.name.match(/\[(\d+)\]/)?.[1];
      if (i !== undefined) toggleWf(select, i);
    }
  });

  const rowCoords = document.getElementById('inputGeoX')?.closest('.frow');
  if (rowCoords) rowCoords.style.display = esAlmacenPropio ? 'none' : '';

  [
    document.querySelector('[name="plano_geodir"]')?.closest('.fgroup'),
    document.querySelector('[name="direccion_entrega"]')?.closest('.fgroup'),
    document.querySelector('[name="referencias_entrega"]')?.closest('.fgroup'),
  ].forEach(g => { if (g) g.style.display = esAlmacenPropio ? 'none' : ''; });

  const motorizado = document.querySelector('[name="telefono_referencia_movil"]')?.closest('.fgroup');
  if (motorizado) motorizado.style.display = esAlmacenPropio ? 'none' : '';
}

// ── DOCUMENTOS ───────────────────────────────────────
function previewDocs(input) {
  const list = document.getElementById('docList');
  Array.from(input.files).forEach(file => {
    const item = document.createElement('div');
    item.className = 'doc-item';
    item.innerHTML = `
      <span>📄</span>
      <span>${file.name}</span>
      <span style="color:var(--text-faint);font-size:11px;">${(file.size/1024).toFixed(0)} KB</span>
      <button type="button" class="doc-item-remove" onclick="this.parentElement.remove()">×</button>
    `;
    list.appendChild(item);
  });
}

// ── GUARDAR LEAD ─────────────────────────────────────
function toggleGuardarLead(label) {
  label.classList.toggle('active');
  document.getElementById('chkGuardarLead').checked = label.classList.contains('active');
}

// ── PROGRESO ─────────────────────────────────────────
function updateProgress() {
  const tipo        = document.getElementById('inputTipo').value;
  const tipoIngreso = document.querySelector('input[name="tipo_ingreso"]')?.value;
  const nombreRep   = document.querySelector('input[name="nombre_representante"]')?.value?.trim();
  const tipoDoc     = document.querySelector('input[name="tipo_documento"]')?.value;
  const nroDoc      = document.getElementById('inputNroDoc')?.value?.trim();

  let done = 0;
  if (tipo) done++;
  if (tipoIngreso) done++;
  if (nombreRep && tipoDoc && nroDoc) done++;

  if (tipo === 'fija') {
    const dir  = document.querySelector('input[name="direccion_instalacion"]')?.value?.trim();
    const tech = document.querySelector('input[name="tecnologia"]')?.value;
    if (dir && tech) done++;
  }
  if (tipo === 'movil') {
    const entrega = document.querySelector('input[name="tipo_entrega"]')?.value;
    const campana = document.querySelector('input[name="campana_movil"]')?.value;
    if (entrega && campana) done++;
  }

  const docs = document.getElementById('docList')?.children?.length > 0;
  if (docs) done++;

  const pct = Math.round((done / 5) * 100);
  document.getElementById('progressFill').style.width = pct + '%';
  document.getElementById('progressLabel').textContent = `${done} de 5 pasos`;

  // Actualizar stepper
  for (let s = 1; s <= 5; s++) {
    const si = document.getElementById(`si${s}`);
    if (!si) continue;
    si.classList.remove('active','done');
    if (s < done + 1) si.classList.add('done');
    else if (s === done + 1) si.classList.add('active');
  }
}

// ── SCROLL A CARD ────────────────────────────────────
function scrollToCard(id) {
  const el = document.getElementById(id);
  if (el && el.style.display !== 'none') {
    el.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
}

// ── VALIDACIÓN ───────────────────────────────────────
function getValidationErrors() {
  const tipo        = document.getElementById('inputTipo').value;
  const tipoIngreso = document.querySelector('input[name="tipo_ingreso"]')?.value;
  const errors      = [];

  if (!tipo)        { errors.push('Selecciona el tipo de servicio (Móvil o Fija).'); return errors; }
  if (!tipoIngreso) { errors.push('Selecciona el tipo de ingreso.'); }

  // Validar RUC y razón social si es venta directa
  const inputRuc = document.querySelector('input[name="ruc"]');
  if (inputRuc && !inputRuc.closest('[readonly]')) {
    if (!inputRuc.value.trim()) errors.push('Ingresa el RUC de la empresa.');
    if (!document.querySelector('input[name="razon_social"]')?.value?.trim()) errors.push('Ingresa la razón social.');
  }
  // Nota: nombre_representante, telefono_representante y correo se validan más abajo,
  // usando siempre los campos del Paso 3 (son el único origen de estos datos,
  // tanto para crear el lead nuevo como para la venta).

  const nombreRep = document.querySelector('input[name="nombre_representante"]')?.value?.trim();
  const tipoDoc   = document.querySelector('input[name="tipo_documento"]')?.value;
  const nroDoc    = document.getElementById('inputNroDoc')?.value?.trim();
  const telefono  = document.querySelector('input[name="telefono_representante"]')?.value?.trim();
  const correo    = document.querySelector('input[name="correo"]')?.value?.trim();

// Validaciones de formato
const ruc = document.querySelector('input[name="ruc"]')?.value?.trim();

if (ruc && !/^\d{11}$/.test(ruc)) {
    errors.push('El RUC debe tener exactamente 11 dígitos.');
}

if (telefono && !/^9\d{8}$/.test(telefono)) {
    errors.push('El teléfono debe tener 9 dígitos y empezar con 9.');
}

if (correo && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo)) {
    errors.push('El correo electrónico no tiene un formato válido.');
}

  if (!nombreRep) errors.push('Ingresa el nombre del representante.');
  if (!tipoDoc)   errors.push('Selecciona el tipo de documento.');
  if (!nroDoc)    errors.push('Ingresa el número de documento.');
  if (!telefono)  errors.push('Ingresa el teléfono del representante.');
  if (!correo)    errors.push('Ingresa el correo electrónico.');

  if (tipo === 'fija') {
    if (!document.querySelector('input[name="tipo_venta_fija"]')?.value)      errors.push('Selecciona el tipo de venta fija.');
    if (!document.querySelector('input[name="direccion_instalacion"]')?.value?.trim()) errors.push('Ingresa la dirección de instalación.');
    if (!document.querySelector('input[name="tecnologia"]')?.value)           errors.push('Selecciona la tecnología.');
    if (!document.querySelector('input[name="campana_fija"]')?.value)         errors.push('Selecciona la campaña fija.');
    if (!document.querySelector('input[name="tipo_producto_fija"]')?.value)   errors.push('Selecciona el tipo de producto.');
    document.querySelectorAll('.combo-plan').forEach(sel => {
      if (!sel.value) errors.push('Selecciona todos los planes del servicio fijo.');
    });
  }

  if (tipo === 'movil') {
    if (!document.querySelector('input[name="tipo_venta_movil"]')?.value)  errors.push('Selecciona el tipo de venta móvil.');
    if (!document.querySelector('input[name="tipo_entrega"]')?.value)      errors.push('Selecciona el tipo de entrega.');
    if (!document.querySelector('input[name="campana_movil"]')?.value)     errors.push('Selecciona la campaña móvil.');
    if (!document.querySelector('input[name="fecha_despacho"]')?.value)    errors.push('Ingresa la fecha de despacho.');
    if (!document.querySelector('input[name="rango_horario"]')?.value)     errors.push('Selecciona el rango horario.');

    const entrega = document.querySelector('input[name="tipo_entrega"]')?.value;
    if (entrega === 'delivery') {
      if (!document.querySelector('input[name="direccion_entrega"]')?.value?.trim())
        errors.push('Ingresa la dirección de entrega.');
    }
    if (entrega === 'recojo_cac') {
      if (!document.getElementById('inputCacId')?.value)
        errors.push('Selecciona un CAC para el recojo.');
    }

    const lineas = document.querySelectorAll('#lineasBody tr');
    if (!lineas.length) {
      errors.push('Agrega al menos una línea.');
    } else {
      let sinPlan = false;
      lineas.forEach(tr => { if (!tr.querySelector('select[name*="[plan]"]')?.value) sinPlan = true; });
      if (sinPlan) errors.push('Todas las líneas deben tener un plan.');
    }
  }

  return errors;
}

function showClientErrors(errors) {
  const prev = document.getElementById('clientErrors');
  if (prev) prev.remove();
  if (!errors.length) return true;

  const div = document.createElement('div');
  div.id = 'clientErrors';
  div.className = 'err-box';
  div.style.marginBottom = '12px';
  div.innerHTML = `
    <div class="err-box-title">⚠ Completa los campos requeridos:</div>
    <ul>${errors.map(e => `<li>${e}</li>`).join('')}</ul>
  `;
  const scard = document.getElementById('btnSubmit').closest('.scard');
  scard.insertBefore(div, scard.firstChild);
  div.scrollIntoView({ behavior: 'smooth', block: 'center' });
  return false;
}

// ── CHECK SUBMIT ─────────────────────────────────────
function checkSubmit() {
  const tipo        = document.getElementById('inputTipo').value;
  const tipoIngreso = document.querySelector('input[name="tipo_ingreso"]')?.value;
  const btn  = document.getElementById('btnSubmit');
  const hint = document.getElementById('submitHint');
  const listo = tipo && tipoIngreso;
  btn.disabled = !listo;
  if (hint) hint.style.display = listo ? 'none' : 'block';
  updateProgress();
}

// ── SUBMIT ───────────────────────────────────────────
document.getElementById('formVenta').addEventListener('submit', function(e) {
  const errors = getValidationErrors();
  if (errors.length) { e.preventDefault(); showClientErrors(errors); return; }
  const btn = document.getElementById('btnSubmit');
  btn.disabled    = true;
  btn.textContent = 'Enviando…';
});

document.getElementById('formVenta').addEventListener('input',  checkSubmit);
document.getElementById('formVenta').addEventListener('change', checkSubmit);

// ── RESTAURAR TODO EL ESTADO DEL WIZARD TRAS UN ERROR DEL SERVIDOR ──
function restoreBubbleFromOld(fieldName) {
  if (!OLD_DATA[fieldName]) return null;
  const bubble = Array.from(document.querySelectorAll('.bubble')).find(b => {
    const m = b.getAttribute('onclick')?.match(new RegExp(`setBubble\\(this,\\s*'${fieldName}',\\s*'([^']+)'\\)`));
    return m && m[1] === OLD_DATA[fieldName];
  });
  if (bubble) {
    // Ejecuta exactamente lo que dispararía el click (incluye togglePorta*, onTipo*, etc.)
    // eslint-disable-next-line no-new-func
    new Function(bubble.getAttribute('onclick')).call(bubble);
  }
  return OLD_DATA[fieldName];
}

function restoreOldData() {
  if (!OLD_DATA || Object.keys(OLD_DATA).length === 0) return;

  // 1) Texto/número/fecha/select por name (los que ya tienen old() en Blade
  //    quedan cubiertos; esto refuerza cualquier campo que no lo tuviera)
  document.querySelectorAll('#formVenta input[name], #formVenta select[name]').forEach(el => {
    if (['hidden','file','checkbox'].includes(el.type)) return;
    const val = OLD_DATA[el.name];
    if (val !== undefined && !el.value) el.value = val;
  });

  // 2) Tipo de servicio → dispara la cascada normal (deja visibles los pasos)
  if (OLD_DATA.tipo) setTipo(OLD_DATA.tipo);

  // 3) Burbujas simples
  ['tipo_ingreso','tipo_venta_movil','tipo_venta_fija','tipo_documento',
   'tecnologia','campana_fija','campana_movil','full_claro',
   'tipo_entrega','rango_horario'].forEach(restoreBubbleFromOld);

  if (OLD_DATA.tipo_documento) setDocLimit(OLD_DATA.tipo_documento);

  // 4) Tipo de producto fija + combos de planes
  if (OLD_DATA.tipo_producto_fija) {
    const presetFields = Object.entries(PLAN_FIELD_BY_ID)
      .filter(([, name]) => OLD_DATA[name] === '1')
      .map(([id]) => id);
    restoreBubbleFromOld('tipo_producto_fija');
    renderCombosPlay(OLD_DATA.tipo_producto_fija, presetFields);
  }

  // 5) CAC seleccionado (móvil, recojo en CAC)
  if (OLD_DATA.tipo_entrega === 'recojo_cac' && OLD_DATA.cac_id) {
    document.getElementById('inputCacId').value = OLD_DATA.cac_id;
    // No tenemos nombre/dirección sin volver a consultar al backend;
    // se deja el id guardado para no perder la selección al reenviar.
  }

  // 6) Coordenadas de delivery (separadas en dos inputs visuales)
  if (OLD_DATA.coordenadas_geodir) {
    const [lat, lng] = OLD_DATA.coordenadas_geodir.split(',').map(s => s.trim());
    if (lat) document.getElementById('inputGeoX').value = lat;
    if (lng) document.getElementById('inputGeoY').value = lng;
  }

  // 7) Líneas móviles
  if (Array.isArray(OLD_DATA.lineas) && OLD_DATA.lineas.length) {
    document.getElementById('lineasBody').innerHTML = '';
    lineaCount = 0;
    OLD_DATA.lineas.forEach(linea => addLinea(linea));
    togglePortaMovil(OLD_DATA.tipo_venta_movil || '');
  }

  updateProgress();
  checkSubmit();

  document.getElementById('serverErrors')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

@if($errors->any())
window.addEventListener('DOMContentLoaded', () => {
  restoreOldData();
  document.getElementById('docsRestoreNotice').style.display = 'block';
});
@elseif($venta)
window.addEventListener('DOMContentLoaded', () => {
  restoreOldData();
});
@endif
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {

    const ruc = document.getElementById('rucEmpresa');
    const razon = document.getElementById('razonEmpresa');

    const previewRuc = document.getElementById('previewRuc');
    const previewRazon = document.getElementById('previewRazon');

    function syncEmpresa() {
        if (ruc && previewRuc) {
            previewRuc.value = ruc.value;
        }

        if (razon && previewRazon) {
            previewRazon.value = razon.value;
        }
    }

    ruc?.addEventListener('input', syncEmpresa);
    razon?.addEventListener('input', syncEmpresa);

    syncEmpresa();
});