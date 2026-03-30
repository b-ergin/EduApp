<label for="name">Grade Name
    <input id="name" name="name" type="text" value="{{ old('name', $grade->name ?? '') }}" required>
</label>

<button class="btn" type="submit">{{ $buttonLabel }}</button>
