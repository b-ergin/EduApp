@extends('admin.layout', ['title' => 'Dashboard'])

@section('content')
    <h2 style="margin-top:0;">Teacher Overview</h2>
    <p class="muted">Quick health check of your learning content.</p>

    <div class="grid grid-cols-3" style="margin-top:14px;">
        <div><strong>{{ $stats['grades'] }}</strong><div class="muted">Grades</div></div>
        <div><strong>{{ $stats['subjects'] }}</strong><div class="muted">Subjects</div></div>
        <div><strong>{{ $stats['quizzes'] }}</strong><div class="muted">Quizzes</div></div>
        <div><strong>{{ $stats['questions'] }}</strong><div class="muted">Questions</div></div>
        <div><strong>{{ $stats['choices'] }}</strong><div class="muted">Choices</div></div>
        <div><strong>{{ $stats['teachers'] }}</strong><div class="muted">Admin Users</div></div>
    </div>
@endsection
