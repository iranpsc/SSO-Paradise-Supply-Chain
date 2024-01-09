@extends('layouts.app')

@section('content')
    <div class="row justify-content-center">
        <div class="col-sm-4">
            <div class="card">
                <div class="card-header">{{ __('Logout') }}</div>

                <div class="card-body">
                    <form action="{{ route('logout') }}" method="post">
                        @csrf

                        <div class="row mb-3">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-block">{{ __('Logout') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection