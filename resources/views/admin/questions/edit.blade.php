@extends('admin.layout', ['title' => 'Edit Question'])

@section('content')
    <h2 style="margin-top:0;">Edit Question</h2>
    <form method="POST" action="{{ route('admin.questions.update', $question) }}">
        @csrf
        @method('PUT')
        @include('admin.questions._form', ['buttonLabel' => 'Update Question'])
    </form>
@endsection
