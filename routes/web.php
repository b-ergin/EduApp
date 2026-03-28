<?php

use Illuminate\Support\Facades\Route;
use App\Models\Quiz;
use App\Models\Question;
use Illuminate\Http\Request;
use App\Models\Choice;

Route::get('/', function () {
    return view('welcome');
});

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

    return view('single-question', [
        'quiz' => $quiz,
        'question' => $question,
        'nextQuestion' => $nextQuestion
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

