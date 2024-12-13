<x-layouts.app>

    @session('status')
        <div class="alert alert-success text-center text-green-500 font-bold" role="alert">
            {{ session('status') }}
        </div>
    @endsession

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
        </div>
    </div>
</x-layouts.app>
