@extends('admin.layout', ['title' => 'Create Quiz'])

@section('content')
    <h2 style="margin-top:0;">Create Quiz</h2>
    <form method="POST" action="{{ route('admin.quizzes.store') }}">
        @csrf
        @include('admin.quizzes._form', ['buttonLabel' => 'Create Quiz'])
    </form>
@endsection
