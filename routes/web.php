<?php

use Illuminate\Support\Facades\Route;
use App\Models\Grade;
use App\Models\Quiz;
use App\Models\Question;
use Illuminate\Http\Request;
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

Route::get('/quizzes', function (Request $request) {
    $search = trim((string) $request->query('q', ''));
    $selectedGrade = (int) $request->query('grade', 0);
    $sessionProgress = $request->session()->get('student_progress', []);

    $quizzesQuery = Quiz::query()
        ->with('subject.grade')
        ->withCount('questions')
        ->orderBy('title');

    if ($search !== '') {
        $quizzesQuery->where(function ($query) use ($search) {
            $query->where('title', 'like', '%'.$search.'%')
                ->orWhereHas('subject', function ($subjectQuery) use ($search) {
                    $subjectQuery->where('name', 'like', '%'.$search.'%');
                });
        });
    }

    if ($selectedGrade > 0) {
        $quizzesQuery->whereHas('subject', function ($query) use ($selectedGrade) {
            $query->where('grade_id', $selectedGrade);
        });
    }

    $quizzes = $quizzesQuery->paginate(9)->withQueryString();

    $progressByQuiz = [];
    foreach ($quizzes as $quiz) {
        $quizProgress = $sessionProgress[$quiz->id] ?? [];
        $answered = collect($quizProgress['answered_question_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $answeredCount = min(count($answered), (int) $quiz->questions_count);
        $completed = (bool) ($quizProgress['completed'] ?? false);
        $percent = $quiz->questions_count > 0 ? (int) round(($answeredCount / $quiz->questions_count) * 100) : 0;
        $status = $completed ? 'completed' : ($answeredCount > 0 ? 'in_progress' : 'not_started');

        $progressByQuiz[$quiz->id] = [
            'answered_count' => $answeredCount,
            'total_questions' => (int) $quiz->questions_count,
            'completed' => $completed,
            'percent' => $percent,
            'status' => $status,
            'current_question_id' => $quizProgress['current_question_id'] ?? null,
        ];
    }

    $mapNodes = [];
    $previousCompleted = true;
    foreach ($quizzes as $quiz) {
        $progress = $progressByQuiz[$quiz->id];
        $status = $progress['status'];
        $isUnlocked = $status !== 'not_started' || $previousCompleted;

        $mapNodes[] = [
            'quiz' => $quiz,
            'status' => $status,
            'unlocked' => $isUnlocked,
            'percent' => $progress['percent'],
        ];

        $previousCompleted = $progress['completed'];
    }

    return view('quiz-selection', [
        'quizzes' => $quizzes,
        'grades' => Grade::orderBy('name')->get(),
        'progressByQuiz' => $progressByQuiz,
        'mapNodes' => $mapNodes,
        'search' => $search,
        'selectedGrade' => $selectedGrade,
    ]);
})->name('student.quizzes');

Route::get('/quizzes/{quiz}/start', function (Request $request, Quiz $quiz) {
    $progress = $request->session()->get('student_progress', []);
    $quizProgress = $progress[$quiz->id] ?? [];

    if (! $request->boolean('restart')) {
        $orderedQuizIds = Quiz::orderBy('title')->pluck('id')->values();
        $currentIndex = $orderedQuizIds->search($quiz->id);
        $previousQuizId = $currentIndex !== false && $currentIndex > 0 ? $orderedQuizIds[$currentIndex - 1] : null;
        $currentStatus = $quizProgress['completed'] ?? false
            ? 'completed'
            : (count($quizProgress['answered_question_ids'] ?? []) > 0 ? 'in_progress' : 'not_started');

        $previousCompleted = $previousQuizId
            ? (bool) (($progress[$previousQuizId]['completed'] ?? false))
            : true;

        if ($currentStatus === 'not_started' && ! $previousCompleted) {
            return redirect()->route('student.quizzes')->withErrors([
                'quiz' => 'This quiz is locked. Complete the previous node first.',
            ]);
        }
    }

    if ($request->boolean('restart')) {
        $quizProgress = [
            'answered_question_ids' => [],
            'current_question_id' => null,
            'completed' => false,
        ];
    }

    $firstQuestionId = Question::where('quiz_id', $quiz->id)->orderBy('id')->value('id');
    if (! $firstQuestionId) {
        return redirect()->route('student.quizzes')->withErrors([
            'quiz' => 'This quiz has no questions yet.',
        ]);
    }

    $currentQuestionId = $quizProgress['current_question_id'] ?? null;
    $currentQuestionBelongsToQuiz = $currentQuestionId
        ? Question::where('quiz_id', $quiz->id)->where('id', $currentQuestionId)->exists()
        : false;

    $targetQuestionId = $currentQuestionBelongsToQuiz ? $currentQuestionId : $firstQuestionId;

    $progress[$quiz->id] = [
        'answered_question_ids' => $quizProgress['answered_question_ids'] ?? [],
        'current_question_id' => $targetQuestionId,
        'completed' => (bool) ($quizProgress['completed'] ?? false),
    ];
    $request->session()->put('student_progress', $progress);

    return redirect()->route('student.question.show', [
        'quiz' => $quiz->id,
        'question' => $targetQuestionId,
    ]);
})->name('student.quiz.start');

Route::get('/quizzes/{quiz}/questions/{question}', function (Quiz $quiz, Question $question) {
    if ((int) $question->quiz_id !== (int) $quiz->id) {
        abort(404);
    }

    $question->load('choices');

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
})->name('student.question.show');

Route::post('/quizzes/{quiz}/questions/{question}', function (Request $request, Quiz $quiz, Question $question) {
    if ((int) $question->quiz_id !== (int) $quiz->id) {
        abort(404);
    }

    $validated = $request->validate(
        [
            'choice_id' => ['required', 'integer', 'exists:choices,id'],
        ],
        [
            'choice_id.required' => 'Please choose an option before submitting.',
        ]
    );

    $choice = \App\Models\Choice::where('id', $validated['choice_id'])
        ->where('question_id', $question->id)
        ->firstOrFail();

    $nextQuestion = Question::where('quiz_id', $quiz->id)
        ->where('id', '>', $question->id)
        ->orderBy('id')
        ->first();

    $progress = $request->session()->get('student_progress', []);
    $quizProgress = $progress[$quiz->id] ?? [
        'answered_question_ids' => [],
        'current_question_id' => $question->id,
        'completed' => false,
    ];

    $quizProgress['answered_question_ids'] = collect($quizProgress['answered_question_ids'] ?? [])
        ->push((int) $question->id)
        ->map(fn ($id) => (int) $id)
        ->unique()
        ->values()
        ->all();

    $quizProgress['current_question_id'] = $nextQuestion?->id;
    $quizProgress['completed'] = $nextQuestion ? false : true;

    $progress[$quiz->id] = $quizProgress;
    $request->session()->put('student_progress', $progress);

    return redirect()->back()->with([
        'result' => $choice->is_correct,
        'answered_question_id' => (int) $question->id,
        'selected_choice_id' => (int) $validated['choice_id'],
    ]);
})->name('student.question.answer');

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
