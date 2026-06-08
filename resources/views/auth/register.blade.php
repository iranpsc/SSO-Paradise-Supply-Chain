<x-layouts.app>
    <div class="space-y-5 lg:space-y-8">
        <div class="text-center">
            <p class="text-xs md:text-sm lg:text-base font-normal dark:text-[#FFFFFF]">برای ثبت نام ایمیل / نام کاربری و
                رمز مورد نظر
                خودرا وارد کنید. </p>
        </div>
        <div class="flex flex-col gap-10">
            <div class="w-full xl:w-1/2 2xl:w-[40%] mx-auto">
                <div class=" p-6 ">
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
                                <x-form.button id="register-button" spinner-id="register-spinner"
                                    text-id="register-text" full-width>
                                    {{ __('Register') }}
                                </x-form.button>
                            </div>
                        </div>
                    </form>
                    <div class="flex justify-center mt-6">
                        <button type="button" id="connect-wallet-btn" onclick="connectWallet()"
                            class="w-full bg-white dark:bg-slate-800 hover:bg-gray-100 dark:hover:bg-slate-700 border border-gray-200 dark:border-slate-700 rounded-lg px-4 py-3 flex items-center justify-center gap-2 transition focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg id="wallet-icon" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-yellow-500"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path d="M21 7v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h14a2 2 0 012 2z" />
                                <circle cx="12" cy="12" r="4" fill="#f59e42" />
                            </svg>
                            <svg id="wallet-spinner"
                                class="hidden animate-spin h-5 w-5 text-gray-800 dark:text-gray-100"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
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
                                    window.location.href = '/home';
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
    </div>
    @push('scripts')
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
                        if (registerText) registerText.textContent = '{{ __('درحال ثبت نام...') }}';
                    });
                }
            });
        </script>
        <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@@type": "Organization",
      "name": "تونل زمان",
      "url": "https://accounts.irpsc.com/register",
      "@@logo": "https://accounts.irpsc.com/images/logo/accounts.png",
      "description": "سامانه مدیریت حساب کاربری IRPSC، ثبت نام امن و سریع به تمامی سرویس‌ها و خدمات آنلاین ما را فراهم می‌کند. با استفاده از این پلتفرم، کاربران می‌توانند به سادگی حساب‌های کاربری خود را مدیریت کرده و با یک بار ورود، به تمامی خدمات متصل دسترسی داشته باشند.",
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
