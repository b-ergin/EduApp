<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduApp Home</title>
    <style>
        :root {
            --bg: #f4f7fb;
            --card: #ffffff;
            --text: #111827;
            --muted: #6b7280;
            --primary: #0f766e;
            --primary-2: #14b8a6;
            --accent: #1d4ed8;
            --border: #e5e7eb;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Poppins", "Segoe UI", sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at 20% 0%, #dbeafe 0%, rgba(219, 234, 254, 0) 45%),
                radial-gradient(circle at 85% 15%, #ccfbf1 0%, rgba(204, 251, 241, 0) 45%),
                var(--bg);
        }

        .wrap {
            max-width: 1050px;
            margin: 0 auto;
            padding: 28px 16px 40px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .logo {
            font-size: 1.2rem;
            font-weight: 800;
            letter-spacing: 0.02em;
        }

        .pill {
            display: inline-block;
            font-size: 0.78rem;
            color: #0c4a6e;
            border: 1px solid #bae6fd;
            background: #f0f9ff;
            border-radius: 999px;
            padding: 5px 10px;
        }

        .hero {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #334155 100%);
            border-radius: 22px;
            padding: 26px;
            color: #f8fafc;
            border: 1px solid rgba(255, 255, 255, 0.14);
            box-shadow: 0 22px 45px rgba(15, 23, 42, 0.26);
            overflow: hidden;
            position: relative;
        }

        .hero::after {
            content: "";
            position: absolute;
            right: -65px;
            top: -65px;
            width: 220px;
            height: 220px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(20, 184, 166, 0.55), rgba(20, 184, 166, 0));
        }

        .hero h1 {
            margin: 0;
            font-size: clamp(1.55rem, 3.1vw, 2.35rem);
            line-height: 1.15;
            max-width: 700px;
        }

        .hero p {
            margin: 12px 0 0;
            color: #cbd5e1;
            max-width: 690px;
            line-height: 1.45;
        }

        .actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            border: 0;
            border-radius: 11px;
            padding: 10px 14px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: transform 0.14s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn-primary {
            color: #fff;
            background: linear-gradient(135deg, var(--primary), var(--primary-2));
        }

        .btn-light {
            color: #0f172a;
            background: #f8fafc;
        }

        .grid {
            margin-top: 18px;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 14px;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05);
        }

        .card h3 {
            margin: 0 0 6px;
            font-size: 1rem;
        }

        .card p {
            margin: 0;
            color: var(--muted);
            font-size: 0.88rem;
            line-height: 1.45;
        }

        .stack {
            margin-top: 16px;
            display: grid;
            gap: 10px;
        }

        .panel {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 15px;
        }

        .panel h2 {
            margin: 0 0 8px;
            font-size: 1.08rem;
        }

        .list {
            margin: 0;
            padding-left: 18px;
            color: var(--muted);
            font-size: 0.9rem;
            line-height: 1.55;
        }

        .endpoint {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            font-size: 0.83rem;
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 8px;
            margin-bottom: 6px;
        }

        .footer {
            margin-top: 18px;
            text-align: center;
            color: var(--muted);
            font-size: 0.84rem;
        }

        @media (max-width: 900px) {
            .grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="wrap">
    <div class="topbar">
        <div class="logo">EduApp</div>
        <span class="pill">Laravel + API Ready</span>
    </div>

    <section class="hero">
        <h1>Build Better Classroom Quizzes with Fast Teacher Workflows</h1>
        <p>
            Manage grades, subjects, quizzes, questions, and choices from the admin panel,
            while powering a mobile-ready API for future Flutter student experiences.
        </p>

        <div class="actions">
            <a class="btn btn-primary" href="/quizzes">Try Student Quiz</a>
            <a class="btn btn-light" href="/admin/login">Open Teacher Admin</a>
        </div>
    </section>

    <section class="grid">
        <article class="card">
            <h3>Teacher Admin Panel</h3>
            <p>Create and organize quiz content quickly with secure admin-only CRUD pages.</p>
        </article>
        <article class="card">
            <h3>Student Quiz Flow</h3>
            <p>Single-question experience with progress bar, answer feedback, and next-question flow.</p>
        </article>
        <article class="card">
            <h3>Mobile API v1</h3>
            <p>Sanctum-protected JSON endpoints ready for Flutter integration and scaling.</p>
        </article>
    </section>

    <section class="stack">
        <div class="panel">
            <h2>Quick Links</h2>
            <ul class="list">
                <li><a href="/admin/login">/admin/login</a> for teacher login</li>
                <li><a href="/quizzes">/quizzes</a> for quiz selection and student progress</li>
                <li><a href="/quizzes/1/questions/1">/quizzes/1/questions/1</a> for mobile-style single question</li>
            </ul>
        </div>

        <div class="panel">
            <h2>API Endpoints (v1)</h2>
            <div class="endpoint">POST /api/v1/login</div>
            <div class="endpoint">GET /api/v1/quizzes</div>
            <div class="endpoint">GET /api/v1/quizzes/{quiz}/questions/{question}</div>
            <div class="endpoint">POST /api/v1/questions/{question}/answer</div>
        </div>
    </section>

    <p class="footer">EduApp classroom foundation is running. Next step: gamification and Flutter UI.</p>
</div>
</body>
</html>
