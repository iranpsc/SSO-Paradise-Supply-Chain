@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card">
                    <div class="card-header">{{ __('Personal Information') }}</div>

                    <div class="card-body">
                        <form action="{{ route('personal-info.update') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row mb-3">
                                <label for="is_company" class="col-md-4 col-form-label text-md-end">{{ __('Account Type') }}</label>
                                <div class="col-md-6">
                                    <select id="is_company" class="form-select @error('is_company') is-invalid @enderror" name="is_company">
                                        <option value="0" @if(old('is_company') == 0 || $personalInfo->is_company == 0) selected @endif>{{ __('Personal') }}</option>
                                        <option value="1" @if(old('is_company') == 1 || $personalInfo->is_company == 1) selected @endif>{{ __('Company') }}</option>
                                    </select>
                                    @error('is_company')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <x-form.text for="first_name" name="first_name" label="{{ __('First Name') }}" :value="old('first_name') ?? $personalInfo->first_name" />
                            <x-form.text for="last_name" name="last_name" label="{{ __('Last Name') }}" :value="old('last_name') ?? $personalInfo->last_name" />
                            <x-form.text for="mobile" name="mobile" label="{{ __('Phone Number') }}" :value="old('mobile') ?? $personalInfo->mobile" />
                            <x-form.text for="telephone" name="telephone" label="{{ __('Telephone Number') }}" :value="old('telephone') ?? $personalInfo->telephone" />
                            <x-form.text for="national_code" name="national_code" label="{{ __('National Code') }}" :value="old('national_code') ?? $personalInfo->national_code" />
                            <x-form.text for="address" name="address" label="{{ __('Address') }}" :value="old('address') ?? $personalInfo->address" />
                            <x-form.file for="melli_card_scan" name="melli_card_scan" label="{{ __('Melli Card Scan (Back and Front)') }}"/>
                            <x-form.file for="certificate_scan" name="certificate_scan" label="{{ __('Certificate Scan') }}" />
                            <x-form.file for="bank_card_scan" name="bank_card_scan" label="{{ __('Bank Card Scan') }}" />
                            
                            <div id="company_information" class="@if(old('is_company') || $personalInfo->is_company) d-block @else d-none @endIf">
                                <x-form.text for="company_name" name="company_name" label="{{ __('Company Name') }}" :value="old('company_name') ?? $personalInfo->company_name" />
                                <x-form.text for="company_address" name="company_address" label="{{ __('Company Address') }}" :value="old('company_address') ?? $personalInfo->company_address" />
                                <x-form.text for="company_national_number" name="company_national_number" label="{{ __('Company National Number') }}" :value="old('company_national_number') ?? $personalInfo->company_national_number" />
                                <x-form.text for="company_registration_number" name="company_registration_number" label="{{ __('Company Registration Number') }}" :value="old('company_registration_number') ?? $personalInfo->company_registration_number" />
                                <x-form.text for="company_tax_number" name="company_tax_number" label="{{ __('Company Tax Number') }}" :value="old('company_tax_number') ?? $personalInfo->company_tax_number" />
                                <x-form.text for="company_executive_name" name="company_executive_name" label="{{ __('Company Executive Name') }}" :value="old('company_executive_name') ?? $personalInfo->company_executive_name" />
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $('#is_company').on('change', function() {
            if ($(this).val() == 1) {
                $('#company_information').addClass('d-block').removeClass('d-none');
            } else {
                $('#company_information').addClass('d-none').removeClass('d-block');
            }
        });
    </script>
@endpush