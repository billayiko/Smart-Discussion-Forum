<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Academic Pulse Forum - {{ $title ?? 'Auth' }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --color-blue-dark: #0a1628; --color-blue-mid: #0f2b4b; --color-blue-light: #1a3a5e;
            --color-white: #ffffff; --color-body-bg: #eef2f7;
            --color-gold-primary: #c9a84c; --color-gold-light: #f0d060; --color-gold-glow: rgba(201, 168, 76, 0.15);
            --color-text-dark: #0a1628; --color-text-muted: #4a6a8a; --color-text-light: #6a8aaa;
            --color-border-subtle: rgba(201, 168, 76, 0.12);
            --shadow-glass: 0 8px 32px rgba(0, 0, 0, 0.04); --shadow-gold: 0 0 30px rgba(201, 168, 76, 0.06);
            --gradient-gold: linear-gradient(135deg, #c9a84c, #f0d060); --gradient-gold-hover: linear-gradient(135deg, #d4b85a, #f0d880);
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