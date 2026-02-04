<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Customize password reset notification URL
        ResetPassword::createUrlUsing(function ($user, string $token) {
            return url(route('password.reset', [
                'token' => $token,
                'email' => $user->getEmailForPasswordReset(),
            ], false));
        });

        // Global customization for password reset emails (fallback)
        ResetPassword::toMailUsing(function ($notifiable, $token) {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            $expireTime = config('auth.passwords.' . config('auth.defaults.passwords') . '.expire');

            return (new MailMessage)
                ->subject(__('Reset Your Password - Secure Account Access'))
                ->view('emails.password-reset', [
                    'user' => $notifiable,
                    'resetUrl' => $url,
                    'expireTime' => $expireTime,
                ])
                ->text('emails.password-reset-text', [
                    'user' => $notifiable,
                    'resetUrl' => $url,
                    'expireTime' => $expireTime,
                ]);
        });

        // Global customization for email verification (fallback)
        VerifyEmail::toMailUsing(function ($notifiable, $url) {
            $expireTime = config('auth.verification.expire', 60);

            return (new MailMessage)
                ->subject(__('Verify Your Email Address - Complete Your Registration'))
                ->view('emails.email-verification', [
                    'user' => $notifiable,
                    'verificationUrl' => $url,
                    'expireTime' => $expireTime,
                ])
                ->text('emails.email-verification-text', [
                    'user' => $notifiable,
                    'verificationUrl' => $url,
                    'expireTime' => $expireTime,
                ]);
        });

        Passport::enablePasswordGrant();

        Passport::tokensExpireIn(now()->addMinutes(60));
        Passport::refreshTokensExpireIn(now()->addMinutes(120));
        Passport::personalAccessTokensExpireIn(now()->addMinutes(60));
    }
}
