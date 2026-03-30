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

<button class="btn" type="submit">{{ $buttonLabel }}</button>
