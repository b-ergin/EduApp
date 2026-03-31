<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Admin Panel' }}</title>
    <style>
        :root {
            --bg: #f3f4f6;
            --surface: #ffffff;
            --text: #111827;
            --muted: #6b7280;
            --border: #e5e7eb;
            --primary: #0f766e;
            --danger: #dc2626;
            --success-bg: #ecfdf5;
            --success-text: #166534;
            --error-bg: #fef2f2;
            --error-text: #991b1b;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: "Poppins", "Segoe UI", sans-serif;
            color: var(--text);
            background: var(--bg);
        }

        .shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 220px 1fr;
        }

        .sidebar {
            background: #0b1324;
            color: #d1d5db;
            padding: 24px 16px;
        }

        .brand {
            font-weight: 700;
            color: white;
            margin-bottom: 20px;
            font-size: 1.1rem;
        }

        .nav a {
            display: block;
            color: #cbd5e1;
            text-decoration: none;
            padding: 8px 10px;
            border-radius: 8px;
            margin-bottom: 6px;
            font-size: 0.92rem;
        }

        .nav a:hover {
            background: #1e293b;
            color: #fff;
        }

        .content {
            padding: 20px;
        }

        .topbar {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px;
        }

        .flash {
            padding: 10px 12px;
            border-radius: 8px;
            margin-bottom: 12px;
            font-size: 0.92rem;
        }

        .flash-success {
            background: var(--success-bg);
            color: var(--success-text);
            border: 1px solid #a7f3d0;
        }

        .flash-error {
            background: var(--error-bg);
            color: var(--error-text);
            border: 1px solid #fecaca;
        }

        .btn {
            display: inline-block;
            border: 0;
            border-radius: 8px;
            padding: 9px 12px;
            text-decoration: none;
            cursor: pointer;
            font-size: 0.9rem;
            background: var(--primary);
            color: white;
        }

        .btn-danger {
            background: var(--danger);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border-bottom: 1px solid var(--border);
            padding: 10px;
            text-align: left;
            font-size: 0.9rem;
            vertical-align: top;
        }

        th {
            font-size: 0.8rem;
            text-transform: uppercase;
            color: var(--muted);
            letter-spacing: 0.02em;
        }

        .actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        input, select, textarea {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid var(--border);
            font: inherit;
            margin-top: 6px;
        }

        label {
            display: block;
            font-size: 0.88rem;
            margin-bottom: 10px;
        }

        .muted {
            color: var(--muted);
            font-size: 0.85rem;
        }

        .grid {
            display: grid;
            gap: 12px;
        }

        .grid-cols-3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        @media (max-width: 900px) {
            .shell { grid-template-columns: 1fr; }
            .sidebar { display: none; }
            .grid-cols-3 { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="shell">
    <aside class="sidebar">
        <div class="brand">EduApp Admin</div>
        <nav class="nav">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <a href="{{ route('admin.grades.index') }}">Grades</a>
            <a href="{{ route('admin.subjects.index') }}">Subjects</a>
            <a href="{{ route('admin.quizzes.index') }}">Quizzes</a>
            <a href="{{ route('admin.questions.index') }}">Questions</a>
        </nav>
    </aside>

    <main class="content">
        <div class="topbar">
            <strong>{{ $title ?? 'Admin Panel' }}</strong>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button class="btn btn-danger" type="submit">Logout</button>
            </form>
        </div>

        @if (session('status'))
            <div class="flash flash-success">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="flash flash-error">
                <ul style="margin:0; padding-left: 16px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card">
            @yield('content')
        </div>
    </main>
</div>
</body>
</html>
