<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Quiz</title>
    <style>
        :root {
            --bg: #f3f6fb;
            --card: #ffffff;
            --text: #111827;
            --muted: #6b7280;
            --border: #e5e7eb;
            --primary: #0f766e;
            --primary-2: #14b8a6;
            --success: #166534;
            --warning: #9a3412;
            --info: #1e3a8a;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: "Poppins", "Segoe UI", sans-serif;
            color: var(--text);
            background: radial-gradient(circle at 0 0, #e0f2fe 0, rgba(224, 242, 254, 0) 45%), var(--bg);
        }

        .wrap {
            max-width: 1080px;
            margin: 0 auto;
            padding: 24px 16px 34px;
        }

        .head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 14px;
        }

        .head h1 {
            margin: 0;
            font-size: clamp(1.4rem, 2.8vw, 2rem);
        }

        .head p {
            margin: 4px 0 0;
            color: var(--muted);
            font-size: 0.92rem;
        }

        .btn-link {
            text-decoration: none;
            background: #fff;
            border: 1px solid var(--border);
            padding: 8px 12px;
            border-radius: 10px;
            color: #1f2937;
            font-size: 0.88rem;
            font-weight: 600;
        }

        .filters {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 12px;
            margin-bottom: 14px;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: 1fr 220px auto;
            gap: 10px;
        }

        input, select {
            width: 100%;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 10px;
            font: inherit;
        }

        .btn {
            border: 0;
            border-radius: 10px;
            padding: 10px 14px;
            font: inherit;
            font-weight: 700;
            color: #fff;
            background: linear-gradient(135deg, var(--primary), var(--primary-2));
            cursor: pointer;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .map {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 14px;
            margin-bottom: 14px;
        }

        .map h2 {
            margin: 0;
            font-size: 1.02rem;
        }

        .map p {
            margin: 6px 0 0;
            font-size: 0.86rem;
            color: var(--muted);
        }

        .path {
            position: relative;
            max-width: 450px;
            margin: 16px auto 0;
            padding: 8px 0 4px;
        }

        .map-svg {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 0;
        }

        .map-connection {
            fill: none;
            stroke: #d6dee9;
            stroke-width: 6;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .node-item {
            position: relative;
            width: 50%;
            margin-bottom: 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            z-index: 1;
        }

        .node-left {
            margin-right: auto;
        }

        .node-right {
            margin-left: auto;
        }

        .node-bubble {
            width: 84px;
            height: 84px;
            border-radius: 999px;
            border: 4px solid #7dd3fc;
            background: #eff6ff;
            color: #1e3a8a;
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.12);
            text-decoration: none;
        }

        .node-bubble:hover {
            transform: translateY(-2px);
        }

        .node-number {
            font-size: 1.2rem;
            line-height: 1;
        }

        .node-state {
            font-size: 0.65rem;
            margin-top: 3px;
            letter-spacing: 0.03em;
        }

        .node-complete .node-bubble {
            border-color: #86efac;
            background: #ecfdf5;
            color: #166534;
        }

        .node-progress .node-bubble {
            border-color: #fcd34d;
            background: #fffbeb;
            color: #92400e;
        }

        .node-locked .node-bubble {
            border-color: #d1d5db;
            background: #f3f4f6;
            color: #6b7280;
            box-shadow: none;
            cursor: not-allowed;
            pointer-events: none;
        }

        .node-title {
            margin: 7px 0 2px;
            font-size: 0.8rem;
            font-weight: 700;
            line-height: 1.3;
            text-align: center;
            max-width: 150px;
        }

        .node-meta {
            margin: 0;
            font-size: 0.72rem;
            color: var(--muted);
        }

        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 14px;
        }

        .title {
            margin: 0;
            font-size: 1rem;
            line-height: 1.35;
        }

        .meta {
            margin: 8px 0;
            color: var(--muted);
            font-size: 0.86rem;
            line-height: 1.45;
        }

        .badge {
            display: inline-block;
            font-size: 0.76rem;
            font-weight: 700;
            border-radius: 999px;
            padding: 5px 9px;
            border: 1px solid transparent;
        }

        .badge-completed {
            color: var(--success);
            background: #dcfce7;
            border-color: #86efac;
        }

        .badge-progress {
            color: var(--warning);
            background: #ffedd5;
            border-color: #fdba74;
        }

        .badge-new {
            color: var(--info);
            background: #dbeafe;
            border-color: #93c5fd;
        }

        .track {
            margin-top: 10px;
            width: 100%;
            height: 8px;
            border-radius: 999px;
            background: #e5e7eb;
            overflow: hidden;
        }

        .fill {
            height: 100%;
            border-radius: 999px;
            background: linear-gradient(90deg, #0f766e, #14b8a6);
        }

        .progress-text {
            margin: 6px 0 0;
            font-size: 0.8rem;
            color: var(--muted);
        }

        .actions {
            margin-top: 12px;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .action-btn {
            text-decoration: none;
            border-radius: 10px;
            padding: 9px 12px;
            font-size: 0.86rem;
            font-weight: 700;
        }

        .action-primary {
            color: #fff;
            background: linear-gradient(135deg, var(--primary), var(--primary-2));
        }

        .action-secondary {
            color: #0f172a;
            background: #eef2ff;
            border: 1px solid #c7d2fe;
        }

        .empty {
            background: #fff;
            border: 1px dashed #cbd5e1;
            border-radius: 14px;
            padding: 20px;
            color: var(--muted);
            text-align: center;
        }

        @media (max-width: 920px) {
            .grid { grid-template-columns: 1fr; }
            .filter-grid { grid-template-columns: 1fr; }
            .node-item {
                width: 50%;
            }
        }
    </style>
</head>
<body>
<div class="wrap">
    <div class="head">
        <div>
            <h1>Select Your Quiz</h1>
            <p>Pick a quiz, continue where you left off, or retake completed ones.</p>
        </div>
        <a class="btn-link" href="/">Back Home</a>
    </div>

    @if ($errors->any())
        <div class="empty" style="margin-bottom:12px; border-style:solid; color:#991b1b;">{{ $errors->first() }}</div>
    @endif

    <form class="filters" method="GET" action="{{ route('student.quizzes') }}">
        <div class="filter-grid">
            <input type="text" name="q" value="{{ $search }}" placeholder="Search by quiz title or subject...">

            <select name="grade">
                <option value="0">All levels</option>
                @foreach ($grades as $grade)
                    <option value="{{ $grade->id }}" @selected($selectedGrade === $grade->id)>{{ $grade->name }}</option>
                @endforeach
            </select>

            <button type="submit" class="btn">Apply Filters</button>
        </div>
    </form>

    @if (count($mapNodes) > 0)
        <section class="map">
            <h2>Adventure Path</h2>
            <p>Complete quizzes in order to unlock the next node.</p>

            <div class="path">
                <svg class="map-svg" aria-hidden="true"></svg>
                @foreach ($mapNodes as $index => $node)
                    @php
                        $quizNode = $node['quiz'];
                        $status = $node['status'];
                        $isUnlocked = $node['unlocked'];
                        $isCompleted = $status === 'completed';
                        $nodeClass = $index % 2 === 0 ? 'node-item node-left' : 'node-item node-right';
                        if (! $isUnlocked) {
                            $nodeClass .= ' node-locked';
                        }
                        if ($isCompleted) {
                            $nodeClass .= ' node-complete';
                        } elseif ($status === 'in_progress') {
                            $nodeClass .= ' node-progress';
                        }
                    @endphp

                    <article class="{{ $nodeClass }}">
                        @if ($isUnlocked)
                            <a class="node-bubble" href="{{ route('student.quiz.start', $quizNode) }}">
                                <span class="node-number">{{ $index + 1 }}</span>
                                @if ($isCompleted)
                                    <span class="node-state">DONE</span>
                                @elseif ($status === 'in_progress')
                                    <span class="node-state">NOW</span>
                                @else
                                    <span class="node-state">GO</span>
                                @endif
                            </a>
                        @else
                            <div class="node-bubble">
                                <span class="node-number">🔒</span>
                                <span class="node-state">LOCK</span>
                            </div>
                        @endif

                        <p class="node-title">{{ $quizNode->title }}</p>
                        <p class="node-meta">{{ $node['percent'] }}% complete</p>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    @if ($quizzes->count() === 0)
        <div class="empty">No quizzes match your filters yet.</div>
    @else
        <div class="grid">
            @foreach ($quizzes as $quiz)
                @php
                    $progress = $progressByQuiz[$quiz->id] ?? [
                        'status' => 'not_started',
                        'answered_count' => 0,
                        'total_questions' => 0,
                        'percent' => 0,
                        'completed' => false,
                    ];
                @endphp

                <article class="card">
                    <h2 class="title">{{ $quiz->title }}</h2>
                    <p class="meta">
                        Subject: {{ $quiz->subject?->name ?? 'N/A' }}<br>
                        Level: {{ $quiz->subject?->grade?->name ?? 'N/A' }}<br>
                        Questions: {{ $progress['total_questions'] }}
                    </p>

                    @if ($progress['status'] === 'completed')
                        <span class="badge badge-completed">Completed</span>
                    @elseif ($progress['status'] === 'in_progress')
                        <span class="badge badge-progress">In Progress</span>
                    @else
                        <span class="badge badge-new">Not Started</span>
                    @endif

                    <div class="track">
                        <div class="fill" style="width: {{ $progress['percent'] }}%;"></div>
                    </div>
                    <p class="progress-text">{{ $progress['answered_count'] }} / {{ $progress['total_questions'] }} answered ({{ $progress['percent'] }}%)</p>

                    <div class="actions">
                        @if ($progress['status'] === 'in_progress')
                            <a class="action-btn action-primary" href="{{ route('student.quiz.start', $quiz) }}">Resume Quiz</a>
                        @elseif ($progress['status'] === 'completed')
                            <a class="action-btn action-primary" href="{{ route('student.quiz.start', $quiz) }}?restart=1">Retake Quiz</a>
                        @else
                            <a class="action-btn action-primary" href="{{ route('student.quiz.start', $quiz) }}">Start Quiz</a>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        <div style="margin-top:12px;">{{ $quizzes->links() }}</div>
    @endif
</div>
<script>
    (function () {
        function drawMapConnections() {
            const path = document.querySelector('.path');
            const svg = path?.querySelector('.map-svg');
            const bubbles = path ? Array.from(path.querySelectorAll('.node-bubble')) : [];

            if (!path || !svg || bubbles.length < 2) {
                return;
            }

            const pathRect = path.getBoundingClientRect();
            const width = Math.max(pathRect.width, 1);
            const height = Math.max(pathRect.height, 1);

            svg.setAttribute('viewBox', `0 0 ${width} ${height}`);
            svg.innerHTML = '';

            for (let i = 0; i < bubbles.length - 1; i++) {
                const current = bubbles[i].getBoundingClientRect();
                const next = bubbles[i + 1].getBoundingClientRect();

                const x1 = current.left - pathRect.left + (current.width / 2);
                const y1 = current.top - pathRect.top + (current.height / 2);
                const x2 = next.left - pathRect.left + (next.width / 2);
                const y2 = next.top - pathRect.top + (next.height / 2);

                const controlX = (x1 + x2) / 2;
                const controlY = (y1 + y2) / 2 - 18;

                const pathEl = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                pathEl.setAttribute('class', 'map-connection');
                pathEl.setAttribute('d', `M ${x1} ${y1} Q ${controlX} ${controlY} ${x2} ${y2}`);
                svg.appendChild(pathEl);
            }
        }

        window.addEventListener('load', drawMapConnections);
        window.addEventListener('resize', drawMapConnections);
    })();
</script>
</body>
</html>
