<x-layouts.app>
    <div class="space-y-5 lg:space-y-8">
        <div class="text-center">
            <p class="text-xs md:text-sm lg:text-base font-normal dark:text-[#FFFFFF]">برای ورود ابتدا ایمیل / نام کاربری
                و رمزی که
                با ان ثبت نام کردید را وارد کنید </p>
        </div>
        <div class="w-full xl:w-1/2 2xl:w-[40%] mx-auto">
            <div
                class="bg-white dark:bg-[#0F0F0E] rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 md:p-8">
                <form method="POST" action="{{ route('login') }}" id="login-form">
                    <div class="flex flex-col gap-5">
                        @csrf
                        <x-form.text for="email" name="email" type="email" required autofocus />
                        <div class="w-full relative">
                            <x-form.text for="password" id="password" name="password" type="password" required
                                autocomplete="current-password" />

                            <a href="javascript:void(0)" id="toggle-password"
                                class="absolute left-[10px] top-1/2 -translate-y-1/2 cursor-pointer">

                                <!-- eye open -->
                                <svg id="eye-open" width="20" height="20" viewBox="0 0 20 20" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M12.9833 9.999C12.9833 11.649 11.6499 12.9823 9.99993 12.9823C8.34993 12.9823 7.0166 11.649 7.0166 9.999C7.0166 8.349 8.34993 7.0156 9.99993 7.0156C11.6499 7.0156 12.9833 8.349 12.9833 9.999Z"
                                        stroke="#868B90" stroke-width="1.5" />
                                    <path
                                        d="M9.99987 16.8932C12.9415 16.8932 15.6832 15.1599 17.5915 12.1599C18.3415 10.9849 18.3415 9.0099 17.5915 7.8349C15.6832 4.8349 12.9415 3.1016 9.99987 3.1016C7.0582 3.1016 4.31654 4.8349 2.4082 7.8349C1.6582 9.0099 1.6582 10.9849 2.4082 12.1599C4.31654 15.1599 7.0582 16.8932 9.99987 16.8932Z"
                                        stroke="#868B90" stroke-width="1.5" />
                                </svg>

                                <!-- eye closed -->
                               <svg id="eye-closed" class="hidden" width="20" height="20" viewBox="0 0 20 20" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M3 3L17 17" stroke="#868B90" stroke-width="1.5" stroke-linecap="round" />
                                    <path
                                        d="M12.9833 9.999C12.9833 11.649 11.6499 12.9823 9.99993 12.9823C8.34993 12.9823 7.0166 11.649 7.0166 9.999C7.0166 8.349 8.34993 7.0156 9.99993 7.0156C11.6499 7.0156 12.9833 8.349 12.9833 9.999Z"
                                        stroke="#868B90" stroke-width="1.5" />
                                    <path
                                        d="M9.99987 16.8932C12.9415 16.8932 15.6832 15.1599 17.5915 12.1599C18.3415 10.9849 18.3415 9.0099 17.5915 7.8349C15.6832 4.8349 12.9415 3.1016 9.99987 3.1016C7.0582 3.1016 4.31654 4.8349 2.4082 7.8349C1.6582 9.0099 1.6582 10.9849 2.4082 12.1599C4.31654 15.1599 7.0582 16.8932 9.99987 16.8932Z"
                                        stroke="#868B90" stroke-width="1.5" />
                                </svg>

                            </a>

                        </div>
                        <div class="flex flex-col  px-1">
                            <x-form.checkbox :label="__('Remember Me')" for="remember" name="remember" id="remember"
                                :checked="old('remember')" />
                            @if (Route::has('password.request'))
                                <a class="text-xs text-primery-blue dark:text-dark-yellow "
                                    href="{{ route('password.request') }}">
                                    {{ __('Forgot Your Password?') }}
                                </a>
                            @endif
                        </div>
                        <div>
                            <x-form.button id="login-button" spinner-id="login-spinner" text-id="login-text" full-width>
                                {{ __('Login') }}
                            </x-form.button>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>
    @push('scripts')
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
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const toggleBtn = document.getElementById('toggle-password');
                const eyeOpen = document.getElementById('eye-open');
                const eyeClosed = document.getElementById('eye-closed');

                if (!toggleBtn) return;

                toggleBtn.addEventListener('click', function(e) {
                    e.preventDefault();

                    const wrapper = toggleBtn.closest('.relative');
                    const passwordInput = wrapper.querySelector('input');

                    if (!passwordInput) return;

                    const isPassword = passwordInput.type === 'password';

                    passwordInput.type = isPassword ? 'text' : 'password';

                    // تغییر آیکن
                    eyeOpen.classList.toggle('hidden', isPassword);
                    eyeClosed.classList.toggle('hidden', !isPassword);
                });
            });
        </script>
    @endpush
</x-layouts.app>
