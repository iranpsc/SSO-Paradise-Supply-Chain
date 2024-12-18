<x-layouts.app>



@session('status')
    <div
        class="bg-black/10 backdrop-blur-md flex justify-center items-center z-[20000] h-screen w-screen fixed right-0 top-0 text-center">
        <div class="flex items-center justify-center bg-white dark:bg-[#0F0F0E] rounded-xl flex-col gap-5 p-5 text-center min-w-72 dark:text-white">
            <div>
                <div class="flex w-full justify-center">
                    <svg width="58" height="58" viewBox="0 0 58 58" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path class="dark:stroke-dark-yellow" d="M41.084 49.5443H16.9173C9.66732 49.5443 4.83398 45.9193 4.83398 37.4609V20.5443C4.83398 12.0859 9.66732 8.46094 16.9173 8.46094H41.084C48.334 8.46094 53.1673 12.0859 53.1673 20.5443V37.4609C53.1673 45.9193 48.334 49.5443 41.084 49.5443Z" stroke="#0066FF" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                        <path class="dark:stroke-dark-yellow" d="M41.0827 21.75L33.5185 27.7917C31.0293 29.7733 26.9452 29.7733 24.456 27.7917L16.916 21.75" stroke="#0066FF" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            </div>
            {{ session('status') }}
            <p id="timer-text" class="text-xs text-gray-500 "></p>
            <div class="text-center">
                <span class="text-xs text-[#868B90] dark:text-[#ECEEF3] text-center">برای بازیابی رمز عبور حساب خود لطفا ایمیل ارسال شده را تایید کنید.</span>
            </div>
            <div class="flex justify-between w-full text-xs md:text-sm gap-3">
                <a class="flex w-1/2 items-center justify-center gap-2 bg-primery-blue dark:bg-dark-yellow border-primery-blue dark:border-dark-yellow border py-[10px] px-4 rounded-[10px] text-white dark:text-black" href="https://mail.google.com/">
                    <svg width="22" height="16" viewBox="0 0 22 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1.79013 16.0031H5.18328V7.76264L2.96139 3.88281L0.335938 4.12713V14.5489C0.335938 15.3523 0.986699 16.0031 1.79013 16.0031Z" fill="#0085F7" />
                        <path d="M16.8164 16.0031H20.2096C21.013 16.0031 21.6638 15.3523 21.6638 14.5489V4.12713L19.0421 3.88281L16.8164 7.76264V16.0031H16.8164Z" fill="#00A94B" />
                        <path d="M16.8174 1.4581L14.8242 5.26149L16.8174 7.75964L21.6647 4.12413V2.18522C21.6647 0.388068 19.6131 -0.638354 18.1746 0.44018L16.8174 1.4581Z" fill="#FFBC00" />
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M5.18246 7.76248L3.2832 3.75052L5.18246 1.46094L10.9992 5.82352L16.816 1.46094V7.76248L10.9992 12.1251L5.18246 7.76248Z" fill="#FF4131" />
                        <path d="M0.335938 2.18522V4.12413L5.18328 7.75964V1.4581L3.82602 0.44018C2.38757 -0.638354 0.335938 0.388068 0.335938 2.18522Z" fill="#E51C19" />
                    </svg>
                    مشاهده ایمیل
                </a>

                <form method="POST" action="{{ route('password.email') }}" class="w-1/2">
                    @csrf
                    <button id="resend-button" type="submit" class="text-white dark:text-black bg-primery-blue dark:bg-dark-yellow py-[14px] px-6 md:px-[40px] rounded-xl md:w-max border-primery-blue dark:border-dark-yellow border">
                        ارسال مجدد
                    </button>
                    <input class="hidden" type="email" name="email" id="resend-email" required>
                    
                </form>
            </div>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const emailInput = document.querySelector('#resend-email');
        const savedEmail = localStorage.getItem('email');
        if (emailInput && savedEmail) {
            emailInput.value = savedEmail;
        }
    });
</script>
@endSession

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const resendButton = document.getElementById('resend-button');
        const timerText = document.getElementById('timer-text');

        let timer = 2 * 60; 

        function updateTimer() {
            const minutes = Math.floor(timer / 60);
            const seconds = timer % 60;
            timerText.textContent = `برای ارسال مجدد  ${minutes}:${seconds < 10 ? '0' : ''}${seconds} ثانیه صبر کنید.`;
            timer--;

            if (timer < 0) {
                clearInterval(timerInterval);
                resendButton.disabled = false;
                resendButton.classList.remove('bg-gray-400', 'cursor-not-allowed');
                timerText.textContent = '';
            }
        }

        resendButton.addEventListener('click', function (event) {
            event.preventDefault();
            resendButton.disabled = true;
            resendButton.classList.add('bg-gray-400', 'cursor-not-allowed');
            timer = 2 * 60;
            updateTimer();
            timerInterval = setInterval(updateTimer, 1000);
        });

      
        resendButton.disabled = true;
        resendButton.classList.add('bg-gray-400', 'cursor-not-allowed');
        updateTimer();
        const timerInterval = setInterval(updateTimer, 1000);
    });
</script>




    <div class="space-y-10">

        <div class="text-center">
            <p class="text-xs md:text-xl font-normal text-gray-600 dark:text-[#FFFFFF]">جهت دریافت کد بازیابی رمز عبور ,
                ایمیل خود را وارد کنید.</p>
        </div>

        <div class="flex flex-col gap-10">
            <form method="POST" action="{{ route('password.email') }}">
                <div class="flex flex-col gap-7 w-full xl:w-1/2 2xl:w-[40%] mx-auto">
                    @csrf
                    <x-form.text :label="__('Email Address')" for="email" name="email" type="email" required autofocus />

                    <div class="flex items-center justify-center gap-7 md:gap-10 w-full text-xs md:text-base">
                        <button type="submit"
                            class="text-white bg-primery-blue dark:bg-dark-yellow py-[14px] px-6 md:px-[40px]  rounded-xl  md:w-max border-primery-blue dark:border-dark-yellow border">
                            {{ __('Send Password Reset Link') }}
                        </button>
                        <a href="{{ route('register') }}"
                            class="text-primery-blue dark:text-dark-yellow border border-primery-blue dark:border-dark-yellow py-[14px] px-6 md:px-[40px] rounded-xl  md:w-max">بازگشت
                            به ثبت نام</a>
                    </div>
                </div>

            </form>
<script>
    function saveEmail(event) {
        const emailInput = document.querySelector('input[name="email"]');
        if (emailInput && emailInput.value) {
            localStorage.setItem('email', emailInput.value);
        }
    }
</script>

        </div>
    </div>
</x-layouts.app>
