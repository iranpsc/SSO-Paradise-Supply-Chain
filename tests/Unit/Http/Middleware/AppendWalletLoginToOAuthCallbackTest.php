<?php

namespace Tests\Unit\Http\Middleware;

use App\Http\Middleware\AppendWalletLoginToOAuthCallback;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class AppendWalletLoginToOAuthCallbackTest extends TestCase
{
    private AppendWalletLoginToOAuthCallback $middleware;

    protected function setUp(): void
    {
        parent::setUp();

        $this->middleware = new AppendWalletLoginToOAuthCallback();
        $this->startSession();
    }

    #[Test]
    public function it_appends_wallet_login_with_ampersand_when_callback_already_has_query_string(): void
    {
        session(['wallet_login' => true]);

        $response = $this->runMiddleware(
            $this->oauthRequest('passport.authorizations.authorize'),
            redirect('https://app.example.com/callback?code=abc&state=xyz')
        );

        $this->assertSame(
            'https://app.example.com/callback?code=abc&state=xyz&wallet_login=true',
            $response->headers->get('Location')
        );
        $this->assertFalse(session()->has('wallet_login'));
    }

    #[Test]
    public function it_appends_wallet_login_with_question_mark_when_callback_has_no_query_string(): void
    {
        session(['wallet_login' => true]);

        $response = $this->runMiddleware(
            $this->oauthRequest('passport.authorizations.authorize'),
            redirect('https://app.example.com/callback/code=abc')
        );

        $this->assertSame(
            'https://app.example.com/callback/code=abc?wallet_login=true',
            $response->headers->get('Location')
        );
    }

    #[Test]
    public function it_appends_wallet_login_on_passport_authorizations_approve_route(): void
    {
        session(['wallet_login' => true]);

        $response = $this->runMiddleware(
            $this->oauthRequest('passport.authorizations.approve'),
            redirect('https://app.example.com/callback?code=abc')
        );

        $this->assertStringContainsString('wallet_login=true', $response->headers->get('Location'));
    }

    #[Test]
    public function it_leaves_response_unchanged_when_wallet_login_session_flag_is_false(): void
    {
        $response = $this->runMiddleware(
            $this->oauthRequest(),
            redirect('https://app.example.com/callback?code=abc')
        );

        $this->assertSame(
            'https://app.example.com/callback?code=abc',
            $response->headers->get('Location')
        );
    }

    #[Test]
    public function it_leaves_non_redirect_responses_unchanged(): void
    {
        session(['wallet_login' => true]);

        $plainResponse = new Response('ok', 200);

        $response = $this->runMiddleware($this->oauthRequest(), $plainResponse);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('ok', $response->getContent());
        $this->assertTrue(session('wallet_login'));
    }

    #[Test]
    public function it_leaves_redirects_on_unrelated_routes_unchanged(): void
    {
        session(['wallet_login' => true]);

        $request = Request::create('/home', 'GET');
        $route = new Route('GET', '/home', []);
        $route->name('home');
        $request->setRouteResolver(fn () => $route);
        $request->setLaravelSession($this->app['session.store']);

        $response = $this->runMiddleware(
            $request,
            redirect('https://app.example.com/callback?code=abc')
        );

        $this->assertSame(
            'https://app.example.com/callback?code=abc',
            $response->headers->get('Location')
        );
        $this->assertTrue(session('wallet_login'));
    }

    #[Test]
    public function it_leaves_redirects_without_authorization_code_unchanged(): void
    {
        session(['wallet_login' => true]);

        $response = $this->runMiddleware(
            $this->oauthRequest(),
            redirect('https://app.example.com/callback?state=xyz')
        );

        $this->assertSame(
            'https://app.example.com/callback?state=xyz',
            $response->headers->get('Location')
        );
        $this->assertTrue(session('wallet_login'));
    }

    #[Test]
    public function it_leaves_oauth_redirects_with_empty_location_unchanged(): void
    {
        session(['wallet_login' => true]);

        $redirect = new Response('', 302);
        $redirect->headers->set('Location', '');

        $response = $this->runMiddleware(
            $this->oauthRequest(),
            $redirect
        );

        $this->assertSame('', $response->headers->get('Location'));
        $this->assertTrue(session('wallet_login'));
    }

    #[Test]
    public function it_leaves_oauth_redirects_without_route_unchanged(): void
    {
        session(['wallet_login' => true]);

        $request = Request::create('/oauth/authorize', 'GET');
        $request->setLaravelSession($this->app['session.store']);

        $response = $this->runMiddleware(
            $request,
            redirect('https://app.example.com/callback?code=abc')
        );

        $this->assertSame(
            'https://app.example.com/callback?code=abc',
            $response->headers->get('Location')
        );
        $this->assertTrue(session('wallet_login'));
    }

    private function runMiddleware(Request $request, Response $downstreamResponse): Response
    {
        return $this->middleware->handle($request, fn () => $downstreamResponse);
    }

    private function oauthRequest(string $routeName = 'passport.authorizations.authorize'): Request
    {
        $request = Request::create('/oauth/authorize', 'GET');
        $route = new Route('GET', '/oauth/authorize', []);
        $route->name($routeName);
        $request->setRouteResolver(fn () => $route);
        $request->setLaravelSession($this->app['session.store']);

        return $request;
    }
}
