<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\UpdatePasswordRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class NewPasswordController extends Controller
{
    /**
     * Show the form for editing the user's password.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function showForm()
    {
        return view('auth.passwords.edit');
    }

    /**
     * Update the user's password.
     *
     * @param UpdatePasswordRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdatePasswordRequest $request)
    {
        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        // Invalidate other sessions
        Auth::logoutOtherDevices($request->password);

        return redirect()->route('password.edit')->with('success', __('Password updated successfully.'));
    }
}
