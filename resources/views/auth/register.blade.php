<x-layouts.app>
    <div class="space-y-5 lg:space-y-8">
        <div class="text-center">
            <p class="text-xs md:text-sm lg:text-base font-normal dark:text-[#FFFFFF]">برای ثبت نام ایمیل / نام کاربری و رمز مورد نظر
                خودرا وارد کنید. </p>
        </div>
        <div class="flex flex-col gap-10">
            <div class="w-full xl:w-1/2 2xl:w-[40%] mx-auto">
                <div class="bg-white dark:bg-[#0F0F0E] rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 md:p-8">
                    <form method="POST" action="{{ route('register') }}" id="register-form">
                        <div class="flex flex-col gap-7">
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

                            <div>
                                <x-form.button id="register-button" spinner-id="register-spinner" text-id="register-text" full-width>
                                    {{ __('Register') }}
                                </x-form.button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

@push('scripts')
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Loading state for register form
            const registerForm = document.getElementById('register-form');
            const registerButton = document.getElementById('register-button');
            const registerSpinner = document.getElementById('register-spinner');
            const registerText = document.getElementById('register-text');

            if (registerForm && registerButton) {
                registerForm.addEventListener('submit', function() {
                    registerButton.disabled = true;
                    if (registerSpinner) registerSpinner.classList.remove('hidden');
                    if (registerText) registerText.textContent = '{{ __('Loading...') }}';
                });
            }
        });
    </script>
@endpush
