@extends('admin.layout', ['title' => 'Edit Quiz'])

@section('content')
    <h2 style="margin-top:0;">Edit Quiz</h2>
    <form method="POST" action="{{ route('admin.quizzes.update', $quiz) }}">
        @csrf
        @method('PUT')
        @include('admin.quizzes._form', ['buttonLabel' => 'Update Quiz'])
    </form>
@endsection
