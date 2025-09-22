<x-layouts.app>
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Organization",
      "name": "تونل زمان",
      "url": "https://accounts.irpsc.com/register",
      "logo": "https://accounts.irpsc.com/images/logo/accounts.png",
      "description": "سامانه مدیریت حساب کاربری IRPSC، ثبت نام امن و سریع به تمامی سرویس‌ها و خدمات آنلاین ما را فراهم می‌کند. با استفاده از این پلتفرم، کاربران می‌توانند به سادگی حساب‌های کاربری خود را مدیریت کرده و با یک بار ورود، به تمامی خدمات متصل دسترسی داشته باشند.",
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
            <p class="text-xs md:text-xl font-normal dark:text-[#FFFFFF]">برای ثبت نام ایمیل / نام کاربری و رمز مورد نظر
                خودرا وارد کنید. </p>
        </div>
        <div class="flex flex-col gap-10">
            @if ($errors->any(['client_id', 'redirect_uri', 'refferal']))
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li class="mb-0">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                <div class="flex flex-col gap-7 w-full xl:w-1/2 2xl:w-[40%] mx-auto">
                    @csrf

                    <input type="hidden" name="client_id" value="{{ request()->query('client_id') }}">
                    <input type="hidden" name="redirect_uri" value="{{ request()->query('redirect_uri') }}">
                    <input type="hidden" name="referral" value="{{ request()->query('referral') }}">
                    <input type="hidden" name="back_url" value="{{ request()->query('back_url') }}">

                    <x-form.text :label="__('Name')" for="name" name="name" required autofocus />

                    <x-form.text :label="__('Email Address')" for="email" name="email" type="email" required />

                    <x-form.text :label="__('Password')" for="password" name="password" type="password" required
                        autocomplete="new-password" />

                    <x-form.text :label="__('Confirm Password')" for="password_confirmation" name="password_confirmation"
                        type="password" required />

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
                        <button type="submit" id="register-button"
                            class="text-white bg-primery-blue dark:bg-dark-yellow py-[14px] px-[40px] mx-auto rounded-xl w-full md:w-[179px] disabled:opacity-50 disabled:cursor-not-allowed"
                            @if(config('recaptcha.enabled')) disabled @endif>
                            {{ __('Register') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // reCAPTCHA functions
        function onTurnstileSuccess(token) {
            document.getElementById('cf-turnstile-response').value = token;
            // Enable register button after successful reCAPTCHA verification
            const registerButton = document.getElementById('register-button');
            if (registerButton) {
                registerButton.disabled = false;
                registerButton.classList.remove('disabled:opacity-50', 'disabled:cursor-not-allowed');
            }
        }

        function onTurnstileExpired() {
            document.getElementById('cf-turnstile-response').value = '';
            // Disable register button when reCAPTCHA expires
            const registerButton = document.getElementById('register-button');
            if (registerButton) {
                registerButton.disabled = true;
                registerButton.classList.add('disabled:opacity-50', 'disabled:cursor-not-allowed');
            }
            if (typeof turnstile !== 'undefined') {
                turnstile.reset();
            }
        }

        // Reset on form errors
        @if($errors->any())
            if (typeof turnstile !== 'undefined') {
                turnstile.reset();
            }
            // Disable register button on form errors
            const registerButton = document.getElementById('register-button');
            if (registerButton) {
                registerButton.disabled = true;
                registerButton.classList.add('disabled:opacity-50', 'disabled:cursor-not-allowed');
            }
        @endif
    </script>
</x-layouts.app>
