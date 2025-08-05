<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class CustomVerifyEmailNotification extends VerifyEmail
{
    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);
        $expireTime = config('auth.verification.expire', 60);

        return (new MailMessage)
            ->subject(__('Verify Your Email Address - Complete Your Registration'))
            ->view('components.emails.email-verification', [
                'user' => $notifiable,
                'verificationUrl' => $verificationUrl,
                'expireTime' => $expireTime,
            ])
            ->text('components.emails.email-verification-text', [
                'user' => $notifiable,
                'verificationUrl' => $verificationUrl,
                'expireTime' => $expireTime,
            ]);
    }
}
