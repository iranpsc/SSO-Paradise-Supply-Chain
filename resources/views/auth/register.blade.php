@extends('layouts.app')

@section('content')
    <div class="space-y-10" >
        <div class="text-center">
            <p class="text-xs md:text-xl font-normal dark:text-[#FFFFFF]">برای ثبت نام ایمیل / نام کاربری و رمز مورد نظر خودرا وارد کنید. </p>
        </div>
        <div class="flex flex-col gap-10">
            @if ($errors->any(['client_id', 'redirect_uri', 'refferal']))
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li class="mb-0">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                <div class="flex flex-col gap-7 w-full xl:w-1/2 2xl:w-[40%] mx-auto">
                    @csrf

                    <input type="hidden" name="client_id" value="{{ request()->query('client_id') }}">
                    <input type="hidden" name="redirect_uri" value="{{ request()->query('redirect_uri') }}">
                    <input type="hidden" name="referral" value="{{ request()->query('referral') }}">

                    <x-form.text :label="__('Name')" for="name" name="name" required autofocus />

                    <x-form.text :label="__('Email Address')" for="email" name="email" type="email" required />

                    <x-form.text :label="__('Password')" for="password" name="password" type="password" required
                        autocomplete="new-password" />

                    <x-form.text :label="__('Confirm Password')" for="password_confirmation" name="password_confirmation" type="password"
                        required />

                        <div class="flex items-center justify-center">
                            <button type="submit"
                                class="text-white bg-primery-blue dark:bg-dark-yellow py-[14px] px-[40px] mx-auto rounded-xl w-full md:w-[179px]">
                                {{ __('Register') }}
                            </button>
                        </div>
                </div>
            </form>
        </div>
    </div>
@endsection
