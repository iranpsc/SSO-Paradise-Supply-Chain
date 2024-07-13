@extends('layouts.app')

@section('content')
    <div class="space-y-10">

        <div class="text-center">
            <p class="text-xs md:text-xl font-normal dark:text-[#FFFFFF]">برای ورود ابتدا ایمیل / نام کاربری و رمزی که
                با ان ثبت نام کردید را وارد کنید </p>
        </div>
        <form method="POST" action="{{ route('login') }}">
            <div class="flex flex-col gap-7 w-full xl:w-1/2 2xl:w-[40%] mx-auto">
                @csrf

                <x-form.text for="email" name="email" type="email" required autofocus />

                <x-form.text for="password" name="password" type="password" required autocomplete="current-password" />
                <div class="flex items-center gap-2 px-1">
                    <x-form.checkbox :label="__('Remember Me')" for="remember" name="remember" id="remember" :checked="old('remember')" />

                </div>
                <div>

                    @if (Route::has('password.request'))
                        <a class="text-xs text-primery-blue dark:text-dark-yellow"
                            href="{{ route('password.request') }}">
                            {{ __('Forgot Your Password?') }}
                        </a>
                    @endif
                </div>

                <div class="flex items-center justify-center">
                    <button type="submit"
                        class="text-white bg-primery-blue dark:bg-dark-yellow py-[14px] px-[40px] mx-auto rounded-xl w-full md:w-[179px]">
                        {{ __('Login') }}
                    </button>
                </div>
            </div>
        </form>
        <script>
            const passwordInput = document.getElementById('password-input');
            const togglePasswordButton = document.getElementById('toggle-password');

            togglePasswordButton.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);

            });
        </script>


    </div>
@endsection
