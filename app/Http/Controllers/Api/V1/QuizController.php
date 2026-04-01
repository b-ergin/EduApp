<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Choice;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function index(): JsonResponse
    {
        $quizzes = Quiz::with('subject.grade')
            ->withCount('questions')
            ->orderByRaw('COALESCE(sort_order, id)')
            ->orderBy('id')
            ->get();

        return response()->json([
            'data' => $quizzes->map(function (Quiz $quiz) {
                return [
                    'id' => $quiz->id,
                    'title' => $quiz->title,
                    'subject' => $quiz->subject?->name,
                    'grade' => $quiz->subject?->grade?->name,
                    'questions_count' => $quiz->questions_count,
                    'sort_order' => (int) ($quiz->sort_order ?? $quiz->id),
                ];
            }),
        ]);
    }

    public function start(Quiz $quiz): JsonResponse
    {
        $firstQuestionId = Question::where('quiz_id', $quiz->id)
            ->orderBy('id')
            ->value('id');

        if (! $firstQuestionId) {
            return response()->json([
                'message' => 'This quiz has no questions yet.',
            ], 422);
        }

        return response()->json([
            'data' => [
                'quiz_id' => $quiz->id,
                'first_question_id' => (int) $firstQuestionId,
            ],
        ]);
    }

    public function showQuestion(Quiz $quiz, Question $question): JsonResponse
    {
        if ((int) $question->quiz_id !== (int) $quiz->id) {
            return response()->json(['message' => 'Question does not belong to this quiz.'], 404);
        }

        $question->load('choices');

        $totalQuestions = Question::where('quiz_id', $quiz->id)->count();
        $currentIndex = Question::where('quiz_id', $quiz->id)
            ->where('id', '<=', $question->id)
            ->count();

        return response()->json([
            'data' => [
                'quiz' => [
                    'id' => $quiz->id,
                    'title' => $quiz->title,
                ],
                'question' => [
                    'id' => $question->id,
                    'question_text' => $question->question_text,
                    'image_path' => $question->image_path,
                    'choices' => $question->choices->map(fn (Choice $choice) => [
                        'id' => $choice->id,
                        'choice_text' => $choice->choice_text,
                    ]),
                ],
                'progress' => [
                    'current_index' => $currentIndex,
                    'total_questions' => $totalQuestions,
                    'percent' => $totalQuestions > 0 ? (int) round(($currentIndex / $totalQuestions) * 100) : 0,
                ],
            ],
        ]);
    }

    public function submitAnswer(Request $request, Question $question): JsonResponse
    {
        $validated = $request->validate([
            'choice_id' => ['required', 'integer', 'exists:choices,id'],
        ]);

        $choice = Choice::where('id', $validated['choice_id'])
            ->where('question_id', $question->id)
            ->first();

        if (! $choice) {
            return response()->json(['message' => 'Selected choice does not belong to this question.'], 422);
        }

        $nextQuestion = Question::where('quiz_id', $question->quiz_id)
            ->where('id', '>', $question->id)
            ->orderBy('id')
            ->first();

        return response()->json([
            'data' => [
                'is_correct' => (bool) $choice->is_correct,
                'selected_choice_id' => $choice->id,
                'correct_choice_id' => Choice::where('question_id', $question->id)
                    ->where('is_correct', true)
                    ->value('id'),
                'next_question_id' => $nextQuestion?->id,
                'finished' => $nextQuestion ? false : true,
            ],
        ]);
    }
}
