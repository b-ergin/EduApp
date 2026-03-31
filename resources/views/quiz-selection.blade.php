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
            gap: 10px;
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

        .path-wrap {
            overflow-x: auto;
            padding-bottom: 6px;
            -webkit-overflow-scrolling: touch;
        }

        .path {
            position: relative;
            width: max-content;
            min-width: 100%;
            margin: 12px 0 0;
            padding: 12px 14px 10px;
            display: flex;
            gap: 14px;
            align-items: center;
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
            stroke: #cbd5e1;
            stroke-width: 5;
            stroke-linecap: round;
            stroke-linejoin: round;
            opacity: 0.9;
        }

        .node-item {
            position: relative;
            width: 92px;
            flex: 0 0 92px;
            display: flex;
            flex-direction: column;
            align-items: center;
            z-index: 1;
        }

        .node-up {
            transform: translateY(-8px);
        }

        .node-down {
            transform: translateY(8px);
        }

        .node-bubble {
            width: 68px;
            height: 68px;
            border-radius: 999px;
            border: 3px solid #60a5fa;
            background: #dbeafe;
            color: #1e3a8a;
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            box-shadow: 0 5px 10px rgba(59, 130, 246, 0.2);
            text-decoration: none;
            transition: transform 0.18s ease, box-shadow 0.18s ease, filter 0.18s ease;
            position: relative;
        }

        .node-bubble:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 12px rgba(59, 130, 246, 0.24);
        }

        .node-number {
            font-size: 1rem;
            line-height: 1;
        }

        .node-state {
            font-size: 0.56rem;
            margin-top: 2px;
            letter-spacing: 0.03em;
        }

        .node-complete .node-bubble {
            border-color: #22c55e;
            background: #dcfce7;
            color: #166534;
            box-shadow: 0 5px 10px rgba(22, 101, 52, 0.18);
        }

        .node-progress .node-bubble {
            border-color: #f59e0b;
            background: #fef3c7;
            color: #92400e;
            box-shadow: 0 5px 10px rgba(180, 83, 9, 0.18);
        }

        .node-locked .node-bubble {
            border-color: #d1d5db;
            background: #f3f4f6;
            color: #6b7280;
            box-shadow: none;
            cursor: not-allowed;
            pointer-events: none;
            filter: grayscale(0.25);
        }

        .node-title {
            margin: 6px 0 1px;
            font-size: 0.74rem;
            font-weight: 700;
            line-height: 1.3;
            text-align: center;
            max-width: 120px;
        }

        .node-meta {
            margin: 0;
            font-size: 0.68rem;
            color: var(--muted);
        }

        .stars {
            margin-top: 4px;
            letter-spacing: 0.02em;
            line-height: 1;
            user-select: none;
        }

        .star-on {
            color: #f59e0b;
        }

        .star-off {
            color: #cbd5e1;
        }

        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 10px 11px;
        }

        .title {
            margin: 0;
            font-size: 0.95rem;
            line-height: 1.25;
        }

        .meta {
            margin: 5px 0 6px;
            color: var(--muted);
            font-size: 0.79rem;
            line-height: 1.3;
        }

        .badge {
            display: inline-block;
            font-size: 0.7rem;
            font-weight: 700;
            border-radius: 999px;
            padding: 4px 8px;
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
            margin-top: 7px;
            width: 100%;
            height: 6px;
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
            margin: 4px 0 0;
            font-size: 0.74rem;
            color: var(--muted);
        }

        .actions {
            margin-top: 8px;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .action-btn {
            text-decoration: none;
            border-radius: 10px;
            padding: 7px 10px;
            font-size: 0.8rem;
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
            .grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .filter-grid { grid-template-columns: 1fr; }
            .path { min-width: max-content; }
        }

        @media (max-width: 620px) {
            .grid { grid-template-columns: 1fr; }
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

            <div class="path-wrap">
                <div class="path">
                    <svg class="map-svg" aria-hidden="true"></svg>
                    @foreach ($mapNodes as $index => $node)
                    @php
                        $quizNode = $node['quiz'];
                        $status = $node['status'];
                        $isUnlocked = $node['unlocked'];
                        $isCompleted = $status === 'completed';
                        $nodeClass = $index % 2 === 0 ? 'node-item node-up' : 'node-item node-down';
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
                        <div class="stars" aria-label="Stars earned">
                            @for ($star = 1; $star <= 3; $star++)
                                <span class="{{ $star <= $node['stars'] ? 'star-on' : 'star-off' }}">★</span>
                            @endfor
                        </div>
                    </article>
                    @endforeach
                </div>
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
                        'correct_count' => 0,
                        'total_questions' => 0,
                        'percent' => 0,
                        'score_percent' => 0,
                        'stars' => 0,
                        'completed' => false,
                    ];
                @endphp

                <article class="card">
                    <h2 class="title">{{ $quiz->title }}</h2>
                    <p class="meta">
                        {{ $quiz->subject?->name ?? 'N/A' }} •
                        {{ $quiz->subject?->grade?->name ?? 'N/A' }} •
                        {{ $progress['total_questions'] }} questions
                    </p>

                    @if ($progress['status'] === 'completed')
                        <span class="badge badge-completed">Completed</span>
                    @elseif ($progress['status'] === 'in_progress')
                        <span class="badge badge-progress">In Progress</span>
                    @else
                        <span class="badge badge-new">Not Started</span>
                    @endif
                    <div class="stars" aria-label="Stars earned">
                        @for ($star = 1; $star <= 3; $star++)
                            <span class="{{ $star <= ($progress['stars'] ?? 0) ? 'star-on' : 'star-off' }}">★</span>
                        @endfor
                    </div>

                    <div class="track">
                        <div class="fill" style="width: {{ $progress['percent'] }}%;"></div>
                    </div>
                    <p class="progress-text">
                        {{ $progress['answered_count'] }}/{{ $progress['total_questions'] }} answered ({{ $progress['percent'] }}%) •
                        {{ $progress['correct_count'] ?? 0 }} correct ({{ $progress['score_percent'] ?? 0 }}%)
                    </p>

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
