@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('Register') }}</div>

                    <div class="card-body">

                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li class="mb-0">{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        
                        <form method="POST" action="{{ route('register') }}">
                            @csrf

                            <input type="hidden" name="client_id" value="{{ request()->query('client_id') }}">
                            <input type="hidden" name="redirect_uri" value="{{ request()->query('redirect_uri') }}">

                            <x-form.text :label="__('Name')" for="name" name="name" required autofocus />

                            <x-form.text :label="__('Email Address')" for="email" name="email" type="email" required />

                            <x-form.text :label="__('Password')" for="password" name="password" type="password" required autocomplete="new-password" />

                            <x-form.text :label="__('Confirm Password')" for="password_confirmation" name="password_confirmation" type="password" required />

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
