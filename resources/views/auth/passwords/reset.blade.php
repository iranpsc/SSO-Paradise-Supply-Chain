@extends('layouts.app')

@section('content')
    <div class="space-y-10">
        <div class="card-header">{{ __('Reset Password') }}</div>

        <form method="POST" action="{{ route('password.update') }}">
            <div class="flex flex-col gap-7 w-full xl:w-1/2 2xl:w-[40%] mx-auto">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">

                <div class="row mb-3">
                    <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('Email Address') }}</label>

                    <div class="col-md-6">
                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                            name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus>

                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <x-form.text :label="__('Password')" for="password" name="password" type="password" required
                    autocomplete="new-password" />

                <x-form.text :label="__('Confirm Password')" for="password_confirmation" name="password_confirmation" type="password"
                    required />

                <div class="row mb-0">
                    <div class="col-md-6 offset-md-4">
                        <button type="submit" class="btn btn-primary">
                            {{ __('Reset Password') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
