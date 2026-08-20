<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AppendWalletLoginToOAuthCallback
{
    /**
     * Append wallet_login to the OAuth client callback redirect when present.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->shouldAppendWalletLogin($request, $response)) {
            return $response;
        }

        $walletLogin = (bool) $request->session()->pull('wallet_login', false);

        if (! $walletLogin) {
            return $response;
        }

        $location = $response->headers->get('Location');
        $separator = str_contains($location, '?') ? '&' : '?';

        $response->headers->set(
            'Location',
            $location.$separator.http_build_query(['wallet_login' => 'true'])
        );

        return $response;
    }

    private function shouldAppendWalletLogin(Request $request, Response $response): bool
    {
        if (! $response->isRedirect()) {
            return false;
        }

        $routeName = $request->route()?->getName();

        if (! in_array($routeName, ['passport.authorizations.authorize', 'passport.authorizations.approve'], true)) {
            return false;
        }

        $location = $response->headers->get('Location');

        if (! is_string($location) || $location === '') {
            return false;
        }

        return str_contains($location, 'code=');
    }
}
