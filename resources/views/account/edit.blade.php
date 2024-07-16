@extends('layouts.app')

@section('content') 
    <div class="space-y-7">
        <form method="POST" action="{{ route('account.update') }}" enctype="multipart/form-data">
            <div class="flex flex-col gap-7 w-full xl:w-1/2 2xl:w-[40%] mx-auto dark:text-[#FFFFFF]" >
                <div class="card-header">{{ __('Update Account') }}</div>
                @csrf
                @method('PUT')

                <div class="flex flex-col gap-5 md:flex-row items-center justify-between">
                    <label for="name" class="form-label">{{ __('Name') }}</label>
                    <input id="name" type="text" class="bg-[#FCFCFC] dark:bg-[#000000] border-[#DEDEE9] dark:border-[#1A1A18] dark:text-[#FFFFFF] placeholder:text-[#868B90]  border-2 rounded-xl py-[10px] focus:border-[#84858F] focus:ring-0 text-[#868B90] font-normal focus:text-[#1A1A18] focus:border-[1px] w-full md:w-[70%] @error('name') is-invalid @enderror" name="name" value="{{ Auth::user()->name }}" required autocomplete="name">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="flex flex-col gap-5 md:flex-row items-center justify-between">
                    <label for="email" class="form-label">{{ __('Email Address') }}</label>
                    <input id="email" type="email" class="bg-[#FCFCFC] dark:bg-[#000000] border-[#DEDEE9] dark:border-[#1A1A18] dark:text-[#FFFFFF] placeholder:text-[#868B90]  border-2 rounded-xl py-[10px] focus:border-[#84858F] focus:ring-0 text-[#868B90] font-normal focus:text-[#1A1A18] focus:border-[1px] w-full md:w-[70%] @error('email') is-invalid @enderror" name="email" value="{{ Auth::user()->email }}" required autocomplete="email">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Upload avatar --}}
                <div class="flex  gap-5 flex-row items-center ">
                    <label for="avatar" class="form-label">{{ __('Avatar') }}</label>
                    <input id="avatar" type="file" class=" @error('avatar') is-invalid @enderror" name="avatar">
                    @error('avatar')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="flex items-center justify-center">
                    <button type="submit" class="text-white bg-primery-blue dark:bg-dark-yellow py-[14px] px-[40px] mx-auto rounded-xl w-full md:w-max">{{ __('Update Account') }}</button>
                </div></div>

        </form>
    </div>
@endsection