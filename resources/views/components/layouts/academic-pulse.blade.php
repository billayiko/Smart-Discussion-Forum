<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ filled($title ?? null) ? $title.' - Academic Pulse Forum' : 'Academic Pulse Forum' }}</title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --blue-dark: #0a1628;
            --blue-mid: #0f2b4b;
            --blue-light: #1a3a5e;
            --gold: #c9a84c;
            --gold-light: #f0d060;
            --bg: #eef2f7;
            --white: #ffffff;
            --muted: #4a6a8a;
            --text: #0a1628;
            --line: rgba(201, 168, 76, 0.12);
            --student: #3b82f6;
            --lecturer: #8b5cf6;
            --admin: #ef4444;
            --success: #22c55e;
            --warning: #f59e0b;
            --shadow: 0 8px 32px rgba(0, 0, 0, 0.06);
            --gold-gradient: linear-gradient(135deg, #c9a84c, #f0d060);
            --header-gradient: linear-gradient(145deg, #0a1628, #0f2b4b, #1a3a5e);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            background: var(--bg);
            color: var(--text);
            font-family: "Segoe UI", -apple-system, BlinkMacSystemFont, Roboto, system-ui, sans-serif;
        }
        a { color: inherit; text-decoration: none; }
        button, input, select, textarea { font: inherit; }
        .ap-shell { min-height: 100vh; }
        .ap-header {
            position: sticky;
            top: 0;
            z-index: 20;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 14px clamp(16px, 4vw, 48px);
            background: var(--header-gradient);
            border-bottom: 2px solid var(--gold);
            color: var(--white);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        .ap-brand { display: inline-flex; align-items: center; gap: 12px; min-width: 0; }
        .ap-logo {
            width: 44px;
            height: 44px;
            display: grid;
            place-items: center;
            flex: 0 0 auto;
            border-radius: 14px;
            background: var(--gold-gradient);
            color: var(--blue-dark);
            font-size: 20px;
            box-shadow: 0 0 30px rgba(201, 168, 76, 0.12);
        }
        .ap-brand h1 { font-size: 1.35rem; line-height: 1; font-weight: 800; }
        .ap-brand h1 span { color: var(--gold); }
        .ap-brand small {
            display: block;
            margin-top: 4px;
            color: #8ea5c1;
            font-size: 0.62rem;
            font-weight: 700;
            letter-spacing: 1.4px;
            text-transform: uppercase;
        }
        .ap-nav { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; }
        .ap-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 36px;
            padding: 8px 16px;
            border: 2px solid var(--line);
            border-radius: 999px;
            background: rgba(10, 22, 40, 0.25);
            color: var(--white);
            font-size: 0.84rem;
            font-weight: 700;
            cursor: pointer;
            transition: transform .2s ease, border-color .2s ease, background .2s ease;
        }
        .ap-btn:hover { transform: translateY(-1px); border-color: var(--gold); }
        .ap-btn.primary { background: var(--gold-gradient); color: var(--blue-dark); border-color: var(--gold); }
        .ap-btn.light { background: var(--white); color: var(--text); }
        .ap-btn.danger { color: #fca5a5; border-color: rgba(239, 68, 68, .22); }
        .ap-main { padding: clamp(18px, 4vw, 40px); }
        .ap-card {
            background: var(--white);
            border: 2px solid var(--line);
            border-radius: 18px;
            box-shadow: var(--shadow);
        }
        .ap-footer {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 14px clamp(16px, 4vw, 48px);
            background: var(--white);
            border-top: 2px solid var(--line);
            color: var(--muted);
            font-size: .78rem;
            font-weight: 600;
        }
        .form-shell {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            background:
                radial-gradient(ellipse at 20% 20%, rgba(201, 168, 76, .11), transparent 34%),
                radial-gradient(ellipse at 80% 70%, rgba(26, 58, 94, .08), transparent 42%),
                var(--bg);
        }
        .form-card { width: min(440px, 100%); padding: 26px; }
        .form-logo { text-align: center; margin-bottom: 18px; }
        .form-logo .ap-logo { margin: 0 auto 10px; }
        .form-logo h1 { font-size: 1.3rem; }
        .form-logo span { color: var(--gold); }
        .field { margin-bottom: 13px; }
        .field label { display: block; margin-bottom: 5px; font-size: .76rem; font-weight: 800; color: #1a2a4a; }
        .input-wrap { position: relative; }
        .input-wrap i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--muted); }
        .field input, .field select, .field textarea {
            width: 100%;
            min-height: 42px;
            padding: 10px 12px 10px 38px;
            border: 2px solid var(--line);
            border-radius: 12px;
            background: var(--bg);
            color: var(--text);
            font-weight: 650;
            outline: none;
        }
        .field textarea { padding-left: 12px; min-height: 92px; resize: vertical; }
        .field input:focus, .field select:focus, .field textarea:focus {
            border-color: var(--gold);
            background: var(--white);
            box-shadow: 0 0 0 4px rgba(201, 168, 76, .12);
        }
        .form-row { display: flex; justify-content: space-between; gap: 10px; align-items: center; margin: 10px 0 14px; color: var(--muted); font-size: .78rem; font-weight: 650; }
        .notice { margin-bottom: 14px; padding: 10px 12px; border-radius: 12px; background: rgba(34,197,94,.08); color: #166534; font-weight: 700; font-size: .82rem; }
        .error-list { margin-bottom: 14px; padding: 10px 14px; border-radius: 12px; background: rgba(239,68,68,.08); color: #b91c1c; font-weight: 700; font-size: .82rem; }
        .dashboard { display: flex; min-height: calc(100vh - 74px); }
        .sidebar {
            width: 292px;
            flex: 0 0 auto;
            padding: 18px 16px;
            background: var(--blue-dark);
            color: #8ea5c1;
            border-right: 2px solid var(--line);
        }
        .side-block { padding-bottom: 18px; margin-bottom: 18px; border-bottom: 1px solid rgba(201,168,76,.12); }
        .side-title { margin-bottom: 10px; color: #8ea5c1; font-size: .67rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1.2px; }
        .side-title i { color: var(--gold); margin-right: 6px; }
        .stat-row { display: flex; justify-content: space-between; gap: 12px; padding: 6px 0; font-size: .84rem; font-weight: 650; }
        .stat-row strong { color: var(--white); }
        .dash-content { flex: 1; padding: clamp(18px, 3vw, 32px); overflow: auto; }
        .dash-title { display: flex; justify-content: space-between; gap: 16px; align-items: flex-start; flex-wrap: wrap; margin-bottom: 18px; }
        .dash-title h2 { color: var(--blue-dark); font-size: 1.55rem; }
        .dash-title p { color: var(--muted); font-weight: 650; margin-top: 4px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 18px; margin-bottom: 18px; }
        .panel { padding: 20px; }
        .panel h3 { margin-bottom: 12px; color: #1a2a4a; font-size: .98rem; display: flex; align-items: center; gap: 8px; }
        .panel h3 i { color: var(--gold); }
        .metric-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
        .metric { padding: 14px; border: 1px solid var(--line); border-radius: 12px; background: var(--bg); text-align: center; }
        .metric strong { display: block; color: var(--gold); font-size: 1.45rem; }
        .metric span { color: var(--muted); font-size: .72rem; font-weight: 700; }
        .list { display: grid; gap: 10px; }
        .list-item { display: flex; justify-content: space-between; gap: 12px; align-items: center; padding: 10px 0; border-bottom: 1px solid var(--line); color: #1a2a4a; font-weight: 650; }
        .pill { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 999px; background: var(--bg); color: var(--muted); font-size: .7rem; font-weight: 800; }
        .user-badge { display: inline-flex; align-items: center; gap: 10px; padding: 5px 12px 5px 7px; border: 2px solid var(--line); border-radius: 999px; background: rgba(10,22,40,.28); font-weight: 700; }
        .avatar { width: 32px; height: 32px; display: grid; place-items: center; border-radius: 50%; background: var(--gold-gradient); color: var(--blue-dark); font-size: .78rem; font-weight: 900; }
        @media (max-width: 820px) {
            .ap-header { align-items: flex-start; flex-direction: column; }
            .dashboard { flex-direction: column; }
            .sidebar { width: 100%; display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 14px; }
            .side-block { border-bottom: 0; margin-bottom: 0; padding-bottom: 0; }
            .metric-grid { grid-template-columns: 1fr; }
            .ap-footer { flex-direction: column; }
        }
    </style>
</head>
<body>
    {{ $slot }}
</body>
</html>
