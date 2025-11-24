<x-layouts.app>
    <div class="space-y-10">
        <div class="w-full xl:w-1/2 2xl:w-[40%] mx-auto">
            <div class="bg-white dark:bg-[#0F0F0E] rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 md:p-8">
                <div class="flex flex-col gap-7">
                    <div class="text-2xl mb-2 dark:text-white">{{ __('Confirm Password') }}</div>
                    <p class="text-sm dark:text-gray-300 mb-4">{{ __('Please confirm your password before continuing.') }}</p>

                    <form method="POST" action="{{ route('password.confirm') }}">
                        @csrf

                        <x-form.text :label="__('Password')" for="password" name="password" type="password" required autocomplete="current-password" />

                        <div class="flex flex-col gap-3">
                            <button type="submit" class="text-white bg-primery-blue dark:bg-dark-yellow py-[14px] px-[40px] mx-auto rounded-xl w-full md:w-max">
                                {{ __('Confirm Password') }}
                            </button>

                            @if (Route::has('password.request'))
                                <a class="text-xs text-primery-blue dark:text-dark-yellow text-center" href="{{ route('password.request') }}">
                                    {{ __('Forgot Your Password?') }}
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
