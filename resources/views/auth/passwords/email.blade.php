<x-layouts.app>
    @session('status')
        <div id="alert-modal"
            class="bg-black/10 backdrop-blur-md flex justify-center items-center z-[20000] h-screen w-screen fixed right-0 top-0 text-center">
            <div
                class="relative flex items-center justify-center bg-white dark:bg-[#0F0F0E] rounded-xl flex-col gap-5 p-5 text-center min-w-72 dark:text-white">

                <button onclick="document.getElementById('alert-modal').style.display = 'none';" class="w-max absolute right-5 top-5 cruiser-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                      </svg>
                </button>
                <div>
                    <div class="flex w-full justify-center">
                        <svg width="58" height="58" viewBox="0 0 58 58" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path class="dark:stroke-dark-yellow"
                                d="M41.084 49.5443H16.9173C9.66732 49.5443 4.83398 45.9193 4.83398 37.4609V20.5443C4.83398 12.0859 9.66732 8.46094 16.9173 8.46094H41.084C48.334 8.46094 53.1673 12.0859 53.1673 20.5443V37.4609C53.1673 45.9193 48.334 49.5443 41.084 49.5443Z"
                                stroke="#0066FF" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"
                                stroke-linejoin="round" />
                            <path class="dark:stroke-dark-yellow"
                                d="M41.0827 21.75L33.5185 27.7917C31.0293 29.7733 26.9452 29.7733 24.456 27.7917L16.916 21.75"
                                stroke="#0066FF" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </div>
                </div>
                {{ session('status') }}
                <span id="timer-message" class="text-gray-400 text-xs" style="display: inline;"> برای ارسال مجدد ایمیل <span id="timer">60</span> ثانیه صبر کنید   </span>
                <div class="text-center">
                    <span class="text-xs text-[#868B90] dark:text-[#ECEEF3] text-center">برای بازیابی رمز عبور حساب خود لطفا
                        ایمیل ارسال شده را تایید کنید.</span>
                </div>
                <div class="flex justify-between w-full text-xs md:text-sm gap-3">
                    <a target="_blank" id="email-view-button" class="flex w-1/2 items-center justify-center gap-2 bg-primery-blue dark:bg-dark-yellow border-primery-blue dark:border-dark-yellow border py-[10px] px-4 rounded-[10px] text-white dark:text-black"
                        href="#">
                        <svg width="22" height="16" viewBox="0 0 22 16" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M1.79013 16.0031H5.18328V7.76264L2.96139 3.88281L0.335938 4.12713V14.5489C0.335938 15.3523 0.986699 16.0031 1.79013 16.0031Z"
                                fill="#0085F7" />
                            <path
                                d="M16.8164 16.0031H20.2096C21.013 16.0031 21.6638 15.3523 21.6638 14.5489V4.12713L19.0421 3.88281L16.8164 7.76264V16.0031H16.8164Z"
                                fill="#00A94B" />
                            <path
                                d="M16.8174 1.4581L14.8242 5.26149L16.8174 7.75964L21.6647 4.12413V2.18522C21.6647 0.388068 19.6131 -0.638354 18.1746 0.44018L16.8174 1.4581Z"
                                fill="#FFBC00" />
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M5.18246 7.76248L3.2832 3.75052L5.18246 1.46094L10.9992 5.82352L16.816 1.46094V7.76248L10.9992 12.1251L5.18246 7.76248Z"
                                fill="#FF4131" />
                            <path
                                d="M0.335938 2.18522V4.12413L5.18328 7.75964V1.4581L3.82602 0.44018C2.38757 -0.638354 0.335938 0.388068 0.335938 2.18522Z"
                                fill="#E51C19" />
                        </svg>
                        مشاهده ایمیل
                    </a>

                    <form method="POST" action="{{ route('password.email') }}" class="w-1/2" id="resend-form">
                        @csrf
                        <x-form.button id="resend-button" spinner-id="resend-spinner" text-id="resend-text" variant="secondary" :disabled="true">
                            ارسال مجدد
                        </x-form.button>
                        <input class="hidden" type="email" name="email" id="resend-email" required>
                    </form>
                </div>
            </div>
        </div>
    @endsession

    <div class="space-y-10">
        <div class="text-center">
            <p class="text-xs md:text-xl font-normal text-gray-600 dark:text-[#FFFFFF]">جهت دریافت کد بازیابی رمز عبور ,
                ایمیل خود را وارد کنید.</p>
        </div>

        <div class="flex flex-col gap-10">
            <div class="w-full xl:w-1/2 2xl:w-[40%] mx-auto">
                <div class="bg-white dark:bg-[#0F0F0E] rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 md:p-8">
                    <form method="POST" action="{{ route('password.email') }}" id="reset-form">
                        <div class="flex flex-col gap-7">
                            @csrf
                            <x-form.text :label="__('Email Address')" for="email" name="email" type="email" required autofocus />

                            <div class="flex items-center justify-center gap-7 md:gap-10 w-full text-xs md:text-base">
                                <x-form.button id="reset-button" spinner-id="reset-spinner" text-id="reset-text">
                                    {{ __('Send Password Reset Link') }}
                                </x-form.button>
                                <a href="{{ route('login') }}"
                                    class="text-primery-blue dark:text-dark-yellow border border-primery-blue dark:border-dark-yellow py-[14px] px-6 md:px-[40px] rounded-xl  md:w-max">بازگشت
                                    به ورود</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const emailInput = document.querySelector('input[name="email"]');
            const resendEmailInput = document.querySelector('#resend-email');
            const emailViewButton = document.querySelector('#email-view-button');

            // Save email in localStorage when the main form is submitted
            emailInput?.form.addEventListener('submit', function() {
                if (emailInput?.value) {
                    localStorage.setItem('email', emailInput.value);
                }
            });

            // Retrieve saved email and update the link dynamically
            const savedEmail = localStorage.getItem('email');
            if (resendEmailInput && savedEmail) {
                resendEmailInput.value = savedEmail;

                const emailDomain = savedEmail.split('@')[1]; // Get domain from email
                let emailLink;

                if (emailDomain.includes('gmail')) {
                    emailLink = 'https://mail.google.com/';
                } else if (emailDomain.includes('yahoo')) {
                    emailLink = 'https://mail.yahoo.com/';
                } else if (emailDomain.includes('outlook') || emailDomain.includes('hotmail')) {
                    emailLink = 'https://outlook.live.com/';
                } else {
                    emailLink = 'https://mail.' + emailDomain; // Generic mail link
                }

                if (emailViewButton) {
                    emailViewButton.href = emailLink; // Update button link
                }
            }
        });
    </script>

    <script>
        (function() {
            const TIMER_DURATION = 60;
            let countdownInterval = null;

            function startCountdown(initialTime) {
                if (initialTime === undefined) {
                    initialTime = TIMER_DURATION;
                }

                const timerSpan = document.getElementById('timer');
                const timerMessage = document.getElementById('timer-message');
                const resendButton = document.getElementById('resend-button');

                if (!timerSpan || !timerMessage) {
                    return;
                }

                if (countdownInterval) {
                    clearInterval(countdownInterval);
                    countdownInterval = null;
                }

                let timeLeft = initialTime;

                if (resendButton) {
                    resendButton.disabled = true;
                }

                // Show the timer message
                timerMessage.style.display = 'inline';
                timerSpan.textContent = timeLeft;

                countdownInterval = setInterval(function() {
                    timeLeft--;
                    timerSpan.textContent = timeLeft;

                    if (timeLeft <= 0) {
                        clearInterval(countdownInterval);
                        countdownInterval = null;
                        sessionStorage.removeItem('emailSentTime');

                        if (resendButton) {
                            resendButton.disabled = false;
                        }

                        timerMessage.style.display = 'none';
                    }
                }, 1000);
            }

            function initTimer() {
                const timerSpan = document.getElementById('timer');
                const timerMessage = document.getElementById('timer-message');
                const resendButton = document.getElementById('resend-button');

                if (!timerSpan || !timerMessage) {
                    return;
                }

                const hasStatusMessage = @json(session()->has('status'));
                if (hasStatusMessage && !sessionStorage.getItem('emailSentTime')) {
                    sessionStorage.setItem('emailSentTime', Date.now());
                }

                const emailSentTime = sessionStorage.getItem('emailSentTime');

                if (emailSentTime) {
                    const timeSinceSent = Math.floor((Date.now() - parseInt(emailSentTime, 10)) / 1000);
                    const remainingTime = TIMER_DURATION - timeSinceSent;

                    if (remainingTime > 0) {
                        startCountdown(remainingTime);
                        return;
                    }

                    sessionStorage.removeItem('emailSentTime');
                }

                // No pending countdown -> make sure UI is reset
                if (resendButton) {
                    resendButton.disabled = false;
                }
                timerMessage.style.display = 'none';
            }

            function checkAndStartTimer() {
                const timerSpan = document.getElementById('timer');
                if (timerSpan) {
                    initTimer();
                } else {
                    setTimeout(checkAndStartTimer, 100);
                }
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function() {
                    setTimeout(checkAndStartTimer, 100);
                });
            } else {
                setTimeout(checkAndStartTimer, 100);
            }

            document.addEventListener('DOMContentLoaded', function() {
                const resetForm = document.getElementById('reset-form');
                const resetButton = document.getElementById('reset-button');
                const resetSpinner = document.getElementById('reset-spinner');
                const resetText = document.getElementById('reset-text');
                const resendButton = document.getElementById('resend-button');
                const resendForm = document.getElementById('resend-form');
                const resendSpinner = document.getElementById('resend-spinner');
                const resendText = document.getElementById('resend-text');

                if (resetForm && resetButton) {
                    resetForm.addEventListener('submit', function() {
                        sessionStorage.setItem('emailSentTime', Date.now());
                        resetButton.disabled = true;
                        if (resetSpinner) resetSpinner.classList.remove('hidden');
                        if (resetText) resetText.textContent = '{{ __('Loading...') }}';
                    });
                }

                if (resendForm && resendButton) {
                    resendForm.addEventListener('submit', function() {
                        sessionStorage.setItem('emailSentTime', Date.now());
                        resendButton.disabled = true;
                        if (resendSpinner) resendSpinner.classList.remove('hidden');
                        if (resendText) resendText.textContent = '{{ __('Loading...') }}';
                    });
                }
            });
        })();
    </script>
@endpush
