<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\VerifiesEmails;
use App\Models\User;
use Illuminate\Http\Request;

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

        $backUrl = $request->session()->pull('back_url');
        $backUrl .= '?registered=1&verified=1';

        return redirect()->to($backUrl);
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
