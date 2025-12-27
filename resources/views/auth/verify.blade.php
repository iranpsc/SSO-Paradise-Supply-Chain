<x-layouts.app>
    <div class="space-y-5 lg:space-y-8">
        <div class="w-full xl:w-1/2 2xl:w-[40%] mx-auto">
            <div
                class="bg-white dark:bg-[#0F0F0E] rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 md:p-8">
                <div class="flex flex-col gap-7 dark:text-gray-300">
                    <div class="text-2xl mb-2 dark:text-white mx-auto">{{ __('Verify Your Email Address') }}</div>
                    <div class="flex flex-col gap-5">

                        <x-partials.alerts />

                        @session('resent')
                            <div class="alert alert-success mx-auto" role="alert">
                                {{ __('A fresh verification link has been sent to your email address.') }}
                            </div>
                        @endsession

                        <div class="text-center">
                            <button onclick="document.getElementById('alert-modal').classList.remove('hidden')"
                                class="!bg-transparent !border-0 hover:underline text-primery-blue dark:text-dark-yellow text-sm !p-0 !rounded-none">ارسال
                                مجدد ایمیل</button>
                        </div>

                        <div id="alert-modal"
                            class=" bg-black/10 backdrop-blur-md flex justify-center items-center z-[20000] h-screen w-screen fixed right-0 top-0 text-center">
                            <div
                                class="relative flex items-center justify-center bg-white dark:bg-[#0F0F0E] rounded-xl flex-col gap-5 p-5 text-center min-w-72 dark:text-white">

                                <div onclick="document.getElementById('alert-modal').classList.add('hidden')"
                                    class="absolute top-4 right-4 cursor-pointer">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="none"
                                        viewBox="0 0 21 20"
                                        class="   cursor-pointer stroke-2 stroke-[#00000096] dark:stroke-dark-gray top-5 end-5 z-50"
                                        alt="درباره من">
                                        <path
                                            d="M18.808.22a.75.75 0 0 1 1.061 1.06L11.561 9.59l8.308 8.308a.75.75 0 0 1-1.06 1.061L10.5 10.65l-8.308 8.308a.75.75 0 1 1-1.061-1.06l8.308-8.31L1.131 1.28A.75.75 0 0 1 2.19.22L10.5 8.528 18.808.22Z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div>
                                    <div class="flex w-full justify-center">
                                        <svg width="58" height="58" viewBox="0 0 58 58" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path class="dark:stroke-dark-yellow"
                                                d="M41.084 49.5443H16.9173C9.66732 49.5443 4.83398 45.9193 4.83398 37.4609V20.5443C4.83398 12.0859 9.66732 8.46094 16.9173 8.46094H41.084C48.334 8.46094 53.1673 12.0859 53.1673 20.5443V37.4609C53.1673 45.9193 48.334 49.5443 41.084 49.5443Z"
                                                stroke="#0066FF" stroke-width="1.5" stroke-miterlimit="10"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                            <path class="dark:stroke-dark-yellow"
                                                d="M41.0827 21.75L33.5185 27.7917C31.0293 29.7733 26.9452 29.7733 24.456 27.7917L16.916 21.75"
                                                stroke="#0066FF" stroke-width="1.5" stroke-miterlimit="10"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </div>
                                </div>

                                {{ session('status') }}

                                <span class="text-gray-400 text-xs"> برای ارسال مجدد ایمیل 
                                    <span id="timer">60</span> ثانیه
                                    صبر کنید </span>
                                <div class="text-center">
                                    <span class="text-xs text-[#868B90] dark:text-[#ECEEF3] text-center">
                                        {{ __('Before proceeding, please check your email for a verification link.') }}</span>
                                </div>
                                <div class="flex justify-between w-full text-xs md:text-sm gap-3">
                                    <a target="_blank" id="email-view-button"
                                        class="flex w-1/2 items-center justify-center gap-2 bg-primery-blue dark:bg-dark-yellow border-primery-blue dark:border-dark-yellow border py-[10px] px-4 rounded-[10px] text-white dark:text-black"
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

                                    <form method="POST" action="{{ route('verification.resend') }}" class="w-1/2"
                                        id="resend-form">
                                        @csrf
                                        <x-form.button id="resend-button" spinner-id="resend-spinner"
                                            text-id="resend-text" variant="primary" :disabled="true">
                                            ارسال مجدد
                                        </x-form.button>
                                        <input class="hidden" type="email" name="email"
                                            value="{{ Auth::user()->email }}" id="resend-email" required>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    @push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const resendForm = document.getElementById('resend-form');
    const resendButton = document.getElementById('resend-button');
    const timerSpan = document.getElementById('timer');

    const STORAGE_KEY = 'verify_timer_end';
    const AUTO_SENT_KEY = 'verify_auto_sent';
    const DURATION = 60;

    let interval = null;

    function getRemainingSeconds(endTime) {
        return Math.max(0, Math.ceil((endTime - Date.now()) / 1000));
    }

    function render(remaining) {
        timerSpan.textContent = remaining;
        resendButton.disabled = remaining > 0;
    }

    function runTimer(endTime) {
        clearInterval(interval);

        render(getRemainingSeconds(endTime));

        interval = setInterval(() => {
            const remaining = getRemainingSeconds(endTime);

            if (remaining <= 0) {
                clearInterval(interval);
                localStorage.removeItem(STORAGE_KEY);
                render(0);
            } else {
                render(remaining);
            }
        }, 1000);
    }

    function startTimer(seconds) {
        const endTime = Date.now() + seconds * 1000;
        localStorage.setItem(STORAGE_KEY, endTime);
        runTimer(endTime);
    }

    // ---------- منطق لود صفحه ----------
    const storedEndTime = Number(localStorage.getItem(STORAGE_KEY));
    const hasAutoSent = localStorage.getItem(AUTO_SENT_KEY);

    if (storedEndTime && storedEndTime > Date.now()) {
        // تایمر قبلاً شروع شده → ادامه بده
        runTimer(storedEndTime);
    } else {
        // تایمر جدید
        startTimer(DURATION);

        if (!hasAutoSent) {
            localStorage.setItem(AUTO_SENT_KEY, '1');
            setTimeout(() => {
                resendForm.submit();
            }, 300);
        }
    }

    // ---------- ارسال دستی ----------
    resendForm.addEventListener('submit', function () {
        startTimer(DURATION);
    });

});
</script>


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
    @endpush
</x-layouts.app>
