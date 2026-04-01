<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuestionController extends Controller
{
    public function index(Request $request): View
    {
        $selectedGradeId = (int) $request->query('grade_id', 0);
        $selectedSubjectId = (int) $request->query('subject_id', 0);
        $selectedQuizId = (int) $request->query('quiz_id', 0);

        $questionsQuery = Question::with('quiz.subject.grade')->withCount('choices');

        if ($selectedGradeId > 0) {
            $questionsQuery->whereHas('quiz.subject', function ($query) use ($selectedGradeId) {
                $query->where('grade_id', $selectedGradeId);
            });
        }

        if ($selectedSubjectId > 0) {
            $questionsQuery->whereHas('quiz', function ($query) use ($selectedSubjectId) {
                $query->where('subject_id', $selectedSubjectId);
            });
        }

        if ($selectedQuizId > 0) {
            $questionsQuery->where('quiz_id', $selectedQuizId);
        }

        $selectedQuiz = $selectedQuizId > 0
            ? Quiz::with('subject.grade')->find($selectedQuizId)
            : null;

        return view('admin.questions.index', [
            'questions' => $questionsQuery->latest('id')->paginate(12)->withQueryString(),
            'quizzes' => Quiz::with('subject.grade')
                ->orderByRaw('COALESCE(sort_order, id)')
                ->orderBy('id')
                ->get(),
            'selectedGradeId' => $selectedGradeId,
            'selectedSubjectId' => $selectedSubjectId,
            'selectedQuizId' => $selectedQuizId,
            'selectedQuiz' => $selectedQuiz,
        ]);
    }

    public function create(Request $request): View
    {
        $selectedQuizId = (int) $request->query('quiz_id', 0);

        return view('admin.questions.create', [
            'quizzes' => Quiz::with('subject.grade')
                ->orderByRaw('COALESCE(sort_order, id)')
                ->orderBy('id')
                ->get(),
            'question' => new Question(),
            'choices' => array_fill(0, 4, ''),
            'correctChoiceIndex' => 0,
            'returnQuizId' => $selectedQuizId > 0 ? $selectedQuizId : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'question_text' => ['required', 'string'],
            'quiz_id' => ['required', 'integer', 'exists:quizzes,id'],
            'image_path' => ['nullable', 'string', 'max:255'],
            'choices' => ['required', 'array', 'size:4'],
            'choices.*' => ['required', 'string', 'max:255'],
            'correct_choice' => ['required', 'integer', 'between:0,3'],
        ]);

        $question = Question::create([
            'question_text' => $validated['question_text'],
            'quiz_id' => $validated['quiz_id'],
            'image_path' => $this->normalizeImagePath($validated['image_path'] ?? null),
        ]);

        foreach ($validated['choices'] as $index => $choiceText) {
            $question->choices()->create([
                'choice_text' => $choiceText,
                'is_correct' => (int) $validated['correct_choice'] === (int) $index,
            ]);
        }

        $returnQuizId = (int) $request->input('return_quiz_id', 0);
        $targetQuizId = $returnQuizId > 0 ? $returnQuizId : (int) $validated['quiz_id'];

        return redirect()->route('admin.questions.index', ['quiz_id' => $targetQuizId])->with('status', 'Question and choices created successfully.');
    }

    public function edit(Request $request, Question $question): View
    {
        $choices = $question->choices()->orderBy('id')->pluck('choice_text')->values()->all();
        $choices = array_pad($choices, 4, '');

        $correctChoiceId = $question->choices()->where('is_correct', true)->value('id');
        $correctChoiceIndex = 0;
        foreach ($question->choices()->orderBy('id')->get() as $index => $choice) {
            if ((int) $choice->id === (int) $correctChoiceId) {
                $correctChoiceIndex = $index;
                break;
            }
        }

        return view('admin.questions.edit', [
            'question' => $question,
            'quizzes' => Quiz::with('subject.grade')
                ->orderByRaw('COALESCE(sort_order, id)')
                ->orderBy('id')
                ->get(),
            'choices' => $choices,
            'correctChoiceIndex' => $correctChoiceIndex,
            'returnQuizId' => (int) $request->query('quiz_id', $question->quiz_id),
        ]);
    }

    public function update(Request $request, Question $question): RedirectResponse
    {
        $validated = $request->validate([
            'question_text' => ['required', 'string'],
            'quiz_id' => ['required', 'integer', 'exists:quizzes,id'],
            'image_path' => ['nullable', 'string', 'max:255'],
            'choices' => ['required', 'array', 'size:4'],
            'choices.*' => ['required', 'string', 'max:255'],
            'correct_choice' => ['required', 'integer', 'between:0,3'],
        ]);

        $question->update([
            'question_text' => $validated['question_text'],
            'quiz_id' => $validated['quiz_id'],
            'image_path' => $this->normalizeImagePath($validated['image_path'] ?? null),
        ]);

        $question->choices()->delete();

        foreach ($validated['choices'] as $index => $choiceText) {
            $question->choices()->create([
                'choice_text' => $choiceText,
                'is_correct' => (int) $validated['correct_choice'] === (int) $index,
            ]);
        }

        $returnQuizId = (int) $request->input('return_quiz_id', 0);
        $targetQuizId = $returnQuizId > 0 ? $returnQuizId : (int) $validated['quiz_id'];

        return redirect()->route('admin.questions.index', ['quiz_id' => $targetQuizId])->with('status', 'Question and choices updated successfully.');
    }

    public function destroy(Request $request, Question $question): RedirectResponse
    {
        $returnQuizId = (int) $request->input('return_quiz_id', $question->quiz_id);
        $question->delete();

        return redirect()->route('admin.questions.index', ['quiz_id' => $returnQuizId])->with('status', 'Question deleted. Related choices were removed by cascade rules.');
    }

    private function normalizeImagePath(?string $rawPath): ?string
    {
        $path = trim((string) $rawPath);
        if ($path === '') {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $path)) {
            return $path;
        }

        if (str_starts_with($path, '/question-images/')) {
            return $path;
        }

        if (str_starts_with($path, 'question-images/')) {
            return '/'.$path;
        }

        if (file_exists($path) && is_file($path)) {
            $destinationDir = public_path('question-images');
            if (! is_dir($destinationDir)) {
                mkdir($destinationDir, 0755, true);
            }

            $extension = pathinfo($path, PATHINFO_EXTENSION) ?: 'jpg';
            $filename = uniqid('qimg_', true).'.'.$extension;
            $destination = $destinationDir.DIRECTORY_SEPARATOR.$filename;

            if (@copy($path, $destination)) {
                return '/question-images/'.$filename;
            }
        }

        return str_starts_with($path, '/') ? $path : '/'.$path;
    }
}
