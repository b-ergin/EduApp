<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Choice;
use App\Models\Grade;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\Subject;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'stats' => [
                'grades' => Grade::count(),
                'subjects' => Subject::count(),
                'quizzes' => Quiz::count(),
                'questions' => Question::count(),
                'choices' => Choice::count(),
                'teachers' => User::where('is_admin', true)->count(),
            ],
        ]);
    }
}
