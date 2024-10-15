<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\ResetPassword;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    // Create a user before each test
    protected function setUp(): void
    {
        parent::setUp();

        // Create a user in the database
        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'testlogin@example.com',
            'password' => Hash::make('zx987654321ZX!'),
        ]);
    }

    /** @test */
    public function it_sends_password_reset_email()
    {
        // Fake notifications to avoid sending real emails
        Notification::fake();

        // Send password reset request
        $response = $this->post('/password/email', [
            'email' => 'testlogin@example.com',
        ]);

        // Assert the reset link was sent successfully
        $response->assertStatus(302); // Redirection after email is sent
        dump('Password reset email sent.');

        // Check if the notification was sent
        Notification::assertSentTo(
            [$this->user], ResetPassword::class
        );
        dump('Password reset notification was sent.');
    }

    /** @test */
    public function it_allows_user_to_reset_password_with_valid_token()
    {
        // Generate a password reset token for the user
        $token = Password::createToken($this->user);

        // Send password reset request with the token
        $response = $this->post('/password/reset', [
            'email' => 'testlogin@example.com',
            'token' => $token,
            'password' => 'NewSecurePassword2024!',
            'password_confirmation' => 'NewSecurePassword2024!',
        ]);

        // Assert the password was reset successfully and the user was redirected
        $response->assertRedirect('/home');
        dump('Password reset successfully and user redirected to /home.');

        // Assert the password was actually changed
        $this->assertTrue(Hash::check('NewSecurePassword2024!', $this->user->fresh()->password));
        dump('Password changed successfully in the database.');
    }

    /** @test */
    public function it_fails_to_reset_password_with_invalid_token()
    {
        // Send password reset request with an invalid token
        $response = $this->post('/password/reset', [
            'email' => 'testlogin@example.com',
            'token' => 'invalid-token',
            'password' => 'NewSecurePassword2024!',
            'password_confirmation' => 'NewSecurePassword2024!',
        ]);

        // Assert that the password reset fails and the user is redirected back with errors
        $response->assertSessionHasErrors();
        dump('Password reset failed due to invalid token.');
    }
}
