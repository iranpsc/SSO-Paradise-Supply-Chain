@props(['label', 'for', 'name', 'value' => null, 'required' => false, 'disabled' => false])

<div class="row mb-3">
    <label for="{{ $for }}" class="col-md-4 col-form-label text-md-end">{{ __($label) }}</label>
    <div class="col-md-6">
        <input id="{{ $for }}" type="file" 
            class="bg-[#FCFCFC] dark:bg-[#000000] dark:text-[#FFFFFF] w-full border-2 rounded-xl py-[10px] px-3 text-[#868B90] font-normal focus:ring-0 transition-all duration-200 @error($name) border-red-500 dark:border-red-500 shadow-lg shadow-red-500/20 focus:border-red-500 dark:focus:border-red-500 @else border-[#DEDEE9] dark:border-[#1A1A18] focus:border-primery-blue dark:focus:border-dark-yellow @enderror"
            name="{{ $name }}" value="{{ old($name, $value) }}" @if ($required) required @endif
            @if ($disabled) disabled @endif>
        @error($name)
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
