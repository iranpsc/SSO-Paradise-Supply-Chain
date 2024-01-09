@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('Change Password') }}</div>

                    <div class="card-body">

                        <x-partials.alerts />
                        
                        <form method="POST" action="{{ route('password.new') }}">
                            @csrf
                            @method('PUT')

                            <x-forms.text :label="__('Current Password')" for="current_password" name="current_password" type="current_password" requried/>

                            <x-forms.text :label="__('New Password')" for="password" name="password" type="password" required/>

                            <x-forms.text :label="__('Confirm New Password')" for="password_confirmation" name="password_confirmation" type="password" required/>

                            <div class="mb-3 row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button type="submit" class="btn btn-primary">
                                        {{ __('Change Password') }}
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