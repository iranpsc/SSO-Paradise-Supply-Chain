<?php

namespace Tests\Unit\Providers;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Messages\MailMessage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AppServiceProviderMailCallbacksTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function reset_password_create_url_callback_builds_reset_route(): void
    {
        $user = User::factory()->create(['email' => 'reset-url@example.com']);
        $notification = new ResetPassword('test-token-123');

        $method = new \ReflectionMethod(ResetPassword::class, 'resetUrl');
        $url = $method->invoke($notification, $user);

        $this->assertStringContainsString('test-token-123', $url);
        $this->assertStringContainsString(urlencode('reset-url@example.com'), $url);
        $this->assertStringContainsString('/password/reset/', $url);
    }

    #[Test]
    public function reset_password_to_mail_callback_returns_custom_mail_message(): void
    {
        $user = User::factory()->create(['email' => 'reset-mail@example.com']);
        $notification = new ResetPassword('mail-token-456');

        $mail = $notification->toMail($user);

        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertSame(__('Reset Your Password - Secure Account Access'), $mail->subject);
    }

    #[Test]
    public function verify_email_to_mail_callback_returns_custom_mail_message(): void
    {
        $user = User::factory()->unverified()->create(['email' => 'verify-mail@example.com']);
        $notification = new VerifyEmail;

        $mail = $notification->toMail($user);

        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertSame(
            __('Verify Your Email Address - Complete Your Registration'),
            $mail->subject
        );
    }
}
