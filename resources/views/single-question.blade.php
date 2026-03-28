<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Question</title>
    <style>
        :root {
            --bg: #f4f7fb;
            --card: #ffffff;
            --text: #1f2937;
            --muted: #6b7280;
            --primary: #0f766e;
            --primary-dark: #115e59;
            --success: #166534;
            --danger: #b91c1c;
            --border: #e5e7eb;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: "Poppins", "Segoe UI", sans-serif;
            background: linear-gradient(180deg, #eef6ff 0%, var(--bg) 100%);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            padding: 20px 12px;
        }

        .phone {
            width: 100%;
            max-width: 430px;
        }

        .card {
            background: var(--card);
            border-radius: 20px;
            border: 1px solid var(--border);
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
            padding: 20px;
        }

        h1 {
            margin: 0 0 8px;
            font-size: 1.2rem;
        }

        .question {
            margin: 0 0 18px;
            font-size: 1.05rem;
            line-height: 1.4;
        }

        .result {
            margin: 0 0 14px;
            font-weight: 600;
        }

        .correct { color: var(--success); }
        .wrong { color: var(--danger); }

        .option {
            display: block;
            padding: 12px 14px;
            border: 1px solid var(--border);
            border-radius: 12px;
            margin-bottom: 10px;
            cursor: pointer;
            background: #fff;
        }

        .option-correct {
            border-color: #16a34a;
            background: #dcfce7;
        }

        .option-wrong {
            border-color: #dc2626;
            background: #fee2e2;
        }


        .option input {
            margin-right: 10px;
        }

        .btn {
            width: 100%;
            border: 0;
            border-radius: 12px;
            padding: 12px 14px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            background: var(--primary);
            color: white;
            margin-top: 8px;
        }

        .btn:hover { background: var(--primary-dark); }

        .next-link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            margin-top: 14px;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.95rem;
            color: #ffffff;
            background: linear-gradient(135deg, #0f766e, #14b8a6);
            border-radius: 12px;
            padding: 12px 14px;
            box-shadow: 0 8px 18px rgba(20, 184, 166, 0.25);
            transition: transform 0.15s ease, box-shadow 0.15s ease, filter 0.15s ease;
        }

        .next-link:hover {
            transform: translateY(-1px);
            filter: brightness(1.02);
            box-shadow: 0 10px 22px rgba(20, 184, 166, 0.35);
        }

        .next-link:active {
            transform: translateY(0);
        }


        .done {
            margin-top: 14px;
            padding: 14px;
            border-radius: 12px;
            border: 1px solid #bfdbfe;
            background: #eff6ff;
            color: #1e3a8a;
        }

        .done-title {
            margin: 0 0 4px;
            font-weight: 700;
            font-size: 0.95rem;
        }

        .done-text {
            margin: 0;
            font-size: 0.9rem;
            color: #1d4ed8;
        }


    </style>
</head>
<body>
    @php
    $answered = session('answered_question_id') == $question->id;
    $selectedChoiceId = session('selected_choice_id');
    @endphp

<div class="phone">
    <div class="card">
        <h1>Question</h1>
        <p class="question">{{ $question->question_text }}</p>

        @if ($answered && !$nextQuestion)
            <p class="done">You finished this quiz.</p>
        @endif


        <form method="POST" action="/quizzes/{{ $quiz->id }}/questions/{{ $question->id }}">
            @csrf
            @foreach ($question->choices as $choice)
                @php
                    $isSelected = (int) $selectedChoiceId === (int) $choice->id;
                    $isCorrect = (bool) $choice->is_correct;

                    $optionClass = 'option';

                    if ($answered && $isCorrect) {
                        $optionClass .= ' option-correct';
                    }

                    if ($answered && $isSelected && !$isCorrect) {
                        $optionClass .= ' option-wrong';
                    }
                @endphp

                <label class="{{ $optionClass }}">
                    <input
                        type="radio"
                        name="choice_id"
                        value="{{ $choice->id }}"
                        {{ $isSelected ? 'checked' : '' }}
                        {{ $answered ? 'disabled' : '' }}
                    >
                    {{ $choice->choice_text }}
                </label>
            @endforeach


            @if (!$answered)
                <button type="submit" class="btn">Submit Answer</button>
            @endif

        </form>

            @if ($answered)
                <p class="result {{ session('result') ? 'correct' : 'wrong' }}">
                    {{ session('result') ? 'Correct!' : 'Wrong!' }}
                </p>
            @endif

            @if ($answered && $nextQuestion)
            <a class="next-link" href="/quizzes/{{ $quiz->id }}/questions/{{ $nextQuestion->id }}">
                Continue to Next Question <span aria-hidden="true">→</span>
            </a>
            @endif

    </div>
</div>
</body>
</html>
