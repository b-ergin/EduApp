@extends('admin.layout', ['title' => 'Grades'])

@section('content')
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
        <h2 style="margin:0;">Grades</h2>
        <a class="btn" href="{{ route('admin.grades.create') }}">Add Grade</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Subjects</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($grades as $grade)
            <tr>
                <td>{{ $grade->name }}</td>
                <td>{{ $grade->subjects_count }}</td>
                <td class="actions">
                    <a class="btn" href="{{ route('admin.grades.edit', $grade) }}">Edit</a>
                    <form method="POST" action="{{ route('admin.grades.destroy', $grade) }}" onsubmit="return confirm('Delete this grade and all related data?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger" type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="3">No grades yet.</td></tr>
        @endforelse
        </tbody>
    </table>

    <div style="margin-top:12px;">{{ $grades->links() }}</div>
@endsection
