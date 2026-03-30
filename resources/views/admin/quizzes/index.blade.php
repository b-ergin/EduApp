@extends('admin.layout', ['title' => 'Quizzes'])

@section('content')
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
        <h2 style="margin:0;">Quizzes</h2>
        <a class="btn" href="{{ route('admin.quizzes.create') }}">Add Quiz</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Subject</th>
                <th>Grade</th>
                <th>Questions</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($quizzes as $quiz)
            <tr>
                <td>{{ $quiz->title }}</td>
                <td>{{ $quiz->subject?->name }}</td>
                <td>{{ $quiz->subject?->grade?->name }}</td>
                <td>{{ $quiz->questions_count }}</td>
                <td class="actions">
                    <a class="btn" href="{{ route('admin.quizzes.edit', $quiz) }}">Edit</a>
                    <form method="POST" action="{{ route('admin.quizzes.destroy', $quiz) }}" onsubmit="return confirm('Delete this quiz and all related questions?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger" type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5">No quizzes yet.</td></tr>
        @endforelse
        </tbody>
    </table>

    <div style="margin-top:12px;">{{ $quizzes->links() }}</div>
@endsection
