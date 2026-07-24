<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Academic Pulse Forum - {{ $title ?? 'Auth' }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --color-blue-dark: #1e1b2e; --color-blue-mid: #2b2650; --color-blue-light: #3d3570;
            --color-white: #ffffff; --color-body-bg: #f7f7fb;
            --color-gold-primary: #7c6ef4; --color-gold-light: #9b8afb; --color-gold-glow: rgba(124, 110, 244, 0.2);
            --color-text-dark: #1e1b2e; --color-text-muted: #71717a; --color-text-light: #a79fc9;
            --color-border-subtle: rgba(124, 110, 244, 0.16);
            --shadow-glass: 0 8px 32px rgba(0, 0, 0, 0.04); --shadow-gold: 0 0 30px rgba(124, 110, 244, 0.14);
            --gradient-gold: linear-gradient(135deg, #7c6ef4, #9b8afb); --gradient-gold-hover: linear-gradient(135deg, #6a5ce0, #8a79f0);
            --radius-md: 14px; --radius-xl: 28px; --glass-backdrop: blur(20px);
        }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: var(--color-body-bg); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; margin:0; }
        .login-container { background: var(--color-white); backdrop-filter: var(--glass-backdrop); border: 2px solid var(--color-border-subtle); border-radius: var(--radius-xl); padding: 24px 28px; width: 100%; max-width: 420px; box-shadow: var(--shadow-glass); }
        .form-group { margin-bottom: 12px; }
        .form-group label { display: block; font-size: 0.75rem; font-weight: 700; margin-bottom: 4px; color: var(--color-blue-dark); }
        .input-wrapper { position: relative; }
        .input-wrapper i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--color-text-muted); font-size: 14px; }
        .input-wrapper input, .input-wrapper select { width: 100%; padding: 8px 12px 8px 36px; background: var(--color-body-bg); border: 2px solid var(--color-border-subtle); border-radius: var(--radius-md); font-size: 0.85rem; outline: none; transition: 0.3s; }
        .input-wrapper input:focus { border-color: var(--color-gold-primary); background: var(--color-white); }
        .btn-gold { width: 100%; padding: 10px; background: var(--gradient-gold); color: var(--color-blue-dark); border: none; border-radius: var(--radius-md); font-size: 0.85rem; font-weight: 700; cursor: pointer; transition: 0.3s; }
        .btn-gold:hover { background: var(--gradient-gold-hover); transform: translateY(-1px); }
    </style>
</head>
<body>
    <div class="login-container">
        {{ $slot }}
    </div>
</body>
</html>