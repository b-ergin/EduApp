<form method="POST" action="/test-quiz">
    @csrf
<h1>Quiz Page</h1>

<h2>{{ $quiz->title }}</h2>
@foreach ($quiz->questions as $question)
<p>Question {{ $loop->iteration }}: {{ $question->question_text }}</p>
    <ul>
        @foreach ($question->choices as $choice)
        <li>
            <input type="radio" name="question_{{ $question->id }}">
            {{ $choice->choice_text }}
        </li>
        @endforeach
    </ul>
@endforeach
<button type="submit">Submit Quiz</button>
</form>