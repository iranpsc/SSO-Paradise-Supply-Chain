@props(['options', 'name', 'label', 'selected' => null])

<div class="row mb-3">
    <label for="{{ $name }}" class="col-md-4 col-form-label text-md-end">{{ __($label) }}</label>
    <div class="col-md-6">
        <select id="{{ $name }}" placeholder="{{ $name }}"
            class="bg-[#FCFCFC] dark:bg-[#000000] dark:text-[#FFFFFF] placeholder:text-[#868B90] w-full border-2 rounded-xl py-[10px] text-[#868B90] font-normal focus:text-[#1A1A18] focus:ring-0 transition-all duration-200 @error($name) border-red-500 dark:border-red-500 shadow-lg shadow-red-500/20 focus:border-red-500 dark:focus:border-red-500 @else border-[#DEDEE9] dark:border-[#1A1A18] focus:border-[#84858F] @enderror"
            name="{{ $name }}">
            @foreach ($options as $option)
                <option value="{{ $option->id }}" @if ($selected == $option->id) selected @endif>{{ $option->name }}
                </option>
            @endforeach
        </select>
        @error($name)
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
