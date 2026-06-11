<x-layouts.app>
    <div class="space-y-5 lg:space-y-8">
        <div class="text-center">
            <p class="text-xs md:text-sm lg:text-base font-normal dark:text-[#FFFFFF]">برای ورود ابتدا ایمیل / نام کاربری
                و رمزی که
                با ان ثبت نام کردید را وارد کنید </p>
        </div>
        <div class="w-full xl:w-1/2 2xl:w-[40%] mx-auto">
            <div class=" rounded-xl p-6 md:p-8">
                <form method="POST" action="{{ route('login') }}" id="login-form">
                    <div class="flex flex-col gap-5">
                        @csrf
                        <x-form.text for="email" name="email" type="email" required autofocus />
                        <div class="w-full relative">
                            <x-form.text for="password" id="password" name="password" type="password" required
                                autocomplete="current-password" />

                            <button aria-label="show btn" href="javascript:void(0)" id="toggle-password"
                                class="absolute left-[10px] top-1/2 -translate-y-1/2 cursor-pointer">

                                <!-- eye open -->
                                <svg id="eye-open" class="w-7 h-7 " width="20" height="20" viewBox="0 0 20 20"
                                    fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M12.9833 9.999C12.9833 11.649 11.6499 12.9823 9.99993 12.9823C8.34993 12.9823 7.0166 11.649 7.0166 9.999C7.0166 8.349 8.34993 7.0156 9.99993 7.0156C11.6499 7.0156 12.9833 8.349 12.9833 9.999Z"
                                        stroke="#868B90" stroke-width="1.5" />
                                    <path
                                        d="M9.99987 16.8932C12.9415 16.8932 15.6832 15.1599 17.5915 12.1599C18.3415 10.9849 18.3415 9.0099 17.5915 7.8349C15.6832 4.8349 12.9415 3.1016 9.99987 3.1016C7.0582 3.1016 4.31654 4.8349 2.4082 7.8349C1.6582 9.0099 1.6582 10.9849 2.4082 12.1599C4.31654 15.1599 7.0582 16.8932 9.99987 16.8932Z"
                                        stroke="#868B90" stroke-width="1.5" />
                                </svg>

                                <!-- eye closed -->
                                <svg aria-label="show btn" id="eye-closed" class="hidden w-7 h-7 " width="20"
                                    height="20" viewBox="0 0 20 20" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M3 3L17 17" stroke="#868B90" stroke-width="1.5" stroke-linecap="round" />
                                    <path
                                        d="M12.9833 9.999C12.9833 11.649 11.6499 12.9823 9.99993 12.9823C8.34993 12.9823 7.0166 11.649 7.0166 9.999C7.0166 8.349 8.34993 7.0156 9.99993 7.0156C11.6499 7.0156 12.9833 8.349 12.9833 9.999Z"
                                        stroke="#868B90" stroke-width="1.5" />
                                    <path
                                        d="M9.99987 16.8932C12.9415 16.8932 15.6832 15.1599 17.5915 12.1599C18.3415 10.9849 18.3415 9.0099 17.5915 7.8349C15.6832 4.8349 12.9415 3.1016 9.99987 3.1016C7.0582 3.1016 4.31654 4.8349 2.4082 7.8349C1.6582 9.0099 1.6582 10.9849 2.4082 12.1599C4.31654 15.1599 7.0582 16.8932 9.99987 16.8932Z"
                                        stroke="#868B90" stroke-width="1.5" />
                                </svg>

                            </button>

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
                <div class="flex justify-center mt-6">
                    <button type="button" id="connect-wallet-btn" onclick="connectWallet()"
                        class="w-full bg-white dark:bg-slate-800 hover:bg-gray-100 dark:hover:bg-slate-700 border border-gray-200 dark:border-slate-700 rounded-lg px-4 py-3 flex items-center justify-center gap-2 transition focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg id="wallet-icon" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-yellow-500" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path d="M21 7v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h14a2 2 0 012 2z" />
                            <circle cx="12" cy="12" r="4" fill="#f59e42" />
                        </svg>
                        <svg id="wallet-spinner" class="hidden animate-spin h-5 w-5 text-gray-800 dark:text-gray-100" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span id="wallet-text" class="text-sm font-medium text-gray-800 dark:text-gray-100">
                            {{ __('Wallet Login') }}
                        </span>
                    </button>
                </div>
                <script>
                    async function connectWallet() {
                        const btn = document.getElementById('connect-wallet-btn');
                        const icon = document.getElementById('wallet-icon');
                        const spinner = document.getElementById('wallet-spinner');
                        const text = document.getElementById('wallet-text');

                        // 1. Check if MetaMask/Web3 wallet is installed
                        if (!window.ethereum) {
                            alert("کیف پول وب۳ پیدا نشد. لطفا متامسک یا یک کیف پول سازگار را نصب کنید.");
                            return;
                        }

                        // Set loading state
                        btn.disabled = true;
                        icon.classList.add('hidden');
                        spinner.classList.remove('hidden');
                        const originalText = text.innerText;
                        text.innerText = "در حال اتصال...";

                        try {
                            // 2. Request the user's wallet address
                            const accounts = await window.ethereum.request({
                                method: 'eth_requestAccounts'
                            });
                            const address = accounts[0];

                            // 3. Fetch the Nonce from your Laravel backend
                            const nonceResponse = await fetch(`/web3/nonce?address=${address}`);
                            if (!nonceResponse.ok) {
                                throw new Error("دریافت کد یکبار مصرف با خطا مواجه شد.");
                            }
                            const nonceData = await nonceResponse.json();

                            text.innerText = "در حال امضای پیام...";

                            // 4. Ask MetaMask to sign the nonce
                            // Properly convert the UTF-8 string to a hex-encoded format
                            const msg = nonceData.nonce;
                            const msgHex = '0x' + Array.from(new TextEncoder().encode(msg))
                                .map(byte => byte.toString(16).padStart(2, '0'))
                                .join('');

                            const signature = await window.ethereum.request({
                                method: 'personal_sign',
                                params: [msgHex, address],
                            });

                            text.innerText = "در حال تایید امضا...";

                            // 5. Send the signature back to Laravel for verification
                            const verifyResponse = await fetch('/web3/verify', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify({
                                    address: address,
                                    signature: signature
                                })
                            });

                            const verifyData = await verifyResponse.json();

                            if (verifyResponse.ok) {
                                text.innerText = "ورود موفقیت‌آمیز...";
                                window.location.href = verifyData.redirect || '/home';
                            } else {
                                throw new Error(verifyData.message || "تایید امضا ناموفق بود.");
                            }

                        } catch (error) {
                            console.error("Web3 auth error:", error);

                            // User rejected connection or sign request
                            let errorMsg = "خطایی رخ داد. لطفا دوباره تلاش کنید.";
                            if (error.code === 4001) {
                                errorMsg = "درخواست امضا یا اتصال توسط کاربر رد شد.";
                            } else if (error.message) {
                                errorMsg = error.message;
                            }
                            alert(errorMsg);

                            // Restore button state
                            btn.disabled = false;
                            icon.classList.remove('hidden');
                            spinner.classList.add('hidden');
                            text.innerText = originalText;
                        }
                    }
                </script>
            </div>
        </div>
    </div>
    @push('scripts')
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
        <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@@type": "Organization",
      "name": "تونل زمان",
      "url": "https://accounts.irpsc.com/login",
      "@@logo": "https://accounts.irpsc.com/images/logo/accounts.png",
      "description": "سامانه مدیریت حساب کاربری IRPSC، ورود امن و سریع به تمامی سرویس‌ها و خدمات آنلاین ما را فراهم می‌کند. با استفاده از این پلتفرم، کاربران می‌توانند به سادگی حساب‌های کاربری خود را مدیریت کرده و با یک بار ورود، به تمامی خدمات متصل دسترسی داشته باشند.",
      "contactPoint": {
        "@@type": "ContactPoint",
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
        "@@type": "LoginAction",
        "target": "https://accounts.irpsc.com/login",
        "@@query-input": "required name=username"
      }
    }
    </script>
    @endpush
</x-layouts.app>
