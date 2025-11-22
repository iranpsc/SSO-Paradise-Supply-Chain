<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\VerifiesEmails;
use App\Models\User;
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

    protected function verified(Request $request)
    {
        $request->user()->update([
            'code' => $this->generateCode(),
        ]);

        $backUrl = Cache::pull('back_url_' . $request->user()->id);
        $backUrl .= $backUrl ? '?verified=1' : '';

        // Validate the back URL domain
        if ($backUrl) {
            $parsedUrl = parse_url($backUrl);
            $domain = isset($parsedUrl['scheme']) && isset($parsedUrl['host'])
                ? $parsedUrl['scheme'] . '://' . $parsedUrl['host']
                : null;

            if ($domain !== 'https://rgb.irpsc.com') {
                return redirect()->route('home')->with('warning', 'Invalid redirect URL.');
            }
        }

        return $backUrl ? redirect()->away($backUrl) : redirect()->route('home');
    }

    /**
     * Generate user code.
     *
     * @return string
     */
    public function generateCode()
    {
        $lastCode = User::orderBy('code', 'desc')->first()->code;

        if (is_null($lastCode)) {
            $lastCode = 'hm-2000000';
        }

        $lastCode = substr($lastCode, 3);
        $lastCode = intval($lastCode);
        $lastCode++;
        $lastCode = str_pad($lastCode, 6, '0', STR_PAD_LEFT);
        $code = 'hm-' . $lastCode;
        return $code;
    }
}
