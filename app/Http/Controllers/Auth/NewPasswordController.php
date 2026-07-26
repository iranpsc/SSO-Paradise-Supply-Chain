<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePasswordRequest;
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
            'password' => $request->password,
        ]);

        // Updating the password hash invalidates other sessions via AuthenticateSession.
        // Only call logoutOtherDevices when a remember-me cookie is present — CookieJar::hasQueued()
        // throws when no recaller cookie has been queued (framework edge case).
        $recaller = Auth::guard()->getRecallerName();
        if ($request->cookies->has($recaller)) {
            Auth::logoutOtherDevices($request->password);
        }

        return redirect()->route('password.edit')->with('success', __('Password updated successfully.'));
    }
}
