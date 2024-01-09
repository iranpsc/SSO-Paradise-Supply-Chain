@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card">
                    <div class="card-header">{{ __('Personal Information') }}</div>

                    <div class="card-body">

                        <x-partials.alerts />

                        <div class="row mb-3">
                            <div class="col-6">
                                <strong>{{ __('First Name') }}:</strong> {{ $personalInfo->first_name }}
                            </div>
                            <div class="col-6">
                                <strong>{{ __('Last Name') }}:</strong> {{ $personalInfo->last_name }}
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-6">
                                <strong>{{ __('Phone Number') }}:</strong> {{ $personalInfo->mobile }}
                            </div>
                            <div class="col-6">
                                <strong>{{ __('Telephone Number') }}:</strong> {{ $personalInfo->telephone }}
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-6">
                                <strong>{{ __('National Code') }}:</strong> {{ $personalInfo->national_code }}
                            </div>
                            <div class="col-6">
                                <strong>{{ __('Address') }}:</strong> {{ $personalInfo->address }}
                            </div>
                        </div>

                        @if($personalInfo->is_company)

                            <div class="row mb-3">
                                <div class="col-6">
                                    <strong>{{ __('Company Name') }}:</strong> {{ $personalInfo->company_name }}
                                </div>
                                <div class="col-6">
                                    <strong>{{ __('Company Address') }}:</strong> {{ $personalInfo->company_address }}
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-6">
                                    <strong>{{ __('Company National Number') }}:</strong> {{ $personalInfo->company_national_number }}
                                </div>
                                <div class="col-6">
                                    <strong>{{ __('Company Registration Number') }}:</strong> {{ $personalInfo->company_registration_number }}
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-6">
                                    <strong>{{ __('Company Tax Number') }}:</strong> {{ $personalInfo->company_tax_number }}
                                </div>
                                <div class="col-6">
                                    <strong>{{ __('Company Executive Name') }}:</strong> {{ $personalInfo->company_executive_name }}
                                </div>
                            </div>

                        @endif
                                    
                    </div>

                    @if(!$personalInfo->is_verified)
                        <div class="card-footer">
                            <div class="row mb-3">
                                <div class="col-12">
                                    <a href="{{ route('personal-info.edit') }}" class="btn btn-primary">{{ __('Edit') }}</a>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection