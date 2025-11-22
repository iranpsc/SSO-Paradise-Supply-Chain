<x-layouts.app>
    <div class="space-y-7">
        <form method="POST" action="{{ route('account.update') }}" enctype="multipart/form-data">
            <div class="flex flex-col gap-7 w-full xl:w-1/2 2xl:w-[40%] mx-auto dark:text-[#FFFFFF]">
                <div class="card-header">{{ __('Update Account') }}</div>
                @csrf
                @method('PUT')

                <div class="flex flex-col gap-5 md:flex-row items-center justify-between">
                    <label for="name" class="form-label">{{ __('Name') }}</label>
                    <input id="name" type="text"
                        class="bg-[#FCFCFC] dark:bg-[#000000] border-[#DEDEE9] dark:border-[#1A1A18] dark:text-[#FFFFFF] placeholder:text-[#868B90]  border-2 rounded-xl py-[10px] focus:border-[#84858F] focus:ring-0 text-[#868B90] font-normal focus:text-[#1A1A18] focus:border-[1px] w-full md:w-[70%] @error('name') is-invalid @enderror"
                        name="name" value="{{ Auth::user()->name }}" required autocomplete="name">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="flex flex-col gap-5 md:flex-row items-center justify-between">
                    <label for="email" class="form-label">{{ __('Email Address') }}</label>
                    <input id="email" type="email"
                        class="bg-[#FCFCFC] dark:bg-[#000000] border-[#DEDEE9] dark:border-[#1A1A18] dark:text-[#FFFFFF] placeholder:text-[#868B90]  border-2 rounded-xl py-[10px] focus:border-[#84858F] focus:ring-0 text-[#868B90] font-normal focus:text-[#1A1A18] focus:border-[1px] w-full md:w-[70%] @error('email') is-invalid @enderror"
                        name="email" value="{{ Auth::user()->email }}" required autocomplete="email">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Upload avatar --}}
                <div class="flex flex-col gap-5 md:flex-row items-center justify-between">
                    <label for="avatar" class="form-label">{{ __('Profile Image') }}</label>
                    <div class="flex flex-col gap-4 w-full md:w-[70%]">
                        {{-- Current avatar preview --}}
                        <div class="flex items-center gap-4">
                            <div class="relative">
                                <img id="current-avatar"
                                    src="{{ Auth::user()->getFirstMediaUrl('avatars') ?: asset('images/logo/accounts.png') }}"
                                    alt="{{ __('Current Avatar') }}"
                                    class="w-20 h-20 rounded-full object-cover border-2 border-[#DEDEE9] dark:border-[#1A1A18]">
                            </div>
                            <div class="flex flex-col gap-2 flex-1">
                                <label for="avatar" class="cursor-pointer">
                                    <span class="text-white bg-primery-blue dark:bg-dark-yellow py-2 px-4 rounded-xl inline-block text-sm hover:opacity-90 transition-opacity">
                                        {{ __('Choose Image') }}
                                    </span>
                                </label>
                                <input id="avatar"
                                    type="file"
                                    accept="image/jpeg,image/png,image/webp"
                                    class="hidden @error('avatar') is-invalid @enderror"
                                    name="avatar"
                                    onchange="previewImage(this)">
                                <p class="text-sm text-[#868B90] dark:text-[#868B90]">
                                    {{ __('Allowed formats: JPEG, PNG, WebP. Max size: 1MB') }}
                                </p>
                            </div>
                        </div>
                        {{-- Preview selected image --}}
                        <div id="image-preview-container" class="hidden">
                            <img id="image-preview"
                                src=""
                                alt="{{ __('Preview') }}"
                                class="w-32 h-32 rounded-full object-cover border-2 border-[#84858F] dark:border-[#84858F] mx-auto">
                            <p class="text-center text-sm text-[#868B90] dark:text-[#868B90] mt-2">
                                {{ __('New image preview') }}
                            </p>
                        </div>
                        @error('avatar')
                            <div class="invalid-feedback text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center justify-center">
                    <button type="submit"
                        class="text-white bg-primery-blue dark:bg-dark-yellow py-[14px] px-[40px] mx-auto rounded-xl w-full md:w-max">{{ __('Update Account') }}</button>
                </div>
            </div>

        </form>
    </div>
</x-layouts.app>

@push('scripts')
    <script>
        function previewImage(input) {
            const previewContainer = document.getElementById('image-preview-container');
            const previewImg = document.getElementById('image-preview');
            const currentAvatar = document.getElementById('current-avatar');

            if (input.files && input.files[0]) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    previewContainer.classList.remove('hidden');
                    currentAvatar.style.opacity = '0.5';
                }

                reader.readAsDataURL(input.files[0]);
            } else {
                previewContainer.classList.add('hidden');
                currentAvatar.style.opacity = '1';
            }
        }
    </script>
@endpush
