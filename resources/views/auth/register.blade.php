@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('Register') }}</div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('register') }}">
                            @csrf

                            <x-forms.text :label="__('Name')" for="name" name="name" required autofocus />

                            <x-forms.text :label="__('Email Address')" for="email" name="email" type="email" required />

                            <x-forms.text :label="__('Password')" for="password" name="password" type="password" required autocomplete="new-password" />

                            <x-forms.text :label="__('Confirm Password')" for="password_confirmation" name="password_confirmation" type="password" required />

                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button type="submit" class="btn btn-primary">
                                        {{ __('Register') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
