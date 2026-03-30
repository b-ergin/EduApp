<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GradeController extends Controller
{
    public function index(): View
    {
        return view('admin.grades.index', [
            'grades' => Grade::withCount('subjects')->orderBy('name')->paginate(12),
        ]);
    }

    public function create(): View
    {
        return view('admin.grades.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:grades,name'],
        ]);

        Grade::create($validated);

        return redirect()->route('admin.grades.index')->with('status', 'Grade created successfully.');
    }

    public function edit(Grade $grade): View
    {
        return view('admin.grades.edit', ['grade' => $grade]);
    }

    public function update(Request $request, Grade $grade): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:grades,name,'.$grade->id],
        ]);

        $grade->update($validated);

        return redirect()->route('admin.grades.index')->with('status', 'Grade updated successfully.');
    }

    public function destroy(Grade $grade): RedirectResponse
    {
        $grade->delete();

        return redirect()->route('admin.grades.index')->with('status', 'Grade deleted. Related items were removed by cascade rules.');
    }
}
