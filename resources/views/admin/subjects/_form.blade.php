<label for="name">Subject Name
    <input id="name" name="name" type="text" value="{{ old('name', $subject->name ?? '') }}" required>
</label>

<label for="grade_id">Grade
    <select id="grade_id" name="grade_id" required>
        <option value="">Select grade</option>
        @foreach ($grades as $grade)
            <option value="{{ $grade->id }}" @selected((int) old('grade_id', $subject->grade_id ?? 0) === $grade->id)>
                {{ $grade->name }}
            </option>
        @endforeach
    </select>
</label>

<button class="btn" type="submit">{{ $buttonLabel }}</button>
