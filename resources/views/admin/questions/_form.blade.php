<label for="quiz_id">Quiz
    <select id="quiz_id" name="quiz_id" required>
        <option value="">Select quiz</option>
        @foreach ($quizzes as $quizItem)
            <option value="{{ $quizItem->id }}" @selected((int) old('quiz_id', $question->quiz_id ?? 0) === $quizItem->id)>
                {{ $quizItem->title }}
                @if($quizItem->subject)
                    - {{ $quizItem->subject->name }}
                @endif
                @if($quizItem->subject?->grade)
                    ({{ $quizItem->subject->grade->name }})
                @endif
            </option>
        @endforeach
    </select>
</label>

<label for="question_text">Question Text
    <textarea id="question_text" name="question_text" rows="4" required>{{ old('question_text', $question->question_text ?? '') }}</textarea>
</label>

<label for="image_path">Image Path (optional)
    <input id="image_path" name="image_path" type="text" value="{{ old('image_path', $question->image_path ?? '') }}">
</label>

@php
    $choiceValues = old('choices', $choices ?? array_fill(0, 4, ''));
    $selectedCorrect = (int) old('correct_choice', $correctChoiceIndex ?? 0);
@endphp

<div style="border:1px solid #e5e7eb; border-radius:10px; padding:12px; margin-bottom:12px;">
    <p style="margin:0 0 10px 0; font-weight:600;">Answer Choices</p>

    @for ($i = 0; $i < 4; $i++)
        <label for="choice_{{ $i }}">Choice {{ $i + 1 }}
            <input
                id="choice_{{ $i }}"
                name="choices[]"
                type="text"
                value="{{ $choiceValues[$i] ?? '' }}"
                required
            >
        </label>
        @error("choices.$i")
            <p style="margin-top:-6px; color:#991b1b; font-size:0.82rem;">{{ $message }}</p>
        @enderror
    @endfor

    @error('choices')
        <p style="margin:4px 0 0 0; color:#991b1b; font-size:0.82rem;">{{ $message }}</p>
    @enderror
</div>

<div style="border:1px solid #e5e7eb; border-radius:10px; padding:12px; margin-bottom:14px;">
    <p style="margin:0 0 10px 0; font-weight:600;">Correct Answer</p>
    <div style="display:grid; gap:8px;">
        @for ($i = 0; $i < 4; $i++)
            <label style="display:flex; align-items:center; gap:8px; margin-bottom:0;">
                <input
                    type="radio"
                    name="correct_choice"
                    value="{{ $i }}"
                    @checked($selectedCorrect === $i)
                >
                <span>Choice {{ $i + 1 }} is correct</span>
            </label>
        @endfor
    </div>
    @error('correct_choice')
        <p style="margin:8px 0 0 0; color:#991b1b; font-size:0.82rem;">{{ $message }}</p>
    @enderror
</div>

<button class="btn" type="submit">{{ $buttonLabel }}</button>
