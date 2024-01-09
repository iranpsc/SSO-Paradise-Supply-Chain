@props(['options', 'name', 'label', 'selected' => null])

<div class="row mb-3">
    <label for="{{ $name }}" class="col-md-4 col-form-label text-md-end">{{ __($label) }}</label>
    <div class="col-md-6">
        <select id="{{ $name }}" class="form-select @error($name) is-invalid @enderror" name="{{ $name }}">
            @foreach($options as $option)
                <option value="{{ $option->id }}" @if($selected == $option->id) selected @endif>{{ $option->name }}</option>
            @endforeach
        </select>
        @error($name)
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>