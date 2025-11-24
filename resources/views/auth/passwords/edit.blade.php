<x-layouts.app>
    <div class="space-y-10">
        <div class="text-center">
            <p class="text-xs md:text-xl font-normal dark:text-[#FFFFFF]"> رمز جدیدی برای حساب کاربری خود بسازید.</p>
        </div>
        <x-partials.alerts />

        <div class="w-full xl:w-1/2 2xl:w-[40%] mx-auto">
            <div class="bg-white dark:bg-[#0F0F0E] rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 md:p-8">
                <form method="POST" action="{{ route('password.new') }}">
                    <div class="flex flex-col gap-7">
                        @csrf
                        @method('PUT')

                        <x-form.text :label="__('Current Password')" for="current_password" name="current_password" type="current_password"
                            requried />

                        <x-form.text :label="__('New Password')" for="password" name="password" type="password" required />

                        <x-form.text :label="__('Confirm New Password')" for="password_confirmation" name="password_confirmation" type="password"
                            required />


                        <div class="flex items-center justify-center">
                            <button type="submit"
                                class="text-white bg-primery-blue dark:bg-dark-yellow py-[14px] px-[40px] mx-auto rounded-xl w-full md:w-max">
                                {{ __('Change Password') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
