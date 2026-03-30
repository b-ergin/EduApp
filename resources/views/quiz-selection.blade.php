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

        .map-track {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
            gap: 10px;
            margin-top: 12px;
        }

        .node {
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 10px;
            background: #fff;
        }

        .node-locked {
            background: #f8fafc;
            border-color: #d1d5db;
            opacity: 0.82;
        }

        .node-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .node-num {
            width: 28px;
            height: 28px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.8rem;
            background: #dbeafe;
            color: #1e3a8a;
        }

        .node-complete .node-num {
            background: #dcfce7;
            color: #166534;
        }

        .node-locked .node-num {
            background: #e5e7eb;
            color: #6b7280;
        }

        .node-title {
            margin: 8px 0 4px;
            font-size: 0.9rem;
            font-weight: 700;
        }

        .node-link {
            display: inline-block;
            margin-top: 8px;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 700;
            color: #0f766e;
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

            <div class="map-track">
                @foreach ($mapNodes as $index => $node)
                    @php
                        $quizNode = $node['quiz'];
                        $status = $node['status'];
                        $isUnlocked = $node['unlocked'];
                        $isCompleted = $status === 'completed';
                        $nodeClass = 'node';
                        if (! $isUnlocked) {
                            $nodeClass .= ' node-locked';
                        }
                        if ($isCompleted) {
                            $nodeClass .= ' node-complete';
                        }
                    @endphp

                    <article class="{{ $nodeClass }}">
                        <div class="node-head">
                            <span class="node-num">{{ $index + 1 }}</span>
                            @if ($isCompleted)
                                <span class="badge badge-completed">Done</span>
                            @elseif ($status === 'in_progress')
                                <span class="badge badge-progress">Current</span>
                            @elseif (! $isUnlocked)
                                <span class="badge badge-new">Locked</span>
                            @else
                                <span class="badge badge-new">Open</span>
                            @endif
                        </div>

                        <p class="node-title">{{ $quizNode->title }}</p>
                        <p class="progress-text">{{ $node['percent'] }}% complete</p>

                        @if ($isUnlocked)
                            <a class="node-link" href="{{ route('student.quiz.start', $quizNode) }}">
                                {{ $isCompleted ? 'Play Again' : ($status === 'in_progress' ? 'Resume Node' : 'Start Node') }}
                            </a>
                        @endif
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
</body>
</html>
