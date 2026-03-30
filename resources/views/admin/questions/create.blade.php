@extends('admin.layout', ['title' => 'Create Question'])

@section('content')
    <h2 style="margin-top:0;">Create Question</h2>
    <form method="POST" action="{{ route('admin.questions.store') }}">
        @csrf
        @include('admin.questions._form', ['buttonLabel' => 'Create Question'])
    </form>
@endsection
