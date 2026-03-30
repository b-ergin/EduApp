<label for="title">Quiz Title
    <input id="title" name="title" type="text" value="{{ old('title', $quiz->title ?? '') }}" required>
</label>

<label for="subject_id">Subject
    <select id="subject_id" name="subject_id" required>
        <option value="">Select subject</option>
        @foreach ($subjects as $subject)
            <option value="{{ $subject->id }}" @selected((int) old('subject_id', $quiz->subject_id ?? 0) === $subject->id)>
                {{ $subject->name }} @if($subject->grade) ({{ $subject->grade->name }}) @endif
            </option>
        @endforeach
    </select>
</label>

<button class="btn" type="submit">{{ $buttonLabel }}</button>
