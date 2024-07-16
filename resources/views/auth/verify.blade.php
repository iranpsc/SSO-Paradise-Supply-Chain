@extends('layouts.app')

@section('content')
<div class="space-y-10">
    <div class="flex flex-col gap-7 w-full xl:w-1/2 2xl:w-[40%] mx-auto dark:text-gray-300">
        <div class="card-header">{{ __('Verify Your Email Address') }}</div>

        <div class="flex flex-col gap-5">

            <x-partials.alerts />
            
            @if (session('resent'))
                <div class="alert alert-success" role="alert">
                    {{ __('A fresh verification link has been sent to your email address.') }}
                </div>
            @endif

            {{ __('Before proceeding, please check your email for a verification link.') }}
            {{ __('If you did not receive the email') }},
            <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                @csrf
                <button type="submit" class="text-primery-blue dark:text-dark-yellow">{{ __('click here to request another') }}</button>.
            </form>
        </div>
    </div>
</div>
@endsection
