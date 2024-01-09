@props(['label', 'for', 'name', 'value' => null, 'type' => 'text', 'required' => false, 'disabled' => false])

<div class="row mb-3">
    <label for="{{ $for }}" class="col-md-4 col-form-label text-md-end">{{ __($label) }}</label>
    <div class="col-md-6">
        <input id="{{ $for }}" type="{{ $type }}" class="form-control @error($name) is-invalid @enderror" name="{{ $name }}" value="{{ old($name, $value) }}" @if($required) required @endif @if($disabled) disabled @endif>
        @error($name)
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>