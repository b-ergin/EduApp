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
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'question_text' => ['required', 'string'],
            'quiz_id' => ['required', 'integer', 'exists:quizzes,id'],
            'image_path' => ['nullable', 'string', 'max:255'],
        ]);

        Question::create($validated);

        return redirect()->route('admin.questions.index')->with('status', 'Question created successfully.');
    }

    public function edit(Question $question): View
    {
        return view('admin.questions.edit', [
            'question' => $question,
            'quizzes' => Quiz::with('subject.grade')->orderBy('title')->get(),
        ]);
    }

    public function update(Request $request, Question $question): RedirectResponse
    {
        $validated = $request->validate([
            'question_text' => ['required', 'string'],
            'quiz_id' => ['required', 'integer', 'exists:quizzes,id'],
            'image_path' => ['nullable', 'string', 'max:255'],
        ]);

        $question->update($validated);

        return redirect()->route('admin.questions.index')->with('status', 'Question updated successfully.');
    }

    public function destroy(Question $question): RedirectResponse
    {
        $question->delete();

        return redirect()->route('admin.questions.index')->with('status', 'Question deleted. Related choices were removed by cascade rules.');
    }
}
