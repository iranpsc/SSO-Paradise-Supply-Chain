<x-app-layout>
    <div class="flex justify-center">
        <div class="flex flex-col gap-7 w-full xl:w-1/2 2xl:w-[40%] mx-auto md:text-xl  dark:text-[#FFFFFF]">
            <div class="card-header">{{ __('Account Info') }}</div>
            <x-partials.alerts />

            <div class="flex flex-col gap-5 md:flex-row items-center justify-between">
                <label for="name" class="form-label">{{ __('Name') }}</label>
                <input id="name" type="text"
                    class="bg-[#FCFCFC] dark:bg-[#000000] border-[#DEDEE9] dark:border-[#1A1A18] dark:text-[#FFFFFF] placeholder:text-[#868B90]  border-2 rounded-xl py-[10px] focus:border-[#84858F] focus:ring-0 text-[#868B90] font-normal focus:text-[#1A1A18] focus:border-[1px] w-full md:w-[70%]"
                    name="name" value="{{ Auth::user()->name }}" disabled>
            </div>

            <div class="flex flex-col gap-5 md:flex-row items-center justify-between">
                <label for="email" class="form-label">{{ __('E-Mail Address') }}</label>
                <input id="email" type="email"
                    class="bg-[#FCFCFC] dark:bg-[#000000] border-[#DEDEE9] dark:border-[#1A1A18] dark:text-[#FFFFFF] placeholder:text-[#868B90]  border-2 rounded-xl py-[10px] focus:border-[#84858F] focus:ring-0 text-[#868B90] font-normal focus:text-[#1A1A18] focus:border-[1px] w-full md:w-[70%]"
                    name="email" value="{{ Auth::user()->email }}" disabled>
            </div>
            <div class="flex items-center justify-center">
                <a href="{{ route('account.edit') }}"
                    class="text-white bg-primery-blue dark:bg-dark-yellow py-[14px] px-[40px] mx-auto rounded-xl w-full md:w-max">{{ __('Update Account') }}</a>
            </div>
        </div>
    </div>
</x-app-layout>
