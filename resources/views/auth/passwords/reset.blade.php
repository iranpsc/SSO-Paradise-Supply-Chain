<x-layouts.app>
    <div class="space-y-10">
        <div class="w-full xl:w-1/2 2xl:w-[40%] mx-auto">
            <div class="bg-white dark:bg-[#0F0F0E] rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 md:p-8">
                <form method="POST" action="{{ route('password.update') }}">
                    <div class="flex flex-col gap-7">
                        <div class="text-2xl mb-5 dark:text-white">{{ __('Reset Password') }}</div>
                        @csrf

                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="flex flex-col gap-3 hidden">
                            <label for="email" class="my-2 mb-5 dark:text-white">{{ __('Email Address') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email"
                                    class="bg-[#FCFCFC] dark:bg-[#000000] border-[#DEDEE9] dark:border-[#1A1A18] dark:text-[#FFFFFF] placeholder:text-[#868B90] w-full border-2 rounded-xl py-[10px] focus:border-[#84858F] focus:ring-0 text-[#868B90] font-normal focus:text-[#1A1A18] focus:border-[1px] @error('email') is-invalid @enderror"
                                    name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus>

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <x-form.text :label="__('Password')" for="password" name="password" type="password" required
                            autocomplete="new-password" />

                        <x-form.text :label="__('Confirm Password')" for="password_confirmation" name="password_confirmation" type="password"
                            required />

                        <div>
                            <x-form.button id="reset-password-button" spinner-id="reset-password-spinner" text-id="reset-password-text">
                                {{ __('Reset Password') }}
                            </x-form.button>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Loading state for password reset form
            const resetPasswordForm = document.querySelector('form[action="{{ route('password.update') }}"]');
            const resetPasswordButton = document.getElementById('reset-password-button');
            const resetPasswordSpinner = document.getElementById('reset-password-spinner');
            const resetPasswordText = document.getElementById('reset-password-text');

            if (resetPasswordForm && resetPasswordButton) {
                resetPasswordForm.addEventListener('submit', function() {
                    resetPasswordButton.disabled = true;
                    if (resetPasswordSpinner) resetPasswordSpinner.classList.remove('hidden');
                    if (resetPasswordText) resetPasswordText.textContent = '{{ __('Loading...') }}';
                });
            }
        });
    </script>
@endpush
