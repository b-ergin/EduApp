@extends('admin.layout', ['title' => 'Create Question'])

@section('content')
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; gap:8px;">
        <h2 style="margin:0;">Create Question</h2>
        <a class="btn" href="{{ route('admin.questions.index', ['quiz_id' => $returnQuizId ?: null]) }}">Back to Questions</a>
    </div>
    <form method="POST" action="{{ route('admin.questions.store') }}">
        @csrf
        @include('admin.questions._form', ['buttonLabel' => 'Create Question'])
    </form>
@endsection
