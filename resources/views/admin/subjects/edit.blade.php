@extends('admin.layout', ['title' => 'Edit Subject'])

@section('content')
    <h2 style="margin-top:0;">Edit Subject</h2>
    <form method="POST" action="{{ route('admin.subjects.update', $subject) }}">
        @csrf
        @method('PUT')
        @include('admin.subjects._form', ['buttonLabel' => 'Update Subject'])
    </form>
@endsection
