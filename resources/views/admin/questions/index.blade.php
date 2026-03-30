@extends('admin.layout', ['title' => 'Questions'])

@section('content')
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
        <h2 style="margin:0;">Questions</h2>
        <a class="btn" href="{{ route('admin.questions.create') }}">Add Question</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Question</th>
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
                <td>{{ $question->quiz?->title }}</td>
                <td>{{ $question->choices_count }}</td>
                <td class="actions">
                    <a class="btn" href="{{ route('admin.questions.edit', $question) }}">Edit</a>
                    <form method="POST" action="{{ route('admin.questions.destroy', $question) }}" onsubmit="return confirm('Delete this question and all related choices?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger" type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5">No questions yet.</td></tr>
        @endforelse
        </tbody>
    </table>

    <div style="margin-top:12px;">{{ $questions->links() }}</div>
@endsection
