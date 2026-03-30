<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function index(): View
    {
        return view('admin.subjects.index', [
            'subjects' => Subject::with('grade')->withCount('quizzes')->orderBy('name')->paginate(12),
        ]);
    }

    public function create(): View
    {
        return view('admin.subjects.create', [
            'grades' => Grade::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'grade_id' => ['required', 'integer', 'exists:grades,id'],
        ]);

        Subject::create($validated);

        return redirect()->route('admin.subjects.index')->with('status', 'Subject created successfully.');
    }

    public function edit(Subject $subject): View
    {
        return view('admin.subjects.edit', [
            'subject' => $subject,
            'grades' => Grade::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Subject $subject): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'grade_id' => ['required', 'integer', 'exists:grades,id'],
        ]);

        $subject->update($validated);

        return redirect()->route('admin.subjects.index')->with('status', 'Subject updated successfully.');
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        $subject->delete();

        return redirect()->route('admin.subjects.index')->with('status', 'Subject deleted. Related quizzes and questions were removed by cascade rules.');
    }
}
