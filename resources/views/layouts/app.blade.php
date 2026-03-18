<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Private Chat')</title>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-primary: #0a0a0f;
            --bg-secondary: #12121a;
            --bg-tertiary: #1a1a28;
            --bg-hover: #222236;
            --border: #2a2a40;
            --border-light: #3a3a55;
            --text-primary: #e8e8f0;
            --text-secondary: #9898b0;
            --text-muted: #6868880;
            --accent: #6c5ce7;
            --accent-hover: #7d6ff0;
            --accent-light: rgba(108, 92, 231, 0.15);
            --green: #00d2a0;
            --green-light: rgba(0, 210, 160, 0.15);
            --red: #ff6b6b;
            --red-light: rgba(255, 107, 107, 0.15);
            --orange: #ffa640;
            --blue: #4dc9f6;
            --radius: 12px;
            --radius-sm: 8px;
            --shadow: 0 4px 24px rgba(0,0,0,0.4);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DM Sans', -apple-system, sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            overflow: hidden;
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--border-light); }

        @yield('styles')
    </style>
</head>
<body>
    @yield('content')
    <script>
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;
        const WS_HOST = window.location.hostname;
        const IS_SECURE = window.location.protocol === 'https:';
        const WS_PROTOCOL = IS_SECURE ? 'wss://' : 'ws://';
    </script>
    @yield('scripts')
</body>
</html>