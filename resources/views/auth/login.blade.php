<x-layouts.app>
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Organization",
      "name": "تونل زمان",
      "url": "https://accounts.irpsc.com/login",
      "logo": "https://accounts.irpsc.com/images/logo/accounts.png",
      "description": "سامانه مدیریت حساب کاربری IRPSC، ورود امن و سریع به تمامی سرویس‌ها و خدمات آنلاین ما را فراهم می‌کند. با استفاده از این پلتفرم، کاربران می‌توانند به سادگی حساب‌های کاربری خود را مدیریت کرده و با یک بار ورود، به تمامی خدمات متصل دسترسی داشته باشند.",
      "contactPoint": {
        "@type": "ContactPoint",
        "telephone": "+98-28-33696489",
        "contactType": "Customer Service",
        "availableLanguage": "Persian"
      },
      "email": "Cq@irpsc.com",

      {{-- "foundingDate": "2020-01-01",
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "خیابان مثال، پلاک 1",
        "addressLocality": "شهر مثال",
        "postalCode": "12345",
        "addressCountry": "IR"
      }, --}}
      "potentialAction": {
        "@type": "LoginAction",
        "target": "https://accounts.irpsc.com/login",
        "query-input": "required name=username"
      }
    }
    </script>
    <div class="space-y-10">

        <div class="text-center">
            <p class="text-xs md:text-xl font-normal dark:text-[#FFFFFF]">برای ورود ابتدا ایمیل / نام کاربری و رمزی که
                با ان ثبت نام کردید را وارد کنید </p>
        </div>
        <form method="POST" action="{{ route('login') }}" id="login-form">
            <div class="flex flex-col gap-7 w-full xl:w-1/2 2xl:w-[40%] mx-auto">
                @csrf
                <x-form.text for="email" name="email" type="email" required autofocus />
                <div class="w-full relative">
                    <x-form.text for="password" name="password" type="password" required
                        autocomplete="current-password" />

                    <a href="javascript:void(0)" id="toggle-password"
                        class=" absolute left-[10px] top-[50%] translate-y-[-50%] cursor-pointer">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path class="stroke-[#868B90]"
                                d="M12.9833 9.99896C12.9833 11.649 11.6499 12.9823 9.99993 12.9823C8.34993 12.9823 7.0166 11.649 7.0166 9.99896C7.0166 8.34896 8.34993 7.01562 9.99993 7.01562C11.6499 7.01562 12.9833 8.34896 12.9833 9.99896Z"
                                stroke="#868B90" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            <path class="stroke-[#868B90]"
                                d="M9.99987 16.8932C12.9415 16.8932 15.6832 15.1599 17.5915 12.1599C18.3415 10.9849 18.3415 9.0099 17.5915 7.8349C15.6832 4.8349 12.9415 3.10156 9.99987 3.10156C7.0582 3.10156 4.31654 4.8349 2.4082 7.8349C1.6582 9.0099 1.6582 10.9849 2.4082 12.1599C4.31654 15.1599 7.0582 16.8932 9.99987 16.8932Z"
                                stroke="#868B90" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </a>
                </div>
                <div class="flex flex-col  gap-2 px-1">
                    <x-form.checkbox :label="__('Remember Me')" for="remember" name="remember" id="remember"
                        :checked="old('remember')" />
                    @if (Route::has('password.request'))
                        <a class="text-xs text-primery-blue dark:text-dark-yellow "
                            href="{{ route('password.request') }}">
                            {{ __('Forgot Your Password?') }}
                        </a>
                    @endif
                </div>
                @if(config('recaptcha.enabled'))
                    <div class="flex justify-center">
                        <div class="cf-turnstile"
                             data-sitekey="{{ config('recaptcha.site_key') }}"
                             data-callback="onTurnstileSuccess"
                             data-expired-callback="onTurnstileExpired">
                        </div>
                    </div>
                @endif

                <input type="hidden" name="cf-turnstile-response" id="cf-turnstile-response">

                <div class="flex items-center justify-center">
                    <button type="submit" id="login-button"
                        class="text-white bg-primery-blue dark:bg-dark-yellow py-[14px] px-[40px] mx-auto rounded-xl w-full md:w-[179px] disabled:opacity-50 disabled:cursor-not-allowed"
                        @if(config('recaptcha.enabled')) disabled @endif>
                        {{ __('Login') }}
                    </button>
                </div>

            </div>
        </form>
        <script>
            const passwordInput = document.getElementById('password');
            const togglePasswordButton = document.getElementById('toggle-password');

            togglePasswordButton.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
            });

            // reCAPTCHA verification state
            let recaptchaVerified = {{ config('recaptcha.enabled') ? 'false' : 'true' }};

            // reCAPTCHA functions
            function onTurnstileSuccess(token) {
                document.getElementById('cf-turnstile-response').value = token;
                recaptchaVerified = true;
                // Enable login button after successful reCAPTCHA verification
                const loginButton = document.getElementById('login-button');
                if (loginButton) {
                    loginButton.disabled = false;
                    loginButton.classList.remove('disabled:opacity-50', 'disabled:cursor-not-allowed');
                }
            }

            function onTurnstileExpired() {
                document.getElementById('cf-turnstile-response').value = '';
                recaptchaVerified = false;
                // Disable login button when reCAPTCHA expires
                const loginButton = document.getElementById('login-button');
                if (loginButton) {
                    loginButton.disabled = true;
                    loginButton.classList.add('disabled:opacity-50', 'disabled:cursor-not-allowed');
                }
                if (typeof turnstile !== 'undefined') {
                    turnstile.reset();
                }
            }

            // Prevent form submission if reCAPTCHA is not verified
            document.getElementById('login-form').addEventListener('submit', function(e) {
                @if(config('recaptcha.enabled'))
                    if (!recaptchaVerified) {
                        e.preventDefault();
                        alert('{{ __('Please complete the reCAPTCHA verification.') }}');
                        return false;
                    }

                    const token = document.getElementById('cf-turnstile-response').value;
                    if (!token || token.trim() === '') {
                        e.preventDefault();
                        alert('{{ __('Please complete the reCAPTCHA verification.') }}');
                        return false;
                    }
                @endif
            });

            // Reset on form errors
            @if($errors->any())
                recaptchaVerified = false;
                if (typeof turnstile !== 'undefined') {
                    turnstile.reset();
                }
                // Disable login button on form errors
                const loginButton = document.getElementById('login-button');
                if (loginButton) {
                    loginButton.disabled = true;
                    loginButton.classList.add('disabled:opacity-50', 'disabled:cursor-not-allowed');
                }
            @endif
        </script>
    </div>
</x-layouts.app>
