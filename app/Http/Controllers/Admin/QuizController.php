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
        $quizzes = Quiz::with('subject.grade')
            ->withCount('questions')
            ->orderByRaw('COALESCE(sort_order, id)')
            ->orderBy('id')
            ->get();

        $groupedQuizzes = $quizzes
            ->groupBy(fn (Quiz $quiz) => (int) ($quiz->subject?->grade?->id ?? 0))
            ->map(function ($items, $gradeId) {
                /** @var \App\Models\Quiz $first */
                $first = $items->first();
                return [
                    'grade_id' => (int) $gradeId,
                    'grade_name' => $first->subject?->grade?->name ?? 'Unassigned Grade',
                    'quizzes' => $items->values(),
                ];
            })
            ->sortBy('grade_name')
            ->values();

        return view('admin.quizzes.index', [
            'groupedQuizzes' => $groupedQuizzes,
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
            'is_challenge' => ['nullable', 'boolean'],
            'challenge_window_size' => ['nullable', 'integer', 'min:1', 'max:50', 'required_if:is_challenge,1'],
            'challenge_min_stars' => ['nullable', 'integer', 'min:1', 'max:150', 'required_if:is_challenge,1'],
            'xp_weight' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        $subject = Subject::findOrFail((int) $validated['subject_id']);
        $maxSortInGrade = Quiz::whereHas('subject', function ($query) use ($subject) {
            $query->where('grade_id', $subject->grade_id);
        })->max('sort_order');

        $validated['sort_order'] = ((int) $maxSortInGrade) + 1;
        $validated['is_challenge'] = $request->boolean('is_challenge');

        if (! $validated['is_challenge']) {
            $validated['challenge_window_size'] = null;
            $validated['challenge_min_stars'] = null;
        }

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
            'is_challenge' => ['nullable', 'boolean'],
            'challenge_window_size' => ['nullable', 'integer', 'min:1', 'max:50', 'required_if:is_challenge,1'],
            'challenge_min_stars' => ['nullable', 'integer', 'min:1', 'max:150', 'required_if:is_challenge,1'],
            'xp_weight' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        $validated['is_challenge'] = $request->boolean('is_challenge');

        if (! $validated['is_challenge']) {
            $validated['challenge_window_size'] = null;
            $validated['challenge_min_stars'] = null;
        }

        $quiz->update($validated);

        return redirect()->route('admin.quizzes.index')->with('status', 'Quiz updated successfully.');
    }

    public function destroy(Quiz $quiz): RedirectResponse
    {
        $quiz->delete();

        return redirect()->route('admin.quizzes.index')->with('status', 'Quiz deleted. Related questions and choices were removed by cascade rules.');
    }

    public function reorder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'grade_id' => ['required', 'integer'],
            'order' => ['required', 'array', 'min:1'],
            'order.*' => ['integer', 'exists:quizzes,id'],
        ]);

        $gradeId = (int) $validated['grade_id'];
        $quizIds = array_map('intval', $validated['order']);
        $countInGrade = Quiz::whereIn('id', $quizIds)
            ->whereHas('subject', function ($query) use ($gradeId) {
                $query->where('grade_id', $gradeId);
            })
            ->count();

        if ($countInGrade !== count($quizIds)) {
            return redirect()->route('admin.quizzes.index')->withErrors([
                'order' => 'Invalid reorder payload for selected grade.',
            ]);
        }

        foreach (array_values($validated['order']) as $index => $quizId) {
            Quiz::where('id', (int) $quizId)->update(['sort_order' => $index + 1]);
        }

        return redirect()->route('admin.quizzes.index')->with('status', 'Quiz order updated for this grade.');
    }
}
