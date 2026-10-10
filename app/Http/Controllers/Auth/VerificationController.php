<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class VerificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Email Verification Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling email verification for any
    | user that recently registered with the application. Emails may also
    | be re-sent if the user didn't receive the original email message.
    |
    */

    use VerifiesEmails;

    /**
     * Where to redirect users after verification.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }

    /**
     * The authenticated user's email has been verified.
     */
    protected function verified(Request $request)
    {
        $user = User::where('id', $request->route('id'))->first();

        $user->assignMemberCode();

        $backUrl = Cache::pull('back_url_'.$user->id);

        if (! is_string($backUrl) || $backUrl === '') {
            return redirect()->route('home');
        }

        $parsedUrl = parse_url($backUrl);
        $domain = isset($parsedUrl['scheme'], $parsedUrl['host'])
            ? $parsedUrl['scheme'].'://'.$parsedUrl['host']
            : null;

        if ($domain !== 'https://metarang.com') {
            return redirect()->route('home')->with('warning', 'Invalid redirect URL.');
        }

        $separator = str_contains($backUrl, '?') ? '&' : '?';

        return redirect()->away($backUrl.$separator.'verified=1');
    }
}
