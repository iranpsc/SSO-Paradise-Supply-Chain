<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\UpdateAccountRequest;
use App\Models\User;

class AccountController extends Controller
{
    /**
     * Show the account dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function show()
    {
        return view('account.show');
    }

    /**
     * Show the account edit form.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function edit()
    {
        return view('account.edit');
    }

    /**
     * Update the account.
     *
     * @param \App\Http\Requests\UpdateAccountRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateAccountRequest $request)
    {
        $account = User::find(auth()->id());

        $account->fill($request->validated());

        if ($request->hasFile('avatar')) {
            $account->clearMediaCollection('avatars');
            $account->addMediaFromRequest('avatar')->toMediaCollection('avatars');
        }

        // Check if the user has changed their email address
        if ($account->isDirty('email')) {
            // If so, mark the account as unverified
            $account->email_verified_at = null;
            // Send the verification email
            $account->sendEmailVerificationNotification();

            $account->save();

            return redirect()->route('verification.notice')
                ->with('info', __('Your account has been updated! Please verify your new email address.'));
        }

        $account->save();

        return redirect()->route('account.show')->with('success', __('Your account has been updated!'));
    }
}
