<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdatePersonalInfoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'is_company' => 'required|boolean',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'mobile' => 'required|string|ir_mobile:zero',
            'telephone' => 'required|string|ir_phone_with_code',
            'national_code' => 'required|string|ir_national_code',
            'address' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255|required_if:is_company,1',
            'company_address' => 'nullable|string|max:255|required_if:is_company,1',
            'company_registration_number' => 'nullable|string|max:255|required_if:is_company,1',
            'company_national_number' => 'nullable|string|max:255|required_if:is_company,1',
            'company_tax_number' => 'nullable|string|max:255|required_if:is_company,1',
            'company_executive_name' => 'nullable|string|max:255|required_if:is_company,1',
            'melli_card_scan' => 'required|image|max:1024',
            'certificate_scan' => 'required|image|max:1024',
            'bank_card_scan' => 'required|image|max:1024',
        ];
    }
}
