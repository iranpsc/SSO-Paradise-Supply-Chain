@props(['label', 'for', 'name', 'checked' => false, 'required' => false, 'disabled' => false])

<div class="row mb-3">
    <div class="col-md-6 offset-md-4">
        <div class="flex gap-2 items-center">
            <input id="{{ $for }}" type="checkbox" class="shrink-0 mt-0.5 border-gray-200 rounded text-primery-blue  focus:ring-primery-blue disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:checked:bg-dark-yellow dark:checked:border-dark-yellow dark:ring-dark-yellow dark:focus:ring-offset-gray-800 @error($name) is-invalid @enderror" name="{{ $name }}" @checked($checked) @if($required) required @endif @if($disabled) disabled @endif>
            <label class="text-primery-blue dark:text-dark-yellow" for="{{ $for }}">{{ __($label) }}</label>
        </div>
        @error($name)
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>