@props(['label', 'for', 'name', 'value' => null, 'type' => 'text', 'required' => false, 'disabled' => false])

<div class="flex flex-col gap-10">
    <div class="flex flex-col gap-3">
        <input id="{{ $for }}" type="{{ $type }}"
            placeholder="{{ trans('validation.attributes.' . $name) }}"
            class="bg-[#FCFCFC]  dark:bg-[#000000] border-[#DEDEE9] dark:border-[#1A1A18] dark:text-[#FFFFFF] placeholder:text-[#868B90] w-full border-2 rounded-xl py-[10px]  focus:ring-0 focus:border-primery-blue dark:focus:border-dark-yellow text-[#868B90] font-normal focus:text-[#1A1A18]  text-sm lg:text-base  @error($name) is-invalid @enderror"
            name="{{ $name }}" value="{{ old($name, $value) }}" @if ($required) required @endif
            @if ($disabled) disabled @endif>
        @error($name)
            <div class="invalid-feedback">
                <div>
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M7.00033 12.8307C10.2087 12.8307 12.8337 10.2057 12.8337 6.9974C12.8337 3.78906 10.2087 1.16406 7.00033 1.16406C3.79199 1.16406 1.16699 3.78906 1.16699 6.9974C1.16699 10.2057 3.79199 12.8307 7.00033 12.8307Z"
                            stroke="#EB5757" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M7 4.66406V7.58073" stroke="#EB5757" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M6.99707 9.33594H7.00231" stroke="#EB5757" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                </div>
                {{ $message }}
            </div>
        @enderror
    </div>
</div>
