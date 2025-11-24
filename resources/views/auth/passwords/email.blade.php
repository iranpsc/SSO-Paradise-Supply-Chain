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

            // Function to populate email in resend form and update email view link
            function populateResendEmail(email) {
                if (!email) {
                    email = localStorage.getItem('email') || emailInput?.value;
                }
                
                if (resendEmailInput && email) {
                    resendEmailInput.value = email;
                    localStorage.setItem('email', email);

                    // Safely extract email domain
                    const emailParts = email.split('@');
                    if (emailParts.length > 1) {
                        const emailDomain = emailParts[1].toLowerCase();
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

                        if (emailViewButton && emailLink) {
                            emailViewButton.href = emailLink; // Update button link
                        }
                    }
                }
            }

            // Retrieve saved email and update the link dynamically on page load
            populateResendEmail();
            
            // Also populate when modal appears (for cases where page loads with status message)
            const alertModal = document.getElementById('alert-modal');
            if (alertModal) {
                const observer = new MutationObserver(function(mutations) {
                    if (alertModal.style.display !== 'none') {
                        populateResendEmail();
                    }
                });
                observer.observe(alertModal, { attributes: true, attributeFilter: ['style', 'class'] });
            }
        });
    </script>

    <script>
        (function() {
            const TIMER_DURATION = 60;
            let countdownInterval = null;

            function startCountdown(initialTime) {
                if (initialTime === undefined || initialTime <= 0) {
                    initialTime = TIMER_DURATION;
                }

                const resendButton = document.getElementById('resend-button');
                const resendText = document.getElementById('resend-text');

                if (!resendButton || !resendText) {
                    console.error('Resend button or text element missing when starting countdown');
                    return;
                }

                // Clear any existing interval
                if (countdownInterval) {
                    clearInterval(countdownInterval);
                    countdownInterval = null;
                }

                // Ensure timeLeft is an integer and positive
                let timeLeft = Math.max(1, Math.floor(initialTime));

                // Disable resend button during countdown
                resendButton.disabled = true;

                // Update button text to show timer
                function updateButtonText(seconds) {
                    const newText = `ارسال مجدد (${seconds} ثانیه)`;
                    resendText.textContent = newText;
                    console.log('Updated button text to:', newText);
                }

                // Set initial button text
                updateButtonText(timeLeft);

                console.log('Countdown started:', timeLeft, 'seconds');
                console.log('Resend button:', resendButton);
                console.log('Resend text element:', resendText);
                console.log('Current text content:', resendText.textContent);

                // Start the countdown interval
                countdownInterval = setInterval(function() {
                    timeLeft--;
                    updateButtonText(timeLeft);

                    if (timeLeft <= 0) {
                        clearInterval(countdownInterval);
                        countdownInterval = null;
                        sessionStorage.removeItem('emailSentTime');

                        // Restore original button text and enable button
                        resendButton.disabled = false;
                        resendText.textContent = 'ارسال مجدد';
                        console.log('Countdown completed');
                    }
                }, 1000);
            }

            function initTimer() {
                const resendButton = document.getElementById('resend-button');
                const resendText = document.getElementById('resend-text');

                if (!resendButton || !resendText) {
                    console.warn('Resend button or text element not found in initTimer');
                    return;
                }

                const hasStatusMessage = @json(session()->has('status'));
                console.log('Has status message:', hasStatusMessage);
                
                // If there's a status message and no timer started yet, initialize it
                if (hasStatusMessage && !sessionStorage.getItem('emailSentTime')) {
                    sessionStorage.setItem('emailSentTime', Date.now());
                    console.log('Timer initialized for new password reset email at', Date.now());
                }

                const emailSentTime = sessionStorage.getItem('emailSentTime');
                console.log('Email sent time from storage:', emailSentTime);

                if (emailSentTime) {
                    const timeSinceSent = Math.floor((Date.now() - parseInt(emailSentTime, 10)) / 1000);
                    const remainingTime = TIMER_DURATION - timeSinceSent;
                    console.log('Time since sent:', timeSinceSent, 'seconds, remaining:', remainingTime, 'seconds');

                    if (remainingTime > 0) {
                        console.log('Starting countdown with remaining time:', remainingTime, 'seconds');
                        startCountdown(remainingTime);
                        return;
                    } else {
                        // Timer has expired, clean up
                        console.log('Timer expired, cleaning up');
                        sessionStorage.removeItem('emailSentTime');
                    }
                } 
                
                // If there's a status message (modal is visible), always start the timer
                if (hasStatusMessage) {
                    // Check if we need to start a fresh timer
                    if (!emailSentTime) {
                        console.log('Status message exists but no timer, starting fresh countdown');
                        sessionStorage.setItem('emailSentTime', Date.now());
                        startCountdown(TIMER_DURATION);
                        return;
                    } else {
                        // Timer expired but status message still exists - start fresh
                        console.log('Timer expired but status exists, starting fresh countdown');
                        sessionStorage.setItem('emailSentTime', Date.now());
                        startCountdown(TIMER_DURATION);
                        return;
                    }
                }

                // No pending countdown -> make sure UI is reset
                resendButton.disabled = false;
                resendText.textContent = 'ارسال مجدد';
            }

            function checkAndStartTimer() {
                const resendButton = document.getElementById('resend-button');
                const resendText = document.getElementById('resend-text');
                const alertModal = document.getElementById('alert-modal');
                
                // If modal exists, it means status message is present
                if (alertModal && resendButton && resendText) {
                    console.log('Modal and button elements found, initializing timer...');
                    initTimer();
                    return true;
                }
                
                return false;
            }

            // Initialize timer when DOM is ready
            function initializeTimerOnReady() {
                // Wait for DOM to be ready
                function tryInit(retries = 0) {
                    const maxRetries = 50; // Try for up to 5 seconds
                    if (retries > maxRetries) {
                        console.error('Timer initialization failed after max retries');
                        return;
                    }
                    
                    if (checkAndStartTimer()) {
                        console.log('Timer initialized successfully');
                        return;
                    }
                    // Retry after a short delay
                    setTimeout(() => tryInit(retries + 1), 100);
                }
                
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', function() {
                        setTimeout(() => tryInit(), 100);
                    });
                } else {
                    // DOM already loaded
                    setTimeout(() => tryInit(), 100);
                }
            }
            
            // Start initialization immediately
            initializeTimerOnReady();
            
            // Also try when window loads (in case DOMContentLoaded already fired)
            window.addEventListener('load', function() {
                setTimeout(checkAndStartTimer, 200);
            });

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
