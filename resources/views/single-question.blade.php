<h1>Single Question Page</h1>
<p>{{ $question->question_text }}</p>

@if (session()->has('result'))
    <p>{{ session('result') ? 'Correct!' : 'Wrong!' }}</p>
@endif

@if (session()->has('result') && $nextQuestion)
    <a href="/quizzes/{{ $quiz->id }}/questions/{{ $nextQuestion->id }}">
        Next Question
    </a>
@endif

@if (session()->has('result') && !$nextQuestion)
    <p>You finished this quiz.</p>
@endif

<form method="POST" action="/quizzes/{{ $quiz->id }}/questions/{{ $question->id }}">
    @csrf
    @foreach ($question->choices as $choice)
        <div>
            <input type="radio" name="choice_id" value="{{ $choice->id }}">
            {{ $choice->choice_text }}
        </div>
    @endforeach
    <button type="submit">Submit Answer</button>
</form>