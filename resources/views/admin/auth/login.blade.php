<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, #0f172a, #1e293b);
            font-family: "Poppins", "Segoe UI", sans-serif;
            color: #111827;
        }
        .card {
            width: min(420px, 92vw);
            background: #fff;
            border-radius: 14px;
            padding: 20px;
        }
        input {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            margin-top: 6px;
        }
        label { display: block; margin-bottom: 10px; font-size: 0.9rem; }
        .btn {
            width: 100%;
            padding: 10px;
            border: 0;
            border-radius: 8px;
            background: #0f766e;
            color: white;
            font-weight: 600;
            cursor: pointer;
        }
        .error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 10px;
            font-size: 0.88rem;
        }
    </style>
</head>
<body>
<div class="card">
    <h2 style="margin-top:0;">Admin Login</h2>

    @if ($errors->any())
        <div class="error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('admin.login.submit') }}">
        @csrf
        <label>Email
            <input type="email" name="email" value="{{ old('email') }}" required>
        </label>

        <label>Password
            <input type="password" name="password" required>
        </label>

        <label style="display:flex; align-items:center; gap:8px;">
            <input type="checkbox" name="remember" style="width:auto; margin:0;"> Remember me
        </label>

        <button class="btn" type="submit">Sign In</button>
    </form>

    <p style="font-size:0.84rem; color:#6b7280;">Default seeded admin: test@example.com / password</p>
</div>
</body>
</html>
