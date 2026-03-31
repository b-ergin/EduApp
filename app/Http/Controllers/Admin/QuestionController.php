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
    public function index(): View
    {
        return view('admin.questions.index', [
            'questions' => Question::with('quiz.subject.grade')->withCount('choices')->latest('id')->paginate(12),
        ]);
    }

    public function create(): View
    {
        return view('admin.questions.create', [
            'quizzes' => Quiz::with('subject.grade')->orderBy('title')->get(),
            'question' => new Question(),
            'choices' => array_fill(0, 4, ''),
            'correctChoiceIndex' => 0,
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
            'image_path' => $validated['image_path'] ?? null,
        ]);

        foreach ($validated['choices'] as $index => $choiceText) {
            $question->choices()->create([
                'choice_text' => $choiceText,
                'is_correct' => (int) $validated['correct_choice'] === (int) $index,
            ]);
        }

        return redirect()->route('admin.questions.index')->with('status', 'Question and choices created successfully.');
    }

    public function edit(Question $question): View
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
            'quizzes' => Quiz::with('subject.grade')->orderBy('title')->get(),
            'choices' => $choices,
            'correctChoiceIndex' => $correctChoiceIndex,
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
            'image_path' => $validated['image_path'] ?? null,
        ]);

        $question->choices()->delete();

        foreach ($validated['choices'] as $index => $choiceText) {
            $question->choices()->create([
                'choice_text' => $choiceText,
                'is_correct' => (int) $validated['correct_choice'] === (int) $index,
            ]);
        }

        return redirect()->route('admin.questions.index')->with('status', 'Question and choices updated successfully.');
    }

    public function destroy(Question $question): RedirectResponse
    {
        $question->delete();

        return redirect()->route('admin.questions.index')->with('status', 'Question deleted. Related choices were removed by cascade rules.');
    }
}
