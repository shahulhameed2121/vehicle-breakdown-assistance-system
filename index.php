<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Vehicle Breakdown Assistance System</title>

  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: "Segoe UI", sans-serif; }

    /* ---------- Theme Variables ---------- */
    :root{
      --bg1:#0f172a; --bg2:#1e293b;
      --card: rgba(255,255,255,0.10);
      --card-border: rgba(255,255,255,0.18);
      --text: #ffffff;
      --muted: rgba(255,255,255,0.85);
      --shadow: 0 18px 40px rgba(0,0,0,0.35);

      --btn1a:#00c6ff; --btn1b:#0072ff;
      --btn2a:#ff512f; --btn2b:#dd2476;
      --btn3a:#1d976c; --btn3b:#93f9b9;

      --road: rgba(255,255,255,0.16);
      --dash: rgba(255,255,255,0.55);
    }

    /* Light mode overrides */
    body.light{
      --bg1:#e8f0ff; --bg2:#f7fbff;
      --card: rgba(0,0,0,0.06);
      --card-border: rgba(0,0,0,0.12);
      --text: #0f172a;
      --muted: rgba(15,23,42,0.8);
      --shadow: 0 18px 40px rgba(2,6,23,0.12);

      --road: rgba(15,23,42,0.12);
      --dash: rgba(15,23,42,0.35);
    }

    /* ---------- Background ---------- */
    body{
      min-height: 100vh;
      display:flex;
      justify-content:center;
      align-items:center;
      padding: 20px;
      background: linear-gradient(-45deg, var(--bg1), var(--bg2), var(--bg1), var(--bg2));
      background-size: 400% 400%;
      animation: gradientBG 10s ease infinite;
      color: var(--text);
      overflow: hidden;
    }

    @keyframes gradientBG{
      0%{background-position:0% 50%;}
      50%{background-position:100% 50%;}
      100%{background-position:0% 50%;}
    }

    /* ---------- Top Bar (toggle) ---------- */
    .topbar{
      position: fixed;
      top: 16px;
      right: 16px;
      display:flex;
      gap: 10px;
      align-items:center;
      z-index: 10;
    }

    .toggle{
      display:flex;
      align-items:center;
      gap: 10px;
      padding: 10px 12px;
      border-radius: 999px;
      border: 1px solid var(--card-border);
      background: var(--card);
      backdrop-filter: blur(14px);
      box-shadow: var(--shadow);
      user-select:none;
    }

    .toggle span{
      font-size: 14px;
      color: var(--muted);
      font-weight: 600;
    }

    .switch{
      width: 52px;
      height: 28px;
      border-radius: 999px;
      border: 1px solid var(--card-border);
      background: rgba(255,255,255,0.12);
      position: relative;
      cursor: pointer;
      transition: 0.25s ease;
      flex-shrink: 0;
    }

    body.light .switch{
      background: rgba(15,23,42,0.10);
    }

    .knob{
      position:absolute;
      top: 50%;
      transform: translateY(-50%);
      left: 4px;
      width: 22px;
      height: 22px;
      border-radius: 50%;
      background: rgba(255,255,255,0.95);
      transition: 0.25s ease;
      box-shadow: 0 8px 18px rgba(0,0,0,0.25);
    }

    body.light .knob{
      background: rgba(15,23,42,0.90);
      box-shadow: 0 8px 18px rgba(2,6,23,0.18);
    }

    body.light .knob{ left: 26px; }
    body:not(.light) .knob{ left: 4px; }

    /* ---------- Card ---------- */
    .container{
      width: min(440px, 92vw);
      text-align:center;
      padding: 42px 34px 30px;
      border-radius: 22px;
      background: var(--card);
      border: 1px solid var(--card-border);
      backdrop-filter: blur(16px);
      box-shadow: var(--shadow);
      animation: fadeIn 1.2s ease;
      position: relative;
      z-index: 2;
    }

    @keyframes fadeIn{
      from{ opacity:0; transform: translateY(26px); }
      to{ opacity:1; transform: translateY(0); }
    }

    h1{
      font-size: 22px;
      margin-bottom: 12px;
      letter-spacing: 0.2px;
    }

    p{
      font-size: 14.5px;
      color: var(--muted);
      margin-bottom: 20px;
      line-height: 1.45;
    }

    /* ---------- Buttons ---------- */
    .btn{
      display:block;
      text-decoration:none;
      padding: 12px 14px;
      margin: 10px 0;
      border-radius: 999px;
      font-weight: 700;
      color: white;
      transition: 0.25s ease;
      position: relative;
      overflow: hidden;
      border: 1px solid rgba(255,255,255,0.12);
    }

    /* subtle shine */
    .btn::after{
      content:"";
      position:absolute;
      top:0; left:-60%;
      width: 40%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.35), transparent);
      transform: skewX(-18deg);
      transition: 0.45s ease;
    }

    .btn:hover::after{ left: 120%; }
    .btn:hover{ transform: translateY(-2px) scale(1.02); box-shadow: 0 12px 25px rgba(0,0,0,0.25); }

    .user-btn{ background: linear-gradient(45deg, var(--btn1a), var(--btn1b)); }
    .admin-btn{ background: linear-gradient(45deg, var(--btn2a), var(--btn2b)); }
    .driver-btn{ background: linear-gradient(45deg, var(--btn3a), var(--btn3b)); }

    /* ---------- Road + Car Animation ---------- */
    .road-wrap{
      width: min(680px, 96vw);
      height: 92px;
      position: fixed;
      bottom: 22px;
      left: 50%;
      transform: translateX(-50%);
      z-index: 1;
      pointer-events: none;
    }

    .road{
      position:absolute;
      inset: 0;
      border-radius: 999px;
      background: var(--road);
      border: 1px solid var(--card-border);
      backdrop-filter: blur(10px);
      overflow: hidden;
    }

    /* moving dashed line */
    .dashline{
      position:absolute;
      top: 50%;
      left: -20%;
      width: 140%;
      height: 4px;
      transform: translateY(-50%);
      background: repeating-linear-gradient(
        90deg,
        var(--dash) 0 26px,
        transparent 26px 46px
      );
      opacity: 0.9;
      animation: dashMove 1.1s linear infinite;
    }

    @keyframes dashMove{
      from{ transform: translate(-0%, -50%); }
      to{ transform: translate(-12%, -50%); }
    }

    /* car */
    .car{
      position:absolute;
      bottom: 34px;
      left: -110px;
      width: 110px;
      height: 44px;
      animation: carMove 4.2s linear infinite;
      filter: drop-shadow(0 12px 18px rgba(0,0,0,0.35));
    }

    @keyframes carMove{
      0%{ left: -140px; transform: translateY(0); }
      40%{ transform: translateY(-2px); }
      50%{ transform: translateY(0); }
      100%{ left: calc(100% + 140px); transform: translateY(0); }
    }

    /* car body */
    .car-body{
      position:absolute;
      left: 10px;
      top: 10px;
      width: 90px;
      height: 24px;
      border-radius: 10px;
      background: rgba(255,255,255,0.92);
    }
    body.light .car-body{ background: rgba(15,23,42,0.90); }

    .car-top{
      position:absolute;
      left: 28px;
      top: 0px;
      width: 44px;
      height: 18px;
      border-radius: 10px 10px 6px 6px;
      background: rgba(255,255,255,0.78);
    }
    body.light .car-top{ background: rgba(15,23,42,0.72); }

    .window{
      position:absolute;
      top: 3px;
      left: 6px;
      width: 14px;
      height: 10px;
      border-radius: 3px;
      background: rgba(0,0,0,0.18);
    }
    .window.w2{ left: 24px; }

    .wheel{
      position:absolute;
      bottom: 2px;
      width: 18px;
      height: 18px;
      border-radius: 50%;
      background: rgba(0,0,0,0.60);
      animation: wheelSpin 0.4s linear infinite;
    }
    .wheel.w1{ left: 20px; }
    .wheel.w2{ left: 72px; }

    @keyframes wheelSpin{
      from{ transform: rotate(0deg); }
      to{ transform: rotate(360deg); }
    }

    .wheel::after{
      content:"";
      position:absolute;
      inset: 4px;
      border-radius: 50%;
      background: rgba(255,255,255,0.30);
    }

    /* little glow headlight */
    .headlight{
      position:absolute;
      right: 6px;
      top: 16px;
      width: 10px;
      height: 6px;
      border-radius: 2px;
      background: rgba(255,255,255,0.85);
      box-shadow: 12px 0 18px rgba(255,255,255,0.35);
      opacity: 0.9;
    }

    /* ---------- Small footer text ---------- */
    .hint{
      margin-top: 14px;
      font-size: 12px;
      color: var(--muted);
      opacity: 0.9;
    }
  </style>
</head>

<body>
  <!-- Top-right theme toggle -->
  <div class="topbar">
    <div class="toggle" id="themeToggle" title="Toggle Dark/Light mode">
      <span id="modeLabel">Dark</span>
      <div class="switch" role="switch" aria-checked="false">
        <div class="knob"></div>
      </div>
    </div>
  </div>

  <div class="container">
    <h1>Vehicle Breakdown Assistance Management System</h1>
    <p>Welcome to our 24/7 roadside assistance service. Get help fast with a few clicks.</p>

    <a href="user/index.php" class="btn user-btn">🚗 Request Vehicle Assistance</a>
    <a href="admin/login.php" class="btn admin-btn">🔐 Admin Login</a>
    <a href="driver/login.php" class="btn driver-btn">👨‍🔧 Driver Login</a>
    

    <div class="hint">Tip: Use the toggle (top-right) to switch themes.</div>
  </div>

  <!-- Road + Car animation -->
  <div class="road-wrap" aria-hidden="true">
    <div class="road">
      <div class="dashline"></div>

      <div class="car">
        <div class="car-top">
          <div class="window"></div>
          <div class="window w2"></div>
        </div>
        <div class="car-body"></div>
        <div class="headlight"></div>
        <div class="wheel w1"></div>
        <div class="wheel w2"></div>
      </div>
    </div>
  </div>

  <script>
    // ----- Theme toggle with memory -----
    const body = document.body;
    const toggle = document.getElementById('themeToggle');
    const label = document.getElementById('modeLabel');

    function applyTheme(theme){
      if(theme === 'light'){
        body.classList.add('light');
        label.textContent = 'Light';
      } else {
        body.classList.remove('light');
        label.textContent = 'Dark';
      }
      localStorage.setItem('vbams_theme', theme);
    }

    // Load saved theme
    const saved = localStorage.getItem('vbams_theme') || 'dark';
    applyTheme(saved);

    // Toggle on click
    toggle.addEventListener('click', () => {
      const isLight = body.classList.contains('light');
      applyTheme(isLight ? 'dark' : 'light');
    });
  </script>
</body>
</html>
