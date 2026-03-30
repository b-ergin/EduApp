@extends('admin.layout', ['title' => 'Create Subject'])

@section('content')
    <h2 style="margin-top:0;">Create Subject</h2>
    <form method="POST" action="{{ route('admin.subjects.store') }}">
        @csrf
        @include('admin.subjects._form', ['buttonLabel' => 'Create Subject'])
    </form>
@endsection
