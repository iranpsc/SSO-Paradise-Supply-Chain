<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class CustomResetPasswordNotification extends ResetPassword
{
    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $url = $this->resetUrl($notifiable);
        $expireTime = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return (new MailMessage)
            ->subject(__('Reset Your Password - Secure Account Access'))
            // Consolidated inline-styled Gmail-friendly template
            ->view('emails.password-reset-inline', [
                'user' => $notifiable,
                'resetUrl' => $url,
                'expireTime' => $expireTime,
                'emailType' => __('Password Reset'),
                'title' => __('Reset Your Password'),
            ])
            ->text('emails.password-reset-inline-text', [
                'user' => $notifiable,
                'resetUrl' => $url,
                'expireTime' => $expireTime,
            ]);
    }

    /**
     * Get the reset URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function resetUrl($notifiable)
    {
        return url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));
    }
}
