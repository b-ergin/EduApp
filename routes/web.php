<?php

use Illuminate\Support\Facades\Route;
use App\Models\Quiz;
use App\Models\Question;
use Illuminate\Http\Request;
use App\Models\Choice;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\ChoiceController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GradeController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\QuizController;
use App\Http\Controllers\Admin\SubjectController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return redirect()->route('admin.login');
})->name('login');

Route::get('/test-quiz', function () {
    $quiz = Quiz::with('questions.choices')->find(1);
    return view('test-quiz', ['quiz' => $quiz]);
});

Route::get('/quizzes/{quiz}/questions/{question}', function ($quizId, $questionId) {
    $quiz = Quiz::find($quizId);
    $question = Question::with('choices')->find($questionId);

    $nextQuestion = Question::where('quiz_id', $quiz->id)
        ->where('id', '>', $question->id)
        ->orderBy('id')
        ->first();

    $totalQuestions = Question::where('quiz_id', $quiz->id)->count();
    $currentIndex = Question::where('quiz_id', $quiz->id)->where('id', '<=', $question->id)->count();
    $progressPercent = $totalQuestions > 0 ? (int) round(($currentIndex / $totalQuestions) * 100) : 0;
   

    return view('single-question', [
        'quiz' => $quiz,
        'question' => $question,
        'nextQuestion' => $nextQuestion,
        'totalQuestions' => $totalQuestions,
        'currentIndex' => $currentIndex,
        'progressPercent' => $progressPercent,
    ]);
});

Route::post('/quizzes/{quiz}/questions/{question}', function (Request $request, $quizId, $questionId) {
    $validated = $request->validate(
        [
            'choice_id' => ['required', 'integer', 'exists:choices,id'],
        ],
        [
            'choice_id.required' => 'Please choose an option before submitting.',
        ]
    );

    $choice = \App\Models\Choice::where('id', $validated['choice_id'])
        ->where('question_id', $questionId)
        ->firstOrFail();

    return redirect()->back()->with([
        'result' => $choice->is_correct,
        'answered_question_id' => (int) $questionId,
        'selected_choice_id' => (int) $validated['choice_id'],
    ]);
});

Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
        Route::get('/', DashboardController::class)->name('admin.dashboard');

        Route::resource('grades', GradeController::class)->except(['show'])->names('admin.grades');
        Route::resource('subjects', SubjectController::class)->except(['show'])->names('admin.subjects');
        Route::resource('quizzes', QuizController::class)->except(['show'])->names('admin.quizzes');
        Route::resource('questions', QuestionController::class)->except(['show'])->names('admin.questions');
        Route::resource('choices', ChoiceController::class)->except(['show'])->names('admin.choices');
    });
});
