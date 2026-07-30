<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login</title>
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;600&display=swap" rel="stylesheet"/>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: 'Sora', sans-serif;
      height: 100vh;
      overflow: hidden;
      background: #111; /* evita el fondo gris mientras carga */
    }

    .login-wrapper {
      width: 100%;
      height: 100vh;
      position: relative;
      overflow: hidden;
      opacity: 0;
      transition: opacity 0.5s ease;
    }

    .login-wrapper.ready { opacity: 1; }

    .slides { position: absolute; inset: 0; }

    .slide {
      position: absolute; inset: 0;
      background-size: cover;
      background-position: center;
      opacity: 0;
      transition: opacity 1.4s ease-in-out;
    }

    .slide.active {
      opacity: 1;
      animation: kenBurns 8s ease-in-out forwards;
    }

    @keyframes kenBurns {
      0%   { transform: scale(1); }
      100% { transform: scale(1.08); }
    }

    .slide::after {
      content: '';
      position: absolute; inset: 0;
      background: linear-gradient(135deg, rgba(0,0,0,0.62) 0%, rgba(0,0,0,0.25) 60%, rgba(0,0,0,0.45) 100%);
    }

    .slide-1 { background-image: url("{{ asset('images/fotogari.png') }}"); }
    .slide-2 { background-image: url("{{ asset('images/paseo.png') }}"); }
    .slide-3 { background-image: url("{{ asset('images/cumpleaños.png') }}"); }
    .slide-4 { background-image: url("{{ asset('images/aniversario.jpeg') }}"); }

    .dots {
      position: absolute;
      bottom: 28px;
      left: 50%;
      transform: translateX(-50%);
      display: flex;
      gap: 7px;
      z-index: 10;
    }

    .dot {
      width: 6px; height: 6px;
      border-radius: 50%;
      background: rgba(255,255,255,0.35);
      transition: all 0.4s;
      cursor: pointer;
    }

    .dot.active {
      background: #fff;
      width: 22px;
      border-radius: 3px;
    }

    .tagline {
      position: absolute;
      bottom: 80px;
      left: 9%;
      z-index: 10;
      max-width: 320px;
    }

    .tagline h1 {
      font-size: 30px;
      font-weight: 600;
      color: #fff;
      line-height: 1.25;
      letter-spacing: -0.5px;
      text-shadow: 0 2px 12px rgba(0,0,0,0.3);
      opacity: 0;
      transform: translateY(10px);
      transition: opacity 0.7s ease, transform 0.7s ease;
    }

    .tagline h1.visible { opacity: 1; transform: translateY(0); }

    .tagline p {
      font-size: 14px;
      color: rgba(255,255,255,0.6);
      margin-top: 6px;
      font-weight: 300;
      opacity: 0;
      transform: translateY(8px);
      transition: opacity 0.7s ease 0.15s, transform 0.7s ease 0.15s;
    }

    .tagline p.visible { opacity: 1; transform: translateY(0); }

    .login-card {
      position: absolute;
      top: 50%;
      right: 50%;
      transform: translate(50%, -50%);
      width: 340px;
      background: rgba(255,255,255,0.10);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border: 1px solid rgba(255,255,255,0.22);
      border-radius: 20px;
      padding: 40px 36px;
      z-index: 20;
      box-shadow: 0 8px 40px rgba(0,0,0,0.35);
    }

    .brand { margin-bottom: 28px; }

    .brand-logo {
      width: 38px; height: 38px;
      background: rgba(255,255,255,0.95);
      border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      margin-bottom: 14px;
    }

    .brand h2 {
      font-size: 21px;
      font-weight: 600;
      color: #fff;
      letter-spacing: -0.3px;
    }

    .brand p {
      font-size: 13px;
      color: rgba(255,255,255,0.5);
      margin-top: 4px;
      font-weight: 300;
    }

    .field { margin-bottom: 14px; }

    .field label {
      display: block;
      font-size: 11px;
      font-weight: 600;
      color: rgba(255,255,255,0.6);
      letter-spacing: 0.8px;
      text-transform: uppercase;
      margin-bottom: 6px;
    }

    .field input {
      width: 100%;
      padding: 11px 14px;
      background: rgba(255,255,255,0.10);
      border: 1px solid rgba(255,255,255,0.18);
      border-radius: 10px;
      color: #fff;
      font-size: 14px;
      font-family: 'Sora', sans-serif;
      outline: none;
      transition: border 0.25s, background 0.25s;
    }

    .field input::placeholder { color: rgba(255,255,255,0.3); }

    .field input:focus {
      border-color: rgba(255,255,255,0.5);
      background: rgba(255,255,255,0.16);
    }

    .forgot {
      text-align: right;
      margin-top: -6px;
      margin-bottom: 22px;
    }

    .forgot a {
      font-size: 12px;
      color: rgba(255,255,255,0.5);
      text-decoration: none;
    }

    .forgot a:hover { color: rgba(255,255,255,0.85); }

    .btn-login {
      width: 100%;
      padding: 13px;
      background: #fff;
      color: #1a1a1a;
      font-family: 'Sora', sans-serif;
      font-size: 14px;
      font-weight: 600;
      border: none;
      border-radius: 10px;
      cursor: pointer;
      transition: opacity 0.2s, transform 0.15s;
    }

    .btn-login:hover { opacity: 0.9; transform: scale(0.99); }
    .btn-login:active { transform: scale(0.97); }

    .alert {
      padding: 10px 14px;
      border-radius: 8px;
      font-size: 12.5px;
      margin-bottom: 16px;
      background: rgba(255,80,80,0.2);
      border: 1px solid rgba(255,80,80,0.35);
      color: #ffd0d0;
    }
  </style>
</head>
<body>

<div class="login-wrapper" id="wrapper">

  <div class="slides">
    <div class="slide slide-1"></div>
    <div class="slide slide-2"></div>
    <div class="slide slide-3"></div>
    <div class="slide slide-4"></div>
  </div>

  <div class="tagline">
    <h1 id="tagline-title">Impulsa tu equipo de ventas</h1>
    <p id="tagline-sub">Gestiona, convierte y crece.</p>
  </div>

  <div class="dots">
    <div class="dot active" onclick="goTo(0)"></div>
    <div class="dot" onclick="goTo(1)"></div>
    <div class="dot" onclick="goTo(2)"></div>
    <div class="dot" onclick="goTo(3)"></div>
  </div>

  <div class="login-card">
    <div class="brand">
      <div class="brand-logo">
        <img src="{{ asset('images/logo.jpg') }}" alt="Logo"
             style="width:100%; height:100%; object-fit:contain; border-radius:10px;" />
      </div>
      <h2>Bienvenido</h2>
      <p>Ingresa a tu cuenta para continuar</p>
    </div>

    @if ($errors->any())
      <div class="alert">
        {{ $errors->first() }}
      </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
      @csrf

      <div class="field">
        <label>Correo</label>
        <input type="email" name="email" placeholder="usuario@empresa.com" value="{{ old('email') }}" required autofocus />
      </div>

      <div class="field">
        <label>Contraseña</label>
        <input type="password" name="password" placeholder="••••••••" required />
      </div>

      <div class="forgot">
        @if (Route::has('password.request'))
          <a href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a>
        @endif
      </div>

      <button type="submit" class="btn-login">Iniciar sesión</button>
    </form>
  </div>

</div>

<script>
  const taglines = [
    { title: "Impulsa tu equipo de ventas", sub: "Gestiona, convierte y crece." },
    { title: "Tus leads, bajo control",      sub: "Seguimiento en tiempo real." },
    { title: "Resultados que hablan solos",  sub: "Visibilidad total del embudo." },
    { title: "El poder de tu fuerza comercial", sub: "Todo en un solo lugar." },
  ];

  const imageUrls = [
    "{{ asset('images/fotogari.png') }}",
    "{{ asset('images/paseo.png') }}",
    "{{ asset('images/cumpleaños.png') }}",
    "{{ asset('images/aniversario.jpeg') }}",
  ];

  const slides  = document.querySelectorAll('.slide');
  const dots    = document.querySelectorAll('.dot');
  const titleEl = document.getElementById('tagline-title');
  const subEl   = document.getElementById('tagline-sub');
  const wrapper = document.getElementById('wrapper');

  let current = 0;
  let timer;

  function showTagline(index) {
    titleEl.classList.remove('visible');
    subEl.classList.remove('visible');
    setTimeout(() => {
      titleEl.textContent = taglines[index].title;
      subEl.textContent   = taglines[index].sub;
      titleEl.classList.add('visible');
      subEl.classList.add('visible');
    }, 200);
  }

  function goTo(index) {
    slides[current].classList.remove('active');
    slides[current].style.animation = 'none';
    dots[current].classList.remove('active');
    void slides[index].offsetWidth;
    current = index;
    slides[current].classList.add('active');
    dots[current].classList.add('active');
    showTagline(current);
    clearInterval(timer);
    timer = setInterval(next, 6000);
  }

  function next() {
    goTo((current + 1) % slides.length);
  }

  // Precargar todas las imágenes antes de mostrar nada
  let loaded = 0;
  imageUrls.forEach((url, i) => {
    const img = new Image();
    img.onload = img.onerror = () => {
      loaded++;
      if (loaded === 1) {
        // La primera imagen cargó — mostrar todo de inmediato
        slides[0].classList.add('active');
        wrapper.classList.add('ready');
        showTagline(0);
        timer = setInterval(next, 6000);
      }
    };
    img.src = url;
  });
</script>

</body>
</html>