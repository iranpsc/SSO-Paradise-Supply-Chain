<x-layouts.app>
    <div class="space-y-10">
        <form method="POST" action="{{ route('password.update') }}">
            <div class="flex flex-col gap-7 w-full xl:w-1/2 2xl:w-[40%] mx-auto">
                <div class="text-2xl my-5">{{ __('Reset Password') }}</div>
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">

                <div class="row mb-3">
                    <label for="email" class="my-2">{{ __('Email Address') }}</label>

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

                <div class="row mb-0">
                    <div class="col-md-6 offset-md-4">
                        <button type="submit" class="btn btn-primary">
                            {{ __('Reset Password') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-layouts.app>
