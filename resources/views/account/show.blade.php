@extends('layouts.app')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow">
                <div class="card-header">{{ __('Account Info') }}</div>

                <div class="card-body">

                    <x-partials.alerts />
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">{{ __('Name') }}</label>
                        <input id="name" type="text" class="form-control" name="name" value="{{ Auth::user()->name }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">{{ __('E-Mail Address') }}</label>
                        <input id="email" type="email" class="form-control" name="email" value="{{ Auth::user()->email }}" disabled>
                    </div>

                </div>

                <div class="card-footer">
                    <div class="mb-3">
                        <a href="{{ route('account.edit') }}" class="btn btn-primary">{{ __('Update Account') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection