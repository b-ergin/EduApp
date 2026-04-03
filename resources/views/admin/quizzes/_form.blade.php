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

<label for="xp_weight">XP Weight (1-10)
    <input id="xp_weight" name="xp_weight" type="number" min="1" max="10"
           value="{{ old('xp_weight', $quiz->xp_weight ?? 3) }}" required>
</label>

<label style="display:flex; align-items:center; gap:8px; margin-top:4px;">
    <input id="is_challenge" name="is_challenge" type="checkbox" value="1"
           @checked((bool) old('is_challenge', $quiz->is_challenge ?? false))>
    <span>Challenge Node (requires stars from previous quizzes)</span>
</label>

<div id="challenge_rules_box" style="border:1px dashed var(--border); border-radius:10px; padding:10px; margin-top:8px;">
    <p class="muted" style="margin-top:0;">
        Challenge rule: user must collect enough stars across the last N quizzes in this level path.
    </p>

    <label for="challenge_window_size">Previous Quizzes To Check (N)
        <input id="challenge_window_size" name="challenge_window_size" type="number" min="1" max="50"
               value="{{ old('challenge_window_size', $quiz->challenge_window_size ?? 3) }}">
    </label>

    <label for="challenge_min_stars">Minimum Total Stars Required
        <input id="challenge_min_stars" name="challenge_min_stars" type="number" min="1" max="150"
               value="{{ old('challenge_min_stars', $quiz->challenge_min_stars ?? 5) }}">
    </label>
</div>

<button class="btn" type="submit">{{ $buttonLabel }}</button>

<script>
    (function () {
        const challengeCheckbox = document.getElementById('is_challenge');
        const rulesBox = document.getElementById('challenge_rules_box');
        if (!challengeCheckbox || !rulesBox) return;

        const sync = () => {
            rulesBox.style.display = challengeCheckbox.checked ? 'block' : 'none';
        };

        challengeCheckbox.addEventListener('change', sync);
        sync();
    })();
</script>
