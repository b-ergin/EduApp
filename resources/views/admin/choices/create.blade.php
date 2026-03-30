@extends('admin.layout', ['title' => 'Create Choice'])

@section('content')
    <h2 style="margin-top:0;">Create Choice</h2>
    <form method="POST" action="{{ route('admin.choices.store') }}">
        @csrf
        @include('admin.choices._form', ['buttonLabel' => 'Create Choice'])
    </form>
@endsection
