@extends('admin.layout', ['title' => 'Questions'])

@section('content')
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
        <h2 style="margin:0;">Questions</h2>
        <a class="btn" href="{{ route('admin.questions.create', ['quiz_id' => $selectedQuizId ?: null]) }}">Add Question</a>
    </div>

    @if ($selectedQuiz)
        <div style="border:1px solid #bfdbfe; background:#eff6ff; color:#1e3a8a; border-radius:10px; padding:10px; margin-bottom:12px;">
            Managing questions for:
            <strong>{{ $selectedQuiz->title }}</strong>
            @if($selectedQuiz->subject)
                - {{ $selectedQuiz->subject->name }}
            @endif
            @if($selectedQuiz->subject?->grade)
                ({{ $selectedQuiz->subject->grade->name }})
            @endif
        </div>
    @endif

    <form method="GET" action="{{ route('admin.questions.index') }}" style="display:grid; grid-template-columns: 1fr 1fr 1fr auto; gap:8px; margin-bottom:12px;">
        <select name="grade_id" id="grade_id_filter">
            <option value="0">All Grades</option>
            @foreach ($quizzes->pluck('subject.grade')->filter()->unique('id')->sortBy('name') as $grade)
                <option value="{{ $grade->id }}" @selected($selectedGradeId === (int) $grade->id)>{{ $grade->name }}</option>
            @endforeach
        </select>

        <select name="subject_id" id="subject_id_filter">
            <option value="0">All Subjects</option>
            @foreach ($quizzes->pluck('subject')->filter()->unique('id')->sortBy('name') as $subject)
                <option value="{{ $subject->id }}" @selected($selectedSubjectId === (int) $subject->id)>
                    {{ $subject->name }}
                    @if($subject->grade) ({{ $subject->grade->name }}) @endif
                </option>
            @endforeach
        </select>

        <select name="quiz_id" id="quiz_id_filter">
            <option value="0">All Quizzes</option>
            @foreach ($quizzes as $quiz)
                <option value="{{ $quiz->id }}" @selected($selectedQuizId === (int) $quiz->id)>
                    {{ $quiz->title }}
                    @if($quiz->subject) - {{ $quiz->subject->name }} @endif
                    @if($quiz->subject?->grade) ({{ $quiz->subject->grade->name }}) @endif
                </option>
            @endforeach
        </select>

        <button type="submit" class="btn">Apply</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Question</th>
                <th>Grade</th>
                <th>Subject</th>
                <th>Quiz</th>
                <th>Choices</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($questions as $question)
            <tr>
                <td>#{{ $question->id }}</td>
                <td>{{ $question->question_text }}</td>
                <td>{{ $question->quiz?->subject?->grade?->name }}</td>
                <td>{{ $question->quiz?->subject?->name }}</td>
                <td>{{ $question->quiz?->title }}</td>
                <td>{{ $question->choices_count }}</td>
                <td class="actions">
                    <a class="btn" href="{{ route('admin.questions.edit', ['question' => $question, 'quiz_id' => $selectedQuizId ?: $question->quiz_id]) }}">Edit</a>
                    <form method="POST" action="{{ route('admin.questions.destroy', $question) }}" onsubmit="return confirm('Delete this question and all related choices?');">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="return_quiz_id" value="{{ $selectedQuizId ?: $question->quiz_id }}">
                        <button class="btn btn-danger" type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="7">No questions yet.</td></tr>
        @endforelse
        </tbody>
    </table>

    <div style="margin-top:12px;">{{ $questions->links() }}</div>
@endsection
