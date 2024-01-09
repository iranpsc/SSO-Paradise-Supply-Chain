@props(['label', 'for', 'name', 'checked' => false, 'required' => false, 'disabled' => false])

<div class="row mb-3">
    <div class="col-md-6 offset-md-4">
        <div class="form-check">
            <input id="{{ $for }}" type="checkbox" class="form-check-input @error($name) is-invalid @enderror" name="{{ $name }}" @checked($checked) @if($required) required @endif @if($disabled) disabled @endif>
            <label class="form-check-label" for="{{ $for }}">{{ __($label) }}</label>
        </div>
        @error($name)
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>