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

                            <input type="hidden" name="client_id" value="{{ old('client_id', request('client_id')) }}">
                            <input type="hidden" name="redirect_uri" value="{{ old('redirect_uri', request('redirect_uri')) }}">
                            <input type="hidden" name="referral" value="{{ old('referral', request('referral')) }}">
                            <input type="hidden" name="back_url" value="{{ old('back_url', request('back_url')) }}">

                            @if ($errors->hasAny(['client_id', 'redirect_uri', 'referral', 'back_url']))
                                <div class="rounded-xl border border-red-500/40 bg-red-500/10 px-4 py-3 text-sm text-[#EB5757] space-y-1" role="alert">
                                    @foreach (['client_id', 'redirect_uri', 'referral', 'back_url'] as $field)
                                        @error($field)
                                            <p>{{ $message }}</p>
                                        @enderror
                                    @endforeach
                                </div>
                            @endif

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
                    <div class="flex justify-center mt-6 gap-2">
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
                        <button type="button" id="connectWallet-btn" onclick="connectWalletBtn()"
                            class="w-full bg-white dark:bg-slate-800 hover:bg-gray-100 dark:hover:bg-slate-700 border border-gray-200 dark:border-slate-700 rounded-lg px-4 py-3 flex items-center justify-center gap-2 transition focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg id="connectWallet-icon" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-yellow-500" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path d="M21 7v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h14a2 2 0 012 2z" />
                                <circle cx="12" cy="12" r="4" fill="#f59e42" />
                            </svg>
                            <svg id="connectWallet-spinner" class="hidden animate-spin h-5 w-5 text-gray-800 dark:text-gray-100" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span id="ConnectWalletTxt" class="text-sm font-medium text-gray-800 dark:text-gray-100">
                                {{ __('connectWallet Login') }}
                            </span>
                        </button>
                    </div>
                    <script>
                        const isMobile =
                            "ontouchstart" in window || navigator.maxTouchPoints > 0;

                        if (isMobile) {
                             document
                                 .getElementById("connect-wallet-btn")
                                 .classList.add("hidden");
                         }
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

                                if (verifyResponse.ok) {
                                    text.innerText = "ورود موفقیت‌آمیز...";
                                    window.location.href = '/home';
                                }

                            } catch (error) {
                                console.error("Web3 auth error:", error);

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
                      async function connectWalletBtn() {
                        alert("لطفا برای اتصال با connectWallert از نرم افزار های تغییر آی پی (VPN) استفاده کنید ")
                        const btn = document.getElementById('connectWallet-btn');
                        const icon = document.getElementById('connectWallet-icon');
                        const spinner = document.getElementById('connectWallet-spinner');
                        const text = document.getElementById('ConnectWalletTxt');

                        let uiWasReset = false;

                        const resetButton = () => {
                            uiWasReset = true;
                            btn.disabled = false;
                            icon.classList.remove('hidden');
                            spinner.classList.add('hidden');
                            text.innerText = "ورود با connectWallet";
                        };

                        const setConnecting = () => {
                            uiWasReset = false;
                            btn.disabled = true;
                            icon.classList.add('hidden');
                            spinner.classList.remove('hidden');
                            text.innerText = "در حال اتصال...";
                        };

                        setConnecting();

                        const uiTimeout = setTimeout(() => {
                            resetButton();
                        }, 5000);

                        try {
                            const {
                                address,
                                signature
                            } = await window.connectWalletConnect();

                            clearTimeout(uiTimeout);

                            if (uiWasReset && btn.disabled) {
                                return;
                            }

                            text.innerText = "در حال تایید...";

                            const verifyForm = document.getElementById("web3-verify-form");
                            verifyForm.querySelector('[name="address"]').value = address;
                            verifyForm.querySelector('[name="signature"]').value = signature;
                            verifyForm.submit();
                            console.log({
                                address,
                                signature
                            });
                        } catch (error) {
                            clearTimeout(uiTimeout);
                            console.error(error);
                            resetButton();
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
                    if (registerText) registerText.textContent = "در حال ثبت نام...";
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
            "potentialAction": {
                "@@type": "LoginAction",
                "target": "https://accounts.irpsc.com/login",
                "@@query-input": "required name=username"
            }
        }
    </script>
    @endpush
</x-layouts.app>
