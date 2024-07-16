@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card">
                    

                    <div class="flex flex-col gap-7 w-full xl:w-1/2 2xl:w-[40%] mx-auto dark:text-gray-200">
                        <div class="font-bold text-xl text-gray-500">{{ __('Personal Information') }}</div>
                        <x-partials.alerts />

                        <div class="flex flex-col justify-between gap-5 md:flex-row w-full">
                            <div class="font-azarMehr">
                                <strong>{{ __('First Name') }}:</strong> {{ $personalInfo->first_name }}
                            </div>
                            <div class="font-azarMehr">
                                <strong>{{ __('Last Name') }}:</strong> {{ $personalInfo->last_name }}
                            </div>
                        </div>

                        <div class="flex flex-col justify-between gap-5 md:flex-row w-full">
                            <div class="font-azarMehr">
                                <strong>{{ __('Phone Number') }}:</strong> {{ $personalInfo->mobile }}
                            </div>
                            <div class="font-azarMehr">
                                <strong>{{ __('Telephone Number') }}:</strong> {{ $personalInfo->telephone }}
                            </div>
                        </div>

                        <div class="flex flex-col justify-between gap-5 md:flex-row w-full">
                            <div class="font-azarMehr">
                                <strong>{{ __('National Code') }}:</strong> {{ $personalInfo->national_code }}
                            </div>
                            <div class="font-azarMehr">
                                <strong>{{ __('Address') }}:</strong> {{ $personalInfo->address }}
                            </div>
                        </div>

                        @if($personalInfo->is_company)

                            <div class="flex flex-col justify-between gap-5 md:flex-row w-full">
                                <div class="font-azarMehr">
                                    <strong>{{ __('Company Name') }}:</strong> {{ $personalInfo->company_name }}
                                </div>
                                <div class="font-azarMehr">
                                    <strong>{{ __('Company Address') }}:</strong> {{ $personalInfo->company_address }}
                                </div>
                            </div>

                            <div class="flex flex-col justify-between gap-5 md:flex-row w-full">
                                <div class="font-azarMehr">
                                    <strong>{{ __('Company National Number') }}:</strong> {{ $personalInfo->company_national_number }}
                                </div>
                                <div class="font-azarMehr">
                                    <strong>{{ __('Company Registration Number') }}:</strong> {{ $personalInfo->company_registration_number }}
                                </div>
                            </div>

                            <div class="flex flex-col justify-between gap-5 md:flex-row w-full">
                                <div class="font-azarMehr">
                                    <strong>{{ __('Company Tax Number') }}:</strong> {{ $personalInfo->company_tax_number }}
                                </div>
                                <div class="font-azarMehr">
                                    <strong>{{ __('Company Executive Name') }}:</strong> {{ $personalInfo->company_executive_name }}
                                </div>
                            </div>

                        @endif
                        @if(!$personalInfo->is_verified)
                        <div class="flex items-center justify-center">
                            <a href="{{ route('personal-info.edit') }}" class="font-azarMehr text-white bg-primery-blue dark:bg-dark-yellow py-[14px] px-[40px] mx-auto rounded-xl w-full md:w-max" >{{ __('Edit') }}</a>
                        </div>
                    @endif          
                    </div>


                </div>
            </div>
        </div>
    </div>
@endsection