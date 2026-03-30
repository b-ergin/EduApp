@extends('admin.layout', ['title' => 'Edit Grade'])

@section('content')
    <h2 style="margin-top:0;">Edit Grade</h2>
    <form method="POST" action="{{ route('admin.grades.update', $grade) }}">
        @csrf
        @method('PUT')
        @include('admin.grades._form', ['buttonLabel' => 'Update Grade'])
    </form>
@endsection
