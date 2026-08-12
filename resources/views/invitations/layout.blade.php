<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invite</title>
    <style>
        :root {
            --accent: #d97706;
            --accent-hover: #b45309;
            --bg: #f8fafc;
            --card: #ffffff;
            --border: #e2e8f0;
            --text: #0f172a;
            --text-muted: #64748b;
            --danger: #dc2626;
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --bg: #0f172a;
                --card: #1e293b;
                --border: #334155;
                --text: #f1f5f9;
                --text-muted: #94a3b8;
            }
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: var(--bg);
            color: var(--text);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }

        .card {
            width: 100%;
            max-width: 420px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.04);
        }

        h1 {
            font-size: 20px;
            font-weight: 600;
            margin: 0 0 20px;
        }

        p {
            color: var(--text-muted);
            font-size: 14px;
            line-height: 1.5;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 6px;
        }

        .field {
            margin-bottom: 16px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 14px;
            background: var(--bg);
            color: var(--text);
        }

        input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(217, 119, 6, 0.15);
        }

        input:disabled {
            opacity: 0.6;
        }

        button, a.button-link {
            display: block;
            width: 100%;
            padding: 12px;
            background: var(--accent);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            box-sizing: border-box;
        }

        button:hover, a.button-link:hover {
            background: var(--accent-hover);
        }

        .errors {
            background: rgba(220, 38, 38, 0.08);
            border: 1px solid rgba(220, 38, 38, 0.3);
            color: var(--danger);
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 13px;
            margin-bottom: 16px;
        }

        .errors ul {
            margin: 0;
            padding-left: 18px;
        }
    </style>
</head>
<body>
    <div class="card">
        @yield('content')
    </div>
</body>
</html>
