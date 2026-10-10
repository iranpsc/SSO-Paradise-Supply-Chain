<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Http\JsonResponse;
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
        $this->middleware('auth')->only('show', 'resend');
        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }

    /**
     * Mark the user addressed by the signed verification URL as verified.
     *
     * @throws AuthorizationException
     */
    public function verify(Request $request)
    {
        $user = $this->userFromSignedVerificationUrl($request);

        if ($user->hasVerifiedEmail()) {
            return $request->wantsJson()
                ? new JsonResponse([], 204)
                : redirect($this->redirectPath());
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        if ($response = $this->verified($request)) {
            return $response;
        }

        return $request->wantsJson()
            ? new JsonResponse([], 204)
            : redirect($this->redirectPath())->with('verified', true);
    }

    /**
     * The user addressed by the signed URL has been verified.
     */
    protected function verified(Request $request)
    {
        $user = $this->userFromSignedVerificationUrl($request);

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

    /**
     * Resolve the user from the signed verification URL.
     *
     * @throws AuthorizationException
     */
    protected function userFromSignedVerificationUrl(Request $request): User
    {
        $user = User::query()->find($request->route('id'));

        if (! $user || ! hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            throw new AuthorizationException;
        }

        return $user;
    }
}
