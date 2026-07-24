    <style>
        :root {
            --blue-dark: #1e1b2e;
            --blue-mid: #2b2650;
            --blue-light: #3d3570;
            --gold: #7c6ef4;
            --gold-light: #9b8afb;
            --bg: #f7f7fb;
            --white: #ffffff;
            --muted: #71717a;
            --text: #1e1b2e;
            --line: rgba(124, 110, 244, 0.16);
            --student: #14b8a6;
            --lecturer: #9b6ef4;
            --admin: #ec4899;
            --success: #22c55e;
            --warning: #f59e0b;
            --shadow: 0 8px 32px rgba(30, 27, 46, 0.08);
            --gold-gradient: linear-gradient(135deg, #7c6ef4, #9b8afb);
            --header-gradient: linear-gradient(145deg, #1e1b2e, #2b2650, #3d3570);
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
            box-shadow: 0 0 30px rgba(124, 110, 244, 0.2);
        }
        .ap-brand h1 { font-size: 1.35rem; line-height: 1; font-weight: 800; }
        .ap-brand h1 span { color: var(--gold); }
        .ap-brand small {
            display: block;
            margin-top: 4px;
            color: #a79fc9;
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
            background: rgba(30, 27, 46, 0.25);
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
                radial-gradient(ellipse at 20% 20%, rgba(124, 110, 244, .12), transparent 34%),
                radial-gradient(ellipse at 80% 70%, rgba(61, 53, 112, .08), transparent 42%),
                var(--bg);
        }
        .form-card { width: min(440px, 100%); padding: 26px; }
        .form-logo { text-align: center; margin-bottom: 18px; }
        .form-logo .ap-logo { margin: 0 auto 10px; }
        .form-logo h1 { font-size: 1.3rem; }
        .form-logo span { color: var(--gold); }
        .field { margin-bottom: 13px; }
        .field label { display: block; margin-bottom: 5px; font-size: .76rem; font-weight: 800; color: #2d2a3d; }
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
            box-shadow: 0 0 0 4px rgba(124, 110, 244, .14);
        }
        .form-row { display: flex; justify-content: space-between; gap: 10px; align-items: center; margin: 10px 0 14px; color: var(--muted); font-size: .78rem; font-weight: 650; }
        .notice { margin-bottom: 14px; padding: 10px 12px; border-radius: 12px; background: rgba(34,197,94,.08); color: #166534; font-weight: 700; font-size: .82rem; }
        .error-list { margin-bottom: 14px; padding: 10px 14px; border-radius: 12px; background: rgba(239,68,68,.08); color: #b91c1c; font-weight: 700; font-size: .82rem; }
        .dashboard { display: flex; min-height: calc(100vh - 74px); }

.sidebar {
    width: 292px;
    position: fixed; /* Fixes it to the screen */
    top: 0;
    left: 0;
    bottom: 0;
    height: 100vh; /* Forces it to fill the exact viewport height */
    z-index: 100; /* Keeps it on top of background elements */

    /* Remove padding so the white container fills the edges completely */
    padding: 0;

    /* Keep your existing styles */
    background: var(--blue-dark);
    color: #a79fc9;
    border-right: 2px solid var(--line);
}

.pulse-sidebar {
    height: 100%; /* Spans the full height of the newly fixed wrapper */
    display: flex;
    flex-direction: column;
    justify-content: space-between; /* Keeps the profile at the very bottom */
    gap: 22px;

    /* Shift your inner padding here to keep items neat inside the white block */
    padding: 26px 18px 20px;

    background: rgba(255, 255, 255, .9);
    border-right: 1px solid rgba(255, 255, 255, .92);
    box-shadow: 12px 0 40px rgba(30, 27, 46, .05);
}

        .side-block { padding-bottom: 18px; margin-bottom: 18px; border-bottom: 1px solid rgba(124,110,244,.16); }
        .side-title { margin-bottom: 10px; color: #a79fc9; font-size: .67rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1.2px; }
        .side-title i { color: var(--gold); margin-right: 6px; }
        .stat-row { display: flex; justify-content: space-between; gap: 12px; padding: 6px 0; font-size: .84rem; font-weight: 650; }
        .stat-row strong { color: var(--white); }
        .dash-content { flex: 1; padding: clamp(18px, 3vw, 32px); overflow: auto; }
        .dash-title { display: flex; justify-content: space-between; gap: 16px; align-items: flex-start; flex-wrap: wrap; margin-bottom: 18px; }
        .dash-title h2 { color: var(--blue-dark); font-size: 1.55rem; }
        .dash-title p { color: var(--muted); font-weight: 650; margin-top: 4px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 18px; margin-bottom: 18px; }
        .panel { padding: 20px; }
        .panel h3 { margin-bottom: 12px; color: #2d2a3d; font-size: .98rem; display: flex; align-items: center; gap: 8px; }
        .panel h3 i { color: var(--gold); }
        .metric-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
        .metric { padding: 14px; border: 1px solid var(--line); border-radius: 12px; background: var(--bg); text-align: center; }
        .metric strong { display: block; color: var(--gold); font-size: 1.45rem; }
        .metric span { color: var(--muted); font-size: .72rem; font-weight: 700; }
        .list { display: grid; gap: 10px; }
        .list-item { display: flex; justify-content: space-between; gap: 12px; align-items: center; padding: 10px 0; border-bottom: 1px solid var(--line); color: #2d2a3d; font-weight: 650; }
        .pill { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 999px; background: var(--bg); color: var(--muted); font-size: .7rem; font-weight: 800; }
        .user-badge { display: inline-flex; align-items: center; gap: 10px; padding: 5px 12px 5px 7px; border: 2px solid var(--line); border-radius: 999px; background: rgba(30,27,46,.28); font-weight: 700; }
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
            --pulse-blue: #7c6ef4;
            --pulse-blue-soft: #ede9fe;
            --pulse-purple: #9b6ef4;
            --pulse-green: #14b8a6;
            --pulse-orange: #f0a868;
            --pulse-cyan: #ec4899;
            --pulse-ink: #1e1b2e;
            --pulse-muted: #71717a;
            --pulse-line: rgba(255, 255, 255, .7);
            --pulse-card: rgba(255, 255, 255, .88);
            --pulse-shadow: 0 24px 70px rgba(30, 27, 46, .1);
            position: relative;
            isolation: isolate;
            min-height: 100vh;
            color: var(--pulse-ink);
            background:
                radial-gradient(circle at 12% 18%, rgba(124,110,244,.18) 0%, transparent 26%),
                radial-gradient(circle at 88% 12%, rgba(20,184,166,.14) 0%, transparent 28%),
                radial-gradient(circle at 28% 82%, rgba(236,72,153,.12) 0%, transparent 30%),
                url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 800 600'%3E%3Cdefs%3E%3CradialGradient id='g1' cx='30%25' cy='30%25'%3E%3Cstop offset='0%25' stop-color='%23ffffff' stop-opacity='0.9'/%3E%3Cstop offset='100%25' stop-color='%237c6ef4' stop-opacity='0'/%3E%3C/radialGradient%3E%3CradialGradient id='g2' cx='65%25' cy='20%25'%3E%3Cstop offset='0%25' stop-color='%239b6ef4' stop-opacity='0.85'/%3E%3Cstop offset='100%25' stop-color='%239b6ef4' stop-opacity='0'/%3E%3C/radialGradient%3E%3CradialGradient id='g3' cx='20%25' cy='80%25'%3E%3Cstop offset='0%25' stop-color='%237c6ef4' stop-opacity='0.85'/%3E%3Cstop offset='100%25' stop-color='%237c6ef4' stop-opacity='0'/%3E%3C/radialGradient%3E%3CradialGradient id='g4' cx='80%25' cy='75%25'%3E%3Cstop offset='0%25' stop-color='%23ffffff' stop-opacity='0.85'/%3E%3Cstop offset='100%25' stop-color='%23ffffff' stop-opacity='0'/%3E%3C/radialGradient%3E%3CradialGradient id='g5' cx='50%25' cy='50%25'%3E%3Cstop offset='0%25' stop-color='%231e1b2e' stop-opacity='0.9'/%3E%3Cstop offset='100%25' stop-color='%231e1b2e' stop-opacity='0'/%3E%3C/radialGradient%3E%3C/defs%3E%3Cellipse cx='140' cy='120' rx='220' ry='160' fill='url(%23g1)'/%3E%3Cellipse cx='620' cy='80' rx='220' ry='160' fill='url(%23g2)'/%3E%3Cellipse cx='180' cy='460' rx='220' ry='160' fill='url(%23g3)'/%3E%3Cellipse cx='640' cy='480' rx='200' ry='140' fill='url(%23g4)'/%3E%3Cellipse cx='400' cy='300' rx='260' ry='200' fill='url(%23g5)'/%3E%3C/svg%3E"),
                url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='180' height='180' viewBox='0 0 180 180'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='2' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='180' height='180' filter='url(%23n)' opacity='0.035'/%3E%3C/svg%3E" );
            background-size: auto, auto, auto, cover, 180px 180px;
            font-family: "Inter", "Segoe UI", -apple-system, BlinkMacSystemFont, system-ui, sans-serif;
            overflow: hidden;
        }

        .pulse-page[data-theme="dark"] {
            --pulse-blue: #9b8afb;
            --pulse-blue-soft: #2b2650;
            --pulse-purple: #b79cf7;
            --pulse-green: #2dd4bf;
            --pulse-orange: #f5c283;
            --pulse-cyan: #f472b6;
            --pulse-ink: #f2f0fa;
            --pulse-muted: #9691ad;
            --pulse-line: rgba(255, 255, 255, .12);
            --pulse-card: rgba(30, 27, 46, .82);
            --pulse-shadow: 0 24px 70px rgba(0, 0, 0, .4);
            background:
                radial-gradient(circle at 12% 18%, rgba(155,138,251,.18) 0%, transparent 26%),
                radial-gradient(circle at 88% 12%, rgba(45,212,191,.16) 0%, transparent 28%),
                radial-gradient(circle at 28% 82%, rgba(244,114,182,.16) 0%, transparent 30%),
                #13111f;
        }

        .pulse-page > * {
            position: relative;
            z-index: 1;
        }

        .pulse-app {
            display: grid;
            grid-template-columns: 220px minmax(0, 1fr);
            min-height: 100vh;
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
            box-shadow: 0 12px 28px rgba(124, 110, 244, .32);
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
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.88);
            border-radius: 24px;
            box-shadow: 0 8px 24px rgba(30,27,46,0.08), inset 0 1px 0 rgba(255,255,255,0.5);
            padding: 20px;
            overflow: hidden;
        }

        .pulse-menu a {
            min-height: 42px;
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 0 12px;
            border-radius: 10px;
            color: #52525b;
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

        .pulse-sidebar-footer {
            margin-top: auto;
            display: grid;
            gap: 12px;
        }

        .pulse-user {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px;
            border: 1px solid var(--pulse-line);
            border-radius: 18px;
            background: rgba(255, 255, 255, .76);
        }

        .pulse-theme-panel {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
            padding: 8px;
            border: 1px solid var(--pulse-line);
            border-radius: 16px;
            background: rgba(255, 255, 255, .72);
        }

        .pulse-theme-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            min-height: 36px;
            border: 0;
            border-radius: 10px;
            color: var(--pulse-muted);
            background: transparent;
            font-size: .75rem;
            font-weight: 800;
            cursor: pointer;
        }

        .pulse-theme-btn.active {
            color: var(--pulse-blue);
            background: var(--pulse-blue-soft);
        }

        .pulse-avatar {
            width: 38px;
            height: 38px;
            display: grid;
            place-items: center;
            flex: 0 0 auto;
            border-radius: 50%;
            color: #fff;
            background: linear-gradient(135deg, var(--pulse-blue), #9b8afb);
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
            border: 1px solid rgba(255, 255, 255, .8);
            border-radius: 14px;
            background: rgba(255, 255, 255, .96);
            color: var(--pulse-muted);
            box-shadow: 0 8px 20px rgba(30, 27, 46, .04);
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
            border: 1px solid rgba(255, 255, 255, .8);
            border-radius: 14px;
            color: var(--pulse-blue);
            background: rgba(255, 255, 255, .96);
            box-shadow: 0 8px 20px rgba(30, 27, 46, .04);
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
            box-shadow: 0 14px 30px rgba(124, 110, 244, .3);
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
            position: relative;
            border: 1px solid rgba(255, 255, 255, .72);
            border-radius: 22px;
            background: var(--pulse-card);
            box-shadow: var(--pulse-shadow);
            overflow: hidden;
        }

        .pulse-card::before {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: inherit;
            pointer-events: none;
            background: radial-gradient(circle at 20% 18%, rgba(155,110,244,.06) 0%, transparent 18%), radial-gradient(circle at 78% 78%, rgba(236,72,153,.04) 0%, transparent 28%);
            mix-blend-mode: overlay;
            z-index: 0;
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

        .pulse-stat-icon.purple { color: var(--pulse-purple); background: #f1eaff; }
        .pulse-stat-icon.green { color: var(--pulse-green); background: #d9f7f0; }
        .pulse-stat-icon.orange { color: var(--pulse-orange); background: #fdecd6; }
        .pulse-stat-icon.cyan { color: var(--pulse-cyan); background: #fce7f0; }

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
            border: 1px solid rgba(255, 255, 255, .66);
            border-radius: 14px;
            background: rgba(255, 255, 255, .7);

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

        .pulse-tag.green { color: #0f9488; background: #dcf7f1; }
        .pulse-tag.orange { color: #c46d00; background: #fff4df; }
        .pulse-tag.gray { color: #6b7280; background: #f1f1f5; }

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
            background: #eeeef4;
        }

        .pulse-progress span {
            display: block;
            height: 100%;
            border-radius: inherit;
            background: var(--pulse-blue);
        }

        .pulse-bars {
            display: grid;
            gap: 12px;
        }

        .pulse-bar-row {
            display: grid;
            grid-template-columns: minmax(0, 108px) 1fr 28px;
            align-items: center;
            gap: 10px;
        }

        .pulse-bar-label {
            overflow: hidden;
            color: var(--pulse-muted);
            font-size: .76rem;
            font-weight: 700;
            white-space: nowrap;
            text-overflow: ellipsis;
        }

        .pulse-bar-track {
            height: 18px;
            overflow: hidden;
            border-radius: 4px;
            background: #eeeef4;
        }

        .pulse-bar-fill {
            display: block;
            height: 100%;
            border-radius: 0 4px 4px 0;
            background: var(--pulse-blue);
        }

        .pulse-bar-fill.has-value {
            min-width: 4px;
        }

        .pulse-bar-value {
            text-align: right;
            color: var(--pulse-ink);
            font-size: .78rem;
            font-weight: 800;
        }

        .pulse-meter-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            color: var(--pulse-muted);
            font-size: .78rem;
            font-weight: 700;
        }

        .pulse-meter-label strong {
            color: var(--pulse-ink);
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
            color: #52525b;
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
            border: 1px solid rgba(255, 255, 255, .66);
            border-radius: 14px;
            background: rgba(255, 255, 255, .72);

        }

        .pulse-landing {
            position: relative;
            overflow: hidden;
            min-height: 100vh;
            padding: 26px;
            color: var(--pulse-ink);
            background:
                radial-gradient(circle at 12% 22%, rgba(155, 110, 244, .16), transparent 16%),
                radial-gradient(circle at 88% 18%, rgba(124, 110, 244, .14), transparent 18%),
                radial-gradient(circle at 84% 46%, rgba(20, 184, 166, .13), transparent 15%),
                linear-gradient(135deg, #f9f9fc 0%, #ffffff 46%, #f7f7fb 100%);
            font-family: "Inter", "Segoe UI", -apple-system, BlinkMacSystemFont, system-ui, sans-serif;
        }

        .pulse-landing-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 22px;
            max-width: 1320px;
            margin: 0 auto;
            padding: 16px 18px;
            border: 1px solid var(--pulse-line);
            border-radius: 18px;
            background: rgba(255, 255, 255, .72);
            box-shadow: var(--pulse-shadow);
        }

        .pulse-nav-links {
            display: flex;
            align-items: center;
            gap: 30px;
            color: #52525b;
            font-size: .82rem;
            font-weight: 800;
        }

        .pulse-hero {
            position: relative;
            max-width: 1320px;
            min-height: 590px;
            margin: 26px auto 0;
            display: grid;
            place-items: center;
            text-align: center;
        }

        .pulse-hero h1 {
            margin: 0;
            color: var(--pulse-ink);
            font-size: clamp(2.1rem, 5.2vw, 4.9rem);
            line-height: 1.05;
            font-weight: 950;
        }

        .pulse-hero h1 span {
            display: block;
            margin-top: 10px;
            color: var(--pulse-blue);
        }

        .pulse-hero p {
            max-width: 560px;
            margin: 22px auto 28px;
            color: #52525b;
            font-size: 1.05rem;
            line-height: 1.7;
            font-weight: 650;
        }

        .pulse-hero-actions {
            display: flex;
            justify-content: center;
            gap: 14px;
            flex-wrap: wrap;
        }

        .pulse-float {
            position: absolute;
            width: 78px;
            height: 78px;
            display: grid;
            place-items: center;
            border-radius: 26px;
            color: var(--pulse-blue);
            background: rgba(255, 255, 255, .72);
            border: 1px solid var(--pulse-line);
            box-shadow: 0 18px 54px rgba(124, 110, 244, .24);
            backdrop-filter: blur(18px);
            font-size: 1.6rem;
        }

        .pulse-float.one { left: 7%; top: 18%; color: var(--pulse-purple); }
        .pulse-float.two { right: 6%; top: 15%; }
        .pulse-float.three { left: 13%; bottom: 24%; color: var(--pulse-purple); }
        .pulse-float.four { right: 12%; bottom: 28%; color: var(--pulse-green); }

        .pulse-feature-grid {
            max-width: 1320px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
        }

        .pulse-feature {
            min-height: 178px;
            display: grid;
            justify-items: center;
            align-content: center;
            gap: 14px;
            padding: 22px;
            text-align: center;
        }

        .pulse-feature h2 {
            margin: 0;
            color: var(--pulse-ink);
            font-size: 1rem;
        }

        .pulse-feature p {
            margin: 0;
            color: var(--pulse-muted);
            font-size: .82rem;
            line-height: 1.6;
            font-weight: 650;
        }

        .pulse-proof {
            max-width: 1320px;
            margin: 48px auto 0;
            text-align: center;
        }

        .pulse-proof > p {
            margin: 0 0 18px;
            color: var(--pulse-ink);
            font-weight: 850;
        }

        .pulse-proof-card {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
            padding: 28px;
        }

        .pulse-proof-card strong {
            display: block;
            margin-top: 8px;
            color: var(--pulse-ink);
            font-size: 1.45rem;
        }

        .pulse-proof-card span {
            color: var(--pulse-muted);
            font-size: .78rem;
            font-weight: 750;
        }

        .pulse-auth {
            min-height: 100vh;
            display: grid;
            grid-template-columns: minmax(320px, .85fr) minmax(360px, 1.15fr);
            gap: 30px;
            align-items: center;
            padding: clamp(22px, 4vw, 48px);
            color: var(--pulse-ink);
            background:
                radial-gradient(circle at 11% 70%, rgba(20, 184, 166, .14), transparent 17%),
                radial-gradient(circle at 32% 58%, rgba(155, 110, 244, .14), transparent 13%),
                linear-gradient(135deg, #f9f9fc 0%, #ffffff 50%, #f7f7fb 100%);
            font-family: "Inter", "Segoe UI", -apple-system, BlinkMacSystemFont, system-ui, sans-serif;
        }

        .pulse-auth-side {
            position: relative;
            min-height: 620px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .pulse-auth-copy {
            max-width: 360px;
            margin-top: 58px;
        }

        .pulse-auth-copy h1 {
            margin: 0;
            color: var(--pulse-ink);
            font-size: clamp(2rem, 4vw, 3rem);
            line-height: 1.08;
            font-weight: 950;
        }

        .pulse-auth-copy p {
            margin: 14px 0 0;
            color: #52525b;
            line-height: 1.65;
            font-weight: 650;
        }

        .pulse-illustration {
            position: relative;
            width: min(360px, 92%);
            aspect-ratio: 1 / 1;
            align-self: center;
            border-radius: 36px;
            background:
                radial-gradient(circle at 50% 18%, #ffd6a5 0 11%, transparent 12%),
                radial-gradient(circle at 50% 28%, #1e1b2e 0 18%, transparent 19%),
                linear-gradient(180deg, transparent 0 40%, #14b8a6 41% 68%, transparent 69%),
                linear-gradient(160deg, transparent 0 58%, #f2a65a 59% 68%, transparent 69%),
                linear-gradient(20deg, transparent 0 58%, #f2a65a 59% 68%, transparent 69%),
                linear-gradient(0deg, transparent 0 71%, #efeafd 72% 78%, transparent 79%);
        }

        .pulse-illustration::before,
        .pulse-illustration::after {
            content: "";
            position: absolute;
            border-radius: 14px;
            background: #fff;
            border: 1px solid var(--pulse-line);
            box-shadow: var(--pulse-shadow);
        }

        .pulse-illustration::before {
            width: 220px;
            height: 112px;
            left: 50%;
            bottom: 44px;
            transform: translateX(-50%);
        }

        .pulse-illustration::after {
            width: 92px;
            height: 18px;
            left: 44px;
            bottom: 22px;
            background: #ffb86b;
        }

        .pulse-auth-card {
            width: min(640px, 100%);
            justify-self: center;
            padding: clamp(26px, 5vw, 48px);
        }

        .pulse-auth-card h2 {
            margin: 0 0 28px;
            text-align: center;
            color: var(--pulse-ink);
            font-size: 1.35rem;
            font-weight: 950;
        }

        .pulse-form {
            display: grid;
            gap: 18px;
        }

        .pulse-field {
            display: grid;
            gap: 8px;
        }

        .pulse-field label {
            color: var(--pulse-ink);
            font-size: .78rem;
            font-weight: 850;
        }

        .pulse-input {
            min-height: 48px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 0 15px;
            border: 1px solid #e2e2ea;
            border-radius: 12px;
            background: rgba(255, 255, 255, .82);
        }

        .pulse-input input,
        .pulse-input select {
            width: 100%;
            min-width: 0;
            border: 0;
            outline: 0;
            background: transparent;
            color: var(--pulse-ink);
            font-weight: 700;
        }

        .pulse-form-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            color: var(--pulse-muted);
            font-size: .78rem;
            font-weight: 750;
        }

        .pulse-auth-divider {
            display: flex;
            align-items: center;
            gap: 14px;
            color: var(--pulse-muted);
            font-size: .8rem;
            font-weight: 750;
            margin: 8px 0;
        }

        .pulse-auth-divider::before,
        .pulse-auth-divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: var(--pulse-line);
        }

        .pulse-provider-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .pulse-alert {
            padding: 12px 14px;
            border-radius: 12px;
            color: #b91c1c;
            background: #fff1f2;
            font-size: .82rem;
            font-weight: 800;
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

            .pulse-landing-nav,
            .pulse-nav-links {
                align-items: flex-start;
                flex-direction: column;
            }

            .pulse-feature-grid,
            .pulse-proof-card,
            .pulse-auth {
                grid-template-columns: 1fr;
            }

            .pulse-auth-side {
                min-height: 460px;
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

            .pulse-landing {
                padding: 14px;
            }

            .pulse-float {
                display: none;
            }

            .pulse-provider-grid,
            .pulse-hero-actions {
                grid-template-columns: 1fr;
                flex-direction: column;
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
