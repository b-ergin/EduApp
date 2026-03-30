<label for="question_id">Question
    <select id="question_id" name="question_id" required>
        <option value="">Select question</option>
        @foreach ($questions as $questionItem)
            <option value="{{ $questionItem->id }}" @selected((int) old('question_id', $choice->question_id ?? 0) === $questionItem->id)>
                #{{ $questionItem->id }} - {{ \Illuminate\Support\Str::limit($questionItem->question_text, 60) }}
            </option>
        @endforeach
    </select>
</label>

<label for="choice_text">Choice Text
    <input id="choice_text" name="choice_text" type="text" value="{{ old('choice_text', $choice->choice_text ?? '') }}" required>
</label>

<label style="display:flex; align-items:center; gap:8px;">
    <input type="checkbox" name="is_correct" value="1" style="width:auto; margin:0;" @checked(old('is_correct', $choice->is_correct ?? false))>
    Mark as correct answer (other correct choices for the same question will be unset)
</label>

<button class="btn" type="submit">{{ $buttonLabel }}</button>
