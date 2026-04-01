@extends('admin.layout', ['title' => 'Edit Question'])

@section('content')
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; gap:8px;">
        <h2 style="margin:0;">Edit Question</h2>
        <a class="btn" href="{{ route('admin.questions.index', ['quiz_id' => $returnQuizId ?: $question->quiz_id]) }}">Back to Questions</a>
    </div>
    <form method="POST" action="{{ route('admin.questions.update', $question) }}">
        @csrf
        @method('PUT')
        @include('admin.questions._form', ['buttonLabel' => 'Update Question'])
    </form>
@endsection
