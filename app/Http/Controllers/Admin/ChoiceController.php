<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Choice;
use App\Models\Question;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChoiceController extends Controller
{
    public function index(): View
    {
        return view('admin.choices.index', [
            'choices' => Choice::with('question.quiz.subject.grade')->latest('id')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.choices.create', [
            'questions' => Question::with('quiz.subject.grade')->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'choice_text' => ['required', 'string', 'max:255'],
            'question_id' => ['required', 'integer', 'exists:questions,id'],
            'is_correct' => ['nullable', 'boolean'],
        ]);

        $validated['is_correct'] = $request->boolean('is_correct');

        $choice = Choice::create($validated);

        if ($choice->is_correct) {
            Choice::where('question_id', $choice->question_id)
                ->where('id', '!=', $choice->id)
                ->update(['is_correct' => false]);
        }

        return redirect()->route('admin.choices.index')->with('status', 'Choice created successfully.');
    }

    public function edit(Choice $choice): View
    {
        return view('admin.choices.edit', [
            'choice' => $choice,
            'questions' => Question::with('quiz.subject.grade')->orderBy('id')->get(),
        ]);
    }

    public function update(Request $request, Choice $choice): RedirectResponse
    {
        $validated = $request->validate([
            'choice_text' => ['required', 'string', 'max:255'],
            'question_id' => ['required', 'integer', 'exists:questions,id'],
            'is_correct' => ['nullable', 'boolean'],
        ]);

        $validated['is_correct'] = $request->boolean('is_correct');

        $choice->update($validated);

        if ($choice->is_correct) {
            Choice::where('question_id', $choice->question_id)
                ->where('id', '!=', $choice->id)
                ->update(['is_correct' => false]);
        }

        return redirect()->route('admin.choices.index')->with('status', 'Choice updated successfully.');
    }

    public function destroy(Choice $choice): RedirectResponse
    {
        $isOnlyCorrectAnswer = $choice->is_correct
            && Choice::where('question_id', $choice->question_id)
                ->where('is_correct', true)
                ->where('id', '!=', $choice->id)
                ->count() === 0;

        if ($isOnlyCorrectAnswer) {
            return back()->withErrors([
                'choice' => 'Assign another correct choice before deleting this one.',
            ]);
        }

        $choice->delete();

        return redirect()->route('admin.choices.index')->with('status', 'Choice deleted successfully.');
    }
}
