<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <meta name="csrf-token" content="{{ csrf_token() }}"/>
  <script>
  if (localStorage.getItem('theme') === 'light') {
    document.documentElement.classList.add('light');
  }
</script>
  <title>{{ config('app.name', 'CRM') }}</title>
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;600&display=swap" rel="stylesheet"/>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: 'Sora', sans-serif;
      background: #0f0f13;
      color: #fff;
      min-height: 100vh;
      display: flex;
    }

    /* ── SIDEBAR ── */
    .sidebar {
      width: 220px;
      background: #15151c;
      border-right: 1px solid rgba(255,255,255,0.07);
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      position: fixed;
      top: 0; left: 0;
      z-index: 100;
    }

    .sidebar-logo {
      padding: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
      border-bottom: 1px solid rgba(255,255,255,0.07);
    }

    .logo-box {
      width: 32px; height: 32px;
      background: #fff;
      border-radius: 8px;
      display: flex; align-items: center; justify-content: center;
      overflow: hidden;
      flex-shrink: 0;
    }

    .logo-box img { width: 100%; height: 100%; object-fit: contain; }

    .sidebar-logo span {
      font-size: 14px;
      font-weight: 600;
      color: #fff;
    }

    .nav-section { padding: 12px; }

    .nav-label {
      font-size: 10px;
      font-weight: 600;
      color: rgba(255,255,255,0.25);
      letter-spacing: 1px;
      text-transform: uppercase;
      padding: 0 8px;
      margin: 12px 0 6px;
    }

    .nav-item {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 9px 10px;
      border-radius: 8px;
      font-size: 13px;
      color: rgba(255,255,255,0.5);
      text-decoration: none;
      transition: all 0.2s;
      margin-bottom: 2px;
    }

    .nav-item:hover { background: rgba(255,255,255,0.05); color: rgba(255,255,255,0.85); }

    .nav-item.active {
      background: rgba(47,202,245,0.12);
      color: #2FCAF5;
    }

    .nav-item svg { width: 16px; height: 16px; flex-shrink: 0; }

    .sidebar-footer {
      margin-top: auto;
      padding: 12px;
      border-top: 1px solid rgba(255,255,255,0.07);
    }

    .user-info {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px;
      border-radius: 8px;
    }

    .user-avatar {
      width: 30px; height: 30px;
      border-radius: 50%;
      background: rgba(47,202,245,0.15);
      display: flex; align-items: center; justify-content: center;
      font-size: 11px;
      font-weight: 600;
      color: #2FCAF5;
      flex-shrink: 0;
    }

    .user-name { font-size: 12px; font-weight: 600; color: #fff; }
    .user-role { font-size: 11px; color: rgba(255,255,255,0.35); }

    .btn-logout {
      display: flex;
      align-items: center;
      gap: 8px;
      width: 100%;
      padding: 8px 10px;
      background: none;
      border: none;
      border-radius: 8px;
      font-size: 12px;
      color: rgba(255,255,255,0.35);
      cursor: pointer;
      font-family: 'Sora', sans-serif;
      transition: all 0.2s;
      margin-top: 4px;
    }

    .btn-logout:hover { background: rgba(255,80,80,0.08); color: #ff6b6b; }

    /* ── MAIN ── */
    .main-wrapper {
      margin-left: 220px;
      flex: 1;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    .topbar {
      height: 56px;
      border-bottom: 1px solid rgba(255,255,255,0.07);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 28px;
      background: #0f0f13;
      position: sticky;
      top: 0;
      z-index: 50;
    }

    .topbar-title { font-size: 15px; font-weight: 600; color: #fff; }
    .topbar-sub { font-size: 12px; color: rgba(255,255,255,0.35); margin-top: 1px; }

    .page-content { padding: 28px; flex: 1; }

    /* ── ALERTS ── */
    .alert-success {
      padding: 12px 16px;
      background: rgba(29,158,117,0.12);
      border: 1px solid rgba(29,158,117,0.25);
      border-radius: 10px;
      color: #5dcaa5;
      font-size: 13px;
      margin-bottom: 20px;
    }

    .alert-error {
      padding: 12px 16px;
      background: rgba(255,80,80,0.12);
      border: 1px solid rgba(255,80,80,0.25);
      border-radius: 10px;
      color: #ff9090;
      font-size: 13px;
      margin-bottom: 20px;
    }

    /* ── MODO CLARO ── */
html.light body { background: #f0f7ff; color: #0f0f13; }
html.light .sidebar { background: #ffffff; border-right: 1px solid #d0eaf8; }
html.light .sidebar-logo { border-bottom: 1px solid #d0eaf8; }
html.light .sidebar-logo span { color: #0f0f13; }
html.light .nav-label { color: rgba(0,0,0,0.3); }
html.light .nav-item { color: rgba(0,0,0,0.5); }
html.light .nav-item:hover { background: rgba(47,202,245,0.07); color: #0f0f13; }
html.light .nav-item.active { background: rgba(47,202,245,0.15); color: #2FCAF5; }
html.light .sidebar-footer { border-top: 1px solid #d0eaf8; }
html.light .user-name { color: #0f0f13; }
html.light .user-role { color: rgba(0,0,0,0.4); }
html.light .btn-logout { color: rgba(0,0,0,0.4); }
html.light .btn-logout:hover { background: rgba(255,80,80,0.07); color: #cc3333; }
html.light .main-wrapper { background: #f0f7ff; }
html.light .topbar { background: #ffffff; border-bottom: 1px solid #d0eaf8; }
html.light .topbar-title { color: #0f0f13; }
html.light .topbar-sub { color: rgba(0,0,0,0.4); }
html.light .alert-success { background: rgba(29,158,117,0.08); border-color: rgba(29,158,117,0.2); }
html.light .alert-error { background: rgba(255,80,80,0.08); border-color: rgba(255,80,80,0.2); }
  </style>
</head>
<body>

{{-- SIDEBAR --}}
<div class="sidebar">
  <div class="sidebar-logo">
    <div class="logo-box">
      <img src="{{ asset('images/logo.jpg') }}" alt="Logo"/>
    </div>
    <span>{{ config('app.name', 'CRM') }}</span>
  </div>

  <div class="nav-section">
  <div class="nav-label">Principal</div>

  <a href="{{ route('dashboard') }}"
     class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
    <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
      <rect x="2" y="2" width="5" height="5" rx="1"/>
      <rect x="9" y="2" width="5" height="5" rx="1"/>
      <rect x="2" y="9" width="5" height="5" rx="1"/>
      <rect x="9" y="9" width="5" height="5" rx="1"/>
    </svg>
    Dashboard
  </a>

  @if(auth()->user()->isAdmin())
    <a href="{{ route('admin.users.index') }}"
       class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
      <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
        <circle cx="6.5" cy="5" r="3"/>
        <path d="M1 14c0-3 2.5-5 5.5-5s5.5 2 5.5 5"/>
        <path d="M11 2.5a3 3 0 010 5"/>
        <path d="M15 14c0-2-1-3.5-3-4.5"/>
      </svg>
      Usuarios
    </a>
    <a href="{{ route('admin.leads.index') }}"
       class="nav-item {{ request()->routeIs('admin.leads.*') ? 'active' : '' }}">
      <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
        <circle cx="8" cy="6" r="3"/>
        <path d="M2 14c0-3 2.5-6 6-5s6 2 6 5"/>
      </svg>
      Leads
    </a>
    <a href="{{ route('admin.ventas.index') }}"
       class="nav-item {{ request()->routeIs('admin.ventas.*') ? 'active' : '' }}">
      <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
        <path d="M2 4h12v9a1 1 0 01-1 1H3a1 1 0 01-1-1V4z"/>
        <path d="M5 4V3a1 1 0 011-1h4a1 1 0 011 1v1"/>
        <path d="M6 8h4M6 11h2"/>
      </svg>
      Ventas
    </a>
    <a href="{{ route('admin.cacs.index') }}"
       class="nav-item {{ request()->routeIs('admin.cacs.*') ? 'active' : '' }}">
      <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
        <path d="M2 14V6l6-4 6 4v8"/>
        <rect x="5" y="9" width="3" height="5"/>
        <rect x="8" y="9" width="3" height="5"/>
      </svg>
      CACs
    </a>
    <a href="#" class="nav-item {{ request()->routeIs('reportes.*') ? 'active' : '' }}">
      <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
        <path d="M2 12l3-4 3 2 3-5 3 3"/>
        <path d="M2 14h12"/>
      </svg>
      Reportes
    </a>
  @endif

  @if(auth()->user()->isJefe() || auth()->user()->isSupervisor())
    <a href="{{ route('admin.leads.index') }}"
       class="nav-item {{ request()->routeIs('admin.leads.*') ? 'active' : '' }}">
      <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
        <circle cx="8" cy="6" r="3"/>
        <path d="M2 14c0-3 2.5-6 6-5s6 2 6 5"/>
      </svg>
      Leads
    </a>
    <a href="#" class="nav-item">
      <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
        <path d="M2 12l3-4 3 2 3-5 3 3"/>
        <path d="M2 14h12"/>
      </svg>
      Reportes
    </a>
  @endif

  @if(auth()->user()->isAsesor())
    <a href="{{ route('asesor.leads.index') }}"
       class="nav-item {{ request()->routeIs('asesor.leads.*') ? 'active' : '' }}">
      <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
        <circle cx="8" cy="6" r="3"/>
        <path d="M2 14c0-3 2.5-5 6-5s6 2 6 5"/>
      </svg>
      Mis leads
    </a>
    <a href="{{ route('asesor.ventas.index') }}"
       class="nav-item {{ request()->routeIs('asesor.ventas.*') ? 'active' : '' }}">
      <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
        <path d="M2 4h12v9a1 1 0 01-1 1H3a1 1 0 01-1-1V4z"/>
        <path d="M5 4V3a1 1 0 011-1h4a1 1 0 011 1v1"/>
        <path d="M6 8h4M6 11h2"/>
      </svg>
      Mis ventas
    </a>
  @endif

  @if(auth()->user()->isMesaControl())
    <a href="{{ route('mesa.ventas.index') }}"
       class="nav-item {{ request()->routeIs('mesa.ventas.*') ? 'active' : '' }}">
      <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
        <path d="M2 4h12v9a1 1 0 01-1 1H3a1 1 0 01-1-1V4z"/>
        <path d="M5 4V3a1 1 0 011-1h4a1 1 0 011 1v1"/>
        <path d="M6 8h4M6 11h2"/>
      </svg>
      Ventas
    </a>
  @endif
</div>

  <div class="sidebar-footer">
    <div class="user-info">
      <div class="user-avatar">
        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
      </div>
      <div>
        <div class="user-name">{{ auth()->user()->name }}</div>
        <div class="user-role">{{ ucfirst(auth()->user()->role) }}</div>
      </div>
    </div>
    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="btn-logout">
        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
          <path d="M6 2H3a1 1 0 00-1 1v10a1 1 0 001 1h3"/>
          <path d="M11 11l3-3-3-3"/>
          <path d="M14 8H6"/>
        </svg>
        Cerrar sesión
      </button>
    </form>
  </div>
</div>

{{-- MAIN --}}
<div class="main-wrapper">
  <div class="topbar">
    <div>
      <div class="topbar-title">@yield('title', 'Dashboard')</div>
      <div class="topbar-sub">@yield('subtitle', '')</div>
    </div>
    <div style="display:flex; align-items:center; gap:12px;">
  @yield('topbar-actions')
  <button onclick="toggleTheme()" id="themeBtn" style="
    background: rgba(255,255,255,0.07);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 8px;
    padding: 7px 10px;
    cursor: pointer;
    display: flex; align-items: center;
    transition: all 0.2s;
  ">
    <svg id="iconSun" width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="#fff" stroke-width="1.5" style="display:none">
      <circle cx="8" cy="8" r="3"/>
      <path d="M8 1v2M8 13v2M1 8h2M13 8h2M3.5 3.5l1.5 1.5M11 11l1.5 1.5M3.5 12.5l1.5-1.5M11 5l1.5-1.5"/>
    </svg>
    <svg id="iconMoon" width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="#fff" stroke-width="1.5">
      <path d="M13 10A6 6 0 016 3a6 6 0 100 10 6 6 0 007-3z"/>
    </svg>
  </button>
</div>
  </div>

  <div class="page-content">
    @if(session('success'))
      <div class="alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="alert-error">{{ session('error') }}</div>
    @endif

    @yield('content')
  </div>
</div>
<script>
  function toggleTheme() {
    const isLight = document.documentElement.classList.toggle('light');
    localStorage.setItem('theme', isLight ? 'light' : 'dark');
    updateThemeBtn(isLight);
  }

  function updateThemeBtn(isLight) {
    const btn = document.getElementById('themeBtn');
    const sun = document.getElementById('iconSun');
    const moon = document.getElementById('iconMoon');
    if (isLight) {
      btn.style.background = 'rgba(0,0,0,0.05)';
      btn.style.borderColor = 'rgba(0,0,0,0.1)';
      sun.style.display = 'block';
      sun.setAttribute('stroke', '#0f0f13');
      moon.style.display = 'none';
    } else {
      btn.style.background = 'rgba(255,255,255,0.07)';
      btn.style.borderColor = 'rgba(255,255,255,0.1)';
      sun.style.display = 'none';
      moon.style.display = 'block';
      moon.setAttribute('stroke', '#fff');
    }
  }

  const isLight = document.documentElement.classList.contains('light');
  updateThemeBtn(isLight);
</script>
</body>
</html>