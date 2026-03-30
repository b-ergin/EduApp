@extends('admin.layout', ['title' => 'Choices'])

@section('content')
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
        <h2 style="margin:0;">Choices</h2>
        <a class="btn" href="{{ route('admin.choices.create') }}">Add Choice</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Choice</th>
                <th>Question ID</th>
                <th>Correct</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($choices as $choice)
            <tr>
                <td>#{{ $choice->id }}</td>
                <td>{{ $choice->choice_text }}</td>
                <td>#{{ $choice->question_id }}</td>
                <td>{{ $choice->is_correct ? 'Yes' : 'No' }}</td>
                <td class="actions">
                    <a class="btn" href="{{ route('admin.choices.edit', $choice) }}">Edit</a>
                    <form method="POST" action="{{ route('admin.choices.destroy', $choice) }}" onsubmit="return confirm('Delete this choice?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger" type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5">No choices yet.</td></tr>
        @endforelse
        </tbody>
    </table>

    <div style="margin-top:12px;">{{ $choices->links() }}</div>
@endsection
