<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;
use Sentry\Laravel\Integration;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        $middleware->web(replace: [
            \Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class => \App\Http\Middleware\PreventRequestForgery::class,
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\AppendWalletLoginToOAuthCallback::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo('/home');

        // Invalidate other browser sessions when the password hash changes.
        $middleware->authenticateSessions();

        // Keep API auth session-capable and restore API rate limiting.
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Session\Middleware\StartSession::class,
        ]);
        $middleware->throttleApi();
    })
    ->withSchedule(function (Schedule $schedule): void {
        // Delete unverified users older than 24 hours daily at midnight.
        $schedule->command('users:delete-unverified')
            ->dailyAt('00:00')
            ->withoutOverlapping()
            ->onFailure(function () {
                Log::error('Failed to delete unverified users scheduled task');
            });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        Integration::handles($exceptions);
    })
    ->create();
