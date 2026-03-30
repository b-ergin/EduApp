@extends('admin.layout', ['title' => 'Create Grade'])

@section('content')
    <h2 style="margin-top:0;">Create Grade</h2>
    <form method="POST" action="{{ route('admin.grades.store') }}">
        @csrf
        @include('admin.grades._form', ['buttonLabel' => 'Create Grade'])
    </form>
@endsection
