<x-layouts.app>
    <div class="space-y-5 lg:space-y-8">
        <div class="w-full xl:w-1/2 2xl:w-[40%] mx-auto">
            <div class="bg-white dark:bg-[#0F0F0E] rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 md:p-8 lg:p-12">
                <div class="flex flex-col items-center justify-center text-center space-y-6">
                    <!-- Error Icon -->
                    <div class="w-24 h-24 md:w-32 md:h-32 flex items-center justify-center rounded-full bg-yellow-50 dark:bg-yellow-900/20">
                        <svg class="w-12 h-12 md:w-16 md:h-16 text-yellow-500 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>

                    <!-- Error Code -->
                    <div class="space-y-2">
                        <h1 class="text-6xl md:text-7xl lg:text-8xl font-bold text-primery-blue dark:text-dark-yellow">
                            419
                        </h1>
                        <h2 class="text-xl md:text-2xl lg:text-3xl font-bold text-gray-900 dark:text-[#FFFFFF]">
                            انقضای نشست
                        </h2>
                    </div>

                    <!-- Error Message -->
                    <div class="space-y-3 max-w-md">
                        <p class="text-sm md:text-base lg:text-lg text-gray-600 dark:text-gray-400 leading-relaxed">
                            نشست شما منقضی شده است. لطفاً صفحه را رفرش کنید و دوباره تلاش کنید. این خطا معمولاً زمانی رخ می‌دهد که فرم برای مدت طولانی باز مانده باشد.
                        </p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 w-full sm:w-auto pt-4">
                        <button onclick="window.location.reload()" class="inline-flex items-center justify-center">
                            <x-form.button full-width>
                                رفرش صفحه
                            </x-form.button>
                        </button>
                        <a href="{{ auth()->check() ? route('home') : route('login') }}" class="inline-flex items-center justify-center">
                            <x-form.button variant="outline" full-width>
                                بازگشت به صفحه اصلی
                            </x-form.button>
                        </a>
                    </div>

                    <!-- Additional Help -->
                    <div class="pt-6 border-t border-gray-200 dark:border-gray-700 w-full">
                        <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400">
                            اگر مشکل ادامه داشت، لطفاً با پشتیبانی تماس بگیرید:
                            <a href="mailto:Cq@irpsc.com" class="text-primery-blue dark:text-dark-yellow hover:underline">
                                hq@irpsc.com
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

