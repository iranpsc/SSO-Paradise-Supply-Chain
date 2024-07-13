@extends('layouts.app')

@section('content')
<div class="space-y-10">
    <div class="text-center">
        <p class="text-xs md:text-xl font-normal dark:text-[#FFFFFF]">جهت دریافت کد بازیابی رمز عبور  , ایمیل خود را وارد کنید.</p>
    </div>
    <div class="flex flex-col gap-10">
        @if (session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            <div class="flex flex-col gap-7 w-full xl:w-1/2 2xl:w-[40%] mx-auto">
                @csrf

            <x-form.text :label="__('Email Address')" for="email" name="email" type="email" required autofocus />

            
                <div class="flex items-center justify-center">
                    <button type="submit" class="text-white bg-primery-blue dark:bg-dark-yellow py-[14px] px-[40px] mx-auto rounded-xl w-full md:w-max">
                        {{ __('Send Password Reset Link') }}
                    </button>
                </div>
            </div>
           
        </form>
    </div>
</div>
@endsection
