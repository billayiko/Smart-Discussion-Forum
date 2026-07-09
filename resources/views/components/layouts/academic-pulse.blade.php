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

        .pulse-page {
            --pulse-blue: #315cff;
            --pulse-blue-soft: #eef3ff;
            --pulse-purple: #8b5cf6;
            --pulse-green: #16c784;
            --pulse-orange: #ff9f1c;
            --pulse-cyan: #06b6d4;
            --pulse-ink: #0f1f3d;
            --pulse-muted: #7182a8;
            --pulse-line: #e8edf8;
            --pulse-card: rgba(255, 255, 255, .86);
            --pulse-shadow: 0 18px 55px rgba(49, 92, 255, .08);
            min-height: 100vh;
            color: var(--pulse-ink);
            background:
                radial-gradient(circle at 8% 20%, rgba(49, 92, 255, .12), transparent 20%),
                radial-gradient(circle at 86% 8%, rgba(49, 92, 255, .10), transparent 18%),
                radial-gradient(circle at 34% 90%, rgba(139, 92, 246, .12), transparent 20%),
                linear-gradient(135deg, #f8fbff 0%, #ffffff 45%, #f6f9ff 100%);
            font-family: "Inter", "Segoe UI", -apple-system, BlinkMacSystemFont, system-ui, sans-serif;
        }

        .pulse-app {
            display: grid;
            grid-template-columns: 220px minmax(0, 1fr);
            min-height: 100vh;
        }

        .pulse-sidebar {
            position: sticky;
            top: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
            gap: 22px;
            padding: 26px 18px 20px;
            background: rgba(255, 255, 255, .72);
            border-right: 1px solid var(--pulse-line);
            backdrop-filter: blur(22px);
        }

        .pulse-logo {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: var(--pulse-ink);
            font-weight: 900;
        }

        .pulse-logo i {
            width: 36px;
            height: 36px;
            display: grid;
            place-items: center;
            border-radius: 11px;
            color: #fff;
            background: var(--pulse-blue);
            box-shadow: 0 12px 28px rgba(49, 92, 255, .22);
        }

        .pulse-logo span span {
            display: block;
            color: var(--pulse-blue);
            font-size: .76rem;
            line-height: 1.1;
        }

        .pulse-menu {
            display: grid;
            gap: 8px;
        }

        .pulse-menu a {
            min-height: 42px;
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 0 12px;
            border-radius: 10px;
            color: #405175;
            font-size: .84rem;
            font-weight: 750;
            transition: background .2s ease, color .2s ease, transform .2s ease;
        }

        .pulse-menu a:hover,
        .pulse-menu a.active {
            color: var(--pulse-blue);
            background: var(--pulse-blue-soft);
            transform: translateX(2px);
        }

        .pulse-menu i {
            width: 18px;
            color: inherit;
            text-align: center;
        }

        .pulse-user {
            margin-top: auto;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px;
            border: 1px solid var(--pulse-line);
            border-radius: 18px;
            background: rgba(255, 255, 255, .76);
        }

        .pulse-avatar {
            width: 38px;
            height: 38px;
            display: grid;
            place-items: center;
            flex: 0 0 auto;
            border-radius: 50%;
            color: #fff;
            background: linear-gradient(135deg, var(--pulse-blue), #77a1ff);
            font-size: .78rem;
            font-weight: 900;
        }

        .pulse-user strong,
        .pulse-list strong,
        .pulse-table strong {
            display: block;
            color: var(--pulse-ink);
        }

        .pulse-user span,
        .pulse-muted {
            color: var(--pulse-muted);
            font-size: .74rem;
            font-weight: 700;
        }

        .pulse-main {
            min-width: 0;
            padding: 28px;
        }

        .pulse-topbar {
            display: flex;
            justify-content: space-between;
            gap: 18px;
            align-items: center;
            margin-bottom: 24px;
        }

        .pulse-title h1 {
            margin: 0;
            font-size: 1.55rem;
            line-height: 1.1;
            font-weight: 900;
        }

        .pulse-title p {
            margin: 6px 0 0;
            color: var(--pulse-muted);
            font-size: .86rem;
            font-weight: 650;
        }

        .pulse-tools {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .pulse-search,
        .pulse-select {
            height: 42px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 0 14px;
            border: 1px solid var(--pulse-line);
            border-radius: 14px;
            background: rgba(255, 255, 255, .84);
            color: var(--pulse-muted);
            box-shadow: 0 10px 32px rgba(15, 31, 61, .04);
        }

        .pulse-search input,
        .pulse-select select {
            width: 220px;
            border: 0;
            outline: 0;
            color: var(--pulse-ink);
            background: transparent;
            font-weight: 700;
        }

        .pulse-icon-btn {
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            border: 1px solid var(--pulse-line);
            border-radius: 14px;
            color: var(--pulse-blue);
            background: rgba(255, 255, 255, .84);
            box-shadow: 0 10px 32px rgba(15, 31, 61, .04);
        }

        .pulse-btn {
            min-height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 0 18px;
            border: 0;
            border-radius: 12px;
            color: #fff;
            background: var(--pulse-blue);
            box-shadow: 0 14px 30px rgba(49, 92, 255, .18);
            font-weight: 850;
            cursor: pointer;
        }

        .pulse-btn.light {
            color: var(--pulse-blue);
            background: var(--pulse-blue-soft);
            box-shadow: none;
        }

        .pulse-grid {
            display: grid;
            gap: 18px;
        }

        .pulse-stats {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .pulse-two {
            grid-template-columns: minmax(0, 1.05fr) minmax(340px, .95fr);
        }

        .pulse-three {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .pulse-card {
            border: 1px solid var(--pulse-line);
            border-radius: 18px;
            background: var(--pulse-card);
            box-shadow: var(--pulse-shadow);
        }

        .pulse-pad {
            padding: 18px;
        }

        .pulse-stat {
            display: flex;
            gap: 14px;
            align-items: center;
            min-height: 112px;
            padding: 18px;
        }

        .pulse-stat-icon,
        .pulse-soft-icon {
            width: 48px;
            height: 48px;
            display: grid;
            place-items: center;
            border-radius: 14px;
            color: var(--pulse-blue);
            background: var(--pulse-blue-soft);
            font-size: 1rem;
        }

        .pulse-stat-icon.purple { color: var(--pulse-purple); background: #f4edff; }
        .pulse-stat-icon.green { color: var(--pulse-green); background: #eafbf4; }
        .pulse-stat-icon.orange { color: var(--pulse-orange); background: #fff5e6; }
        .pulse-stat-icon.cyan { color: var(--pulse-cyan); background: #e8fbff; }

        .pulse-stat small {
            display: block;
            color: var(--pulse-muted);
            font-size: .72rem;
            font-weight: 800;
        }

        .pulse-stat b {
            display: block;
            margin: 3px 0;
            color: var(--pulse-ink);
            font-size: 1.65rem;
            line-height: 1;
        }

        .pulse-trend {
            display: inline-flex;
            gap: 5px;
            align-items: center;
            color: var(--pulse-green);
            font-size: .7rem;
            font-weight: 850;
        }

        .pulse-section-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 14px;
        }

        .pulse-section-head h2 {
            margin: 0;
            color: var(--pulse-ink);
            font-size: 1rem;
            font-weight: 900;
        }

        .pulse-section-head a {
            color: var(--pulse-blue);
            font-size: .75rem;
            font-weight: 850;
        }

        .pulse-list {
            display: grid;
            gap: 12px;
        }

        .pulse-row {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr) auto;
            gap: 12px;
            align-items: center;
            min-height: 62px;
            padding: 12px;
            border: 1px solid var(--pulse-line);
            border-radius: 14px;
            background: rgba(255, 255, 255, .74);
        }

        .pulse-time {
            min-width: 54px;
            padding: 8px;
            border-radius: 12px;
            text-align: center;
            color: var(--pulse-blue);
            background: var(--pulse-blue-soft);
            font-size: .7rem;
            font-weight: 900;
        }

        .pulse-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--pulse-blue);
        }

        .pulse-row p {
            margin: 3px 0 0;
            color: var(--pulse-muted);
            font-size: .75rem;
            font-weight: 650;
        }

        .pulse-tag {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 24px;
            padding: 0 9px;
            border-radius: 999px;
            color: var(--pulse-blue);
            background: var(--pulse-blue-soft);
            font-size: .7rem;
            font-weight: 850;
            white-space: nowrap;
        }

        .pulse-tag.green { color: #0d9f6e; background: #e8fbf2; }
        .pulse-tag.orange { color: #c46d00; background: #fff4df; }
        .pulse-tag.gray { color: #64748b; background: #f1f5f9; }

        .pulse-chart {
            height: 238px;
            padding: 16px;
        }

        .pulse-chart svg {
            width: 100%;
            height: 100%;
        }

        .pulse-mini-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .pulse-mini {
            display: grid;
            gap: 8px;
            padding: 14px;
            border: 1px solid var(--pulse-line);
            border-radius: 14px;
            background: rgba(255, 255, 255, .74);
        }

        .pulse-progress {
            height: 7px;
            overflow: hidden;
            border-radius: 999px;
            background: #edf2fb;
        }

        .pulse-progress span {
            display: block;
            height: 100%;
            border-radius: inherit;
            background: var(--pulse-blue);
        }

        .pulse-table {
            width: 100%;
            border-collapse: collapse;
            font-size: .82rem;
        }

        .pulse-table th {
            color: var(--pulse-muted);
            font-size: .72rem;
            text-align: left;
            padding: 14px 12px;
            border-bottom: 1px solid var(--pulse-line);
        }

        .pulse-table td {
            padding: 15px 12px;
            color: #405175;
            font-weight: 700;
            border-bottom: 1px solid var(--pulse-line);
        }

        .pulse-table tr:last-child td {
            border-bottom: 0;
        }

        .pulse-actions {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
            align-items: center;
        }

        .pulse-resource {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px;
            border: 1px solid var(--pulse-line);
            border-radius: 14px;
            background: rgba(255, 255, 255, .76);
        }

        @media (max-width: 1180px) {
            .pulse-stats,
            .pulse-three {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .pulse-two {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 840px) {
            .pulse-app {
                grid-template-columns: 1fr;
            }

            .pulse-sidebar {
                position: static;
                height: auto;
            }

            .pulse-menu {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .pulse-topbar,
            .pulse-tools {
                align-items: stretch;
                flex-direction: column;
            }

            .pulse-search input,
            .pulse-select select {
                width: 100%;
            }
        }

        @media (max-width: 560px) {
            .pulse-main {
                padding: 18px;
            }

            .pulse-stats,
            .pulse-three,
            .pulse-mini-grid,
            .pulse-menu {
                grid-template-columns: 1fr;
            }

            .pulse-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    {{ $slot }}
</body>
</html>
