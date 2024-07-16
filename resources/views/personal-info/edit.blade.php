@extends('layouts.app')

@section('content')
    <div class="space-y-10">
        <form action="{{ route('personal-info.update') }}" method="post" enctype="multipart/form-data">
            <div class="flex flex-col gap-7 w-full xl:w-1/2 2xl:w-[40%] mx-auto">
             @csrf
             @method('PUT')

                    <div class="grid md:grid-cols-2 gap-7 dark:text-gray-200">
                        <div class="flex flex-col gap-2">
                            <select id="is_company" placeholder="is_company" class="bg-[#FCFCFC] dark:bg-[#000000] border-[#DEDEE9] dark:border-[#1A1A18] dark:text-[#FFFFFF] placeholder:text-[#868B90] w-full border-2 rounded-xl py-[10px] focus:border-[#84858F] focus:ring-0 text-[#868B90] font-normal focus:text-[#1A1A18] focus:border-[1px] @error('is_company') is-invalid @enderror" name="is_company">
                                <option value="0" @if(old('is_company') == 0 || $personalInfo->is_company == 0) selected @endif>{{ __('Personal') }}</option>
                                <option value="1" @if(old('is_company') == 1 || $personalInfo->is_company == 1) selected @endif>{{ __('Company') }}</option>
                            </select>
                            @error('is_company')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                    </div>
             
             <div id="company_information" class="grid md:grid-cols-2 gap-7 w-full @if(old('is_company') || $personalInfo->is_company) block @else hidden @endIf">
                 <x-form.text for="company_name" name="company_name" label="{{ __('Company Name') }}" :value="old('company_name') ?? $personalInfo->company_name" />
                 <x-form.text for="company_address" name="company_address" label="{{ __('Company Address') }}" :value="old('company_address') ?? $personalInfo->company_address" />
                 <x-form.text for="company_national_number" name="company_national_number" label="{{ __('Company National Number') }}" :value="old('company_national_number') ?? $personalInfo->company_national_number" />
                 <x-form.text for="company_registration_number" name="company_registration_number" label="{{ __('Company Registration Number') }}" :value="old('company_registration_number') ?? $personalInfo->company_registration_number" />
                 <x-form.text for="company_tax_number" name="company_tax_number" label="{{ __('Company Tax Number') }}" :value="old('company_tax_number') ?? $personalInfo->company_tax_number" />
                 <x-form.text for="company_executive_name" name="company_executive_name" label="{{ __('Company Executive Name') }}" :value="old('company_executive_name') ?? $personalInfo->company_executive_name" />
             </div>
             <div class="flex items-center justify-center">
                <button type="submit" class="text-white bg-primery-blue dark:bg-dark-yellow py-[14px] px-[40px] mx-auto rounded-xl w-full md:w-max">{{ __('Update') }}</button>
            </div>

            </div>

         </form>
    </div>
@endsection

@push('scripts')
    <script>
        $('#is_company').on('change', function() {
            if ($(this).val() == 1) {
                $('#company_information').addClass('block').removeClass('hidden');
            } else {
                $('#company_information').addClass('hidden').removeClass('block');
            }
        });
    </script>
@endpush