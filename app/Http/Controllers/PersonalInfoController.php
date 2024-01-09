<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePersonalInfoRequest;
use Illuminate\Http\Request;

class PersonalInfoController extends Controller
{

    /**
     * Display the specified resource.
     * 
     * @return \Illuminate\Contracts\View\View
     */
    public function show()
    {
        return view('personal-info.show')
            ->with('personalInfo', auth()->user()->personalInfo);
    }

    /**
     * Show the form for editing the specified resource.
     * 
     * @return \Illuminate\Contracts\View\View
     */
    public function edit()
    {
        return view('personal-info.edit')
            ->with('personalInfo', auth()->user()->personalInfo);
    }

    /**
     * Update the specified resource in storage.
     * 
     * @param \App\Http\Requests\UpdatePersonalInfoRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdatePersonalInfoRequest $request)
    {
        $request->user()->personalInfo()->updateOrCreate(
            ['user_id' => $request->user()->id],
            $request->only([
                'is_company',
                'first_name',
                'last_name',
                'mobile',
                'telephone',
                'national_code',
                'address',
                'company_name',
                'company_address',
                'company_registration_number',
                'company_national_number',
                'company_tax_number',
                'company_executive_name',
            ])
        );

        if ($request->hasFile('melli_card_scan')) {
            $request->user()->personalInfo->clearMediaCollection('melli_card_scan', 'local');
            $request->user()->personalInfo->addMediaFromRequest('melli_card_scan')
                ->toMediaCollection('melli_card_scan', 'local');
        }

        if ($request->hasFile('certificate_scan')) {
            $request->user()->personalInfo->clearMediaCollection('melli_card_scan', 'local');
            $request->user()->personalInfo->addMediaFromRequest('certificate_scan')
                ->toMediaCollection('certificate_scan', 'local');
        }

        if ($request->hasFile('bank_card_scan')) {
            $request->user()->personalInfo->clearMediaCollection('melli_card_scan', 'local');
            $request->user()->personalInfo->addMediaFromRequest('bank_card_scan')
                ->toMediaCollection('bank_card_scan', 'local');
        }

        return redirect()->route('personal-info.show')
            ->with('success', __('Your personal information has been updated successfully.'));
    }
}
