@extends('admin.layout', ['title' => 'Subjects'])

@section('content')
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
        <h2 style="margin:0;">Subjects</h2>
        <a class="btn" href="{{ route('admin.subjects.create') }}">Add Subject</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Grade</th>
                <th>Quizzes</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($subjects as $subject)
            <tr>
                <td>{{ $subject->name }}</td>
                <td>{{ $subject->grade?->name }}</td>
                <td>{{ $subject->quizzes_count }}</td>
                <td class="actions">
                    <a class="btn" href="{{ route('admin.subjects.edit', $subject) }}">Edit</a>
                    <form method="POST" action="{{ route('admin.subjects.destroy', $subject) }}" onsubmit="return confirm('Delete this subject and all related quizzes?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger" type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="4">No subjects yet.</td></tr>
        @endforelse
        </tbody>
    </table>

    <div style="margin-top:12px;">{{ $subjects->links() }}</div>
@endsection
