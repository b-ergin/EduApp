@extends('admin.layout', ['title' => 'Edit Choice'])

@section('content')
    <h2 style="margin-top:0;">Edit Choice</h2>
    <form method="POST" action="{{ route('admin.choices.update', $choice) }}">
        @csrf
        @method('PUT')
        @include('admin.choices._form', ['buttonLabel' => 'Update Choice'])
    </form>
@endsection
