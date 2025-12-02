<x-layouts.app>
    <div class="space-y-5 lg:space-y-8">
        <div class="w-full xl:w-1/2 2xl:w-[40%] mx-auto">
            <div class="bg-white dark:bg-[#0F0F0E] rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 md:p-8 lg:p-12">
                <div class="flex flex-col items-center justify-center text-center space-y-6">
                    <!-- Error Icon -->
                    <div class="w-24 h-24 md:w-32 md:h-32 flex items-center justify-center rounded-full bg-red-50 dark:bg-red-900/20">
                        <svg class="w-12 h-12 md:w-16 md:h-16 text-red-500 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>

                    <!-- Error Code -->
                    <div class="space-y-2">
                        <h1 class="text-6xl md:text-7xl lg:text-8xl font-bold text-primery-blue dark:text-dark-yellow">
                            {{ $exception->getStatusCode() ?? '500' }}
                        </h1>
                        <h2 class="text-xl md:text-2xl lg:text-3xl font-bold text-gray-900 dark:text-[#FFFFFF]">
                            @if($exception->getStatusCode() == 404)
                                صفحه یافت نشد
                            @elseif($exception->getStatusCode() == 403)
                                دسترسی غیرمجاز
                            @elseif($exception->getStatusCode() == 500)
                                خطای سرور
                            @elseif($exception->getStatusCode() == 503)
                                سرویس در دسترس نیست
                            @else
                                خطا رخ داده است
                            @endif
                        </h2>
                    </div>

                    <!-- Error Message -->
                    <div class="space-y-3 max-w-md">
                        <p class="text-sm md:text-base lg:text-lg text-gray-600 dark:text-gray-400 leading-relaxed">
                            @if($exception->getStatusCode() == 404)
                                متأسفانه صفحه‌ای که به دنبال آن هستید یافت نشد. ممکن است آدرس تغییر کرده باشد یا صفحه حذف شده باشد.
                            @elseif($exception->getStatusCode() == 403)
                                شما اجازه دسترسی به این صفحه را ندارید. لطفاً با مدیر سیستم تماس بگیرید.
                            @elseif($exception->getStatusCode() == 500)
                                متأسفانه خطایی در سرور رخ داده است. لطفاً بعداً دوباره تلاش کنید.
                            @elseif($exception->getStatusCode() == 503)
                                سرویس در حال حاضر در دسترس نیست. لطفاً بعداً دوباره تلاش کنید.
                            @else
                                متأسفانه خطایی رخ داده است. لطفاً بعداً دوباره تلاش کنید.
                            @endif
                        </p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 w-full sm:w-auto pt-4">
                        <a href="{{ auth()->check() ? route('home') : route('login') }}" class="inline-flex items-center justify-center">
                            <x-form.button full-width>
                                بازگشت به صفحه اصلی
                            </x-form.button>
                        </a>
                        <button onclick="window.history.back()" class="inline-flex items-center justify-center">
                            <x-form.button variant="outline" full-width>
                                بازگشت به صفحه قبل
                            </x-form.button>
                        </button>
                    </div>

                    <!-- Additional Help -->
                    <div class="pt-6 border-t border-gray-200 dark:border-gray-700 w-full">
                        <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400">
                            اگر مشکل ادامه داشت، لطفاً با پشتیبانی تماس بگیرید:
                            <a href="mailto:Cq@irpsc.com" class="text-primery-blue dark:text-dark-yellow hover:underline">
                                Cq@irpsc.com
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

