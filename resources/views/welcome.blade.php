<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduApp</title>
    <style>
        :root {
            --bg-a: #e0ecff;
            --bg-b: #d2f5ee;
            --ink: #0f172a;
            --card-a: #0f172a;
            --card-b: #22324e;
            --student-a: #0f766e;
            --student-b: #14b8a6;
            --teacher-a: #1d4ed8;
            --teacher-b: #60a5fa;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Poppins", "Segoe UI", sans-serif;
            color: var(--ink);
            background:
                radial-gradient(1100px 500px at 12% -8%, #c7ddff 0%, rgba(199, 221, 255, 0) 70%),
                radial-gradient(900px 450px at 88% 8%, #baf3e8 0%, rgba(186, 243, 232, 0) 70%),
                linear-gradient(110deg, var(--bg-a), var(--bg-b));
            display: grid;
            place-items: center;
            padding: 20px;
        }

        .scene {
            width: 100%;
            max-width: 940px;
            position: relative;
        }

        .brand {
            font-size: 1.35rem;
            font-weight: 800;
            letter-spacing: 0.02em;
            margin: 0 0 10px;
            color: #0b1220;
        }

        .panel {
            position: relative;
            overflow: hidden;
            border-radius: 28px;
            background: linear-gradient(135deg, var(--card-a), var(--card-b));
            border: 1px solid rgba(255, 255, 255, 0.25);
            box-shadow:
                0 24px 60px rgba(15, 23, 42, 0.24),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            padding: clamp(24px, 4.2vw, 40px);
        }

        .panel::before {
            content: "";
            position: absolute;
            left: -120px;
            bottom: -120px;
            width: 260px;
            height: 260px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(20, 184, 166, 0.25), rgba(20, 184, 166, 0));
        }

        .panel::after {
            content: "";
            position: absolute;
            right: -80px;
            top: -70px;
            width: 250px;
            height: 250px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(56, 189, 248, 0.32), rgba(56, 189, 248, 0));
        }

        .portals {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
            max-width: 620px;
            margin-inline: auto;
        }

        .portal {
            text-decoration: none;
            border-radius: 16px;
            min-height: 64px;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.08rem;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: 0.01em;
            transition: transform 0.16s ease, box-shadow 0.16s ease, filter 0.16s ease;
            border: 1px solid rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(2px);
        }

        .portal:hover {
            transform: translateY(-2px) scale(1.01);
            filter: brightness(1.04);
        }

        .portal:active {
            transform: translateY(0) scale(0.995);
        }

        .student {
            background: linear-gradient(135deg, var(--student-a), var(--student-b));
            box-shadow: 0 12px 26px rgba(20, 184, 166, 0.3);
        }

        .teacher {
            background: linear-gradient(135deg, var(--teacher-a), var(--teacher-b));
            box-shadow: 0 12px 26px rgba(59, 130, 246, 0.3);
        }

        @media (max-width: 680px) {
            .scene {
                max-width: 520px;
            }

            .panel {
                border-radius: 24px;
            }

            .portals {
                grid-template-columns: 1fr;
                max-width: none;
            }

            .portal {
                min-height: 58px;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
<div class="scene">
    <p class="brand">EduApp</p>

    <section class="panel">
        <div class="portals">
            <a class="portal student" href="/quizzes">Student Portal</a>
            <a class="portal teacher" href="/admin/login">Teacher Portal</a>
        </div>
    </section>
</div>
</body>
</html>
