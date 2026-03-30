<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuizController extends Controller
{
    public function index(): View
    {
        return view('admin.quizzes.index', [
            'quizzes' => Quiz::with('subject.grade')->withCount('questions')->orderBy('title')->paginate(12),
        ]);
    }

    public function create(): View
    {
        return view('admin.quizzes.create', [
            'subjects' => Subject::with('grade')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
        ]);

        Quiz::create($validated);

        return redirect()->route('admin.quizzes.index')->with('status', 'Quiz created successfully.');
    }

    public function edit(Quiz $quiz): View
    {
        return view('admin.quizzes.edit', [
            'quiz' => $quiz,
            'subjects' => Subject::with('grade')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Quiz $quiz): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
        ]);

        $quiz->update($validated);

        return redirect()->route('admin.quizzes.index')->with('status', 'Quiz updated successfully.');
    }

    public function destroy(Quiz $quiz): RedirectResponse
    {
        $quiz->delete();

        return redirect()->route('admin.quizzes.index')->with('status', 'Quiz deleted. Related questions and choices were removed by cascade rules.');
    }
}
