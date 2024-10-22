<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Tests\TestCase;
use Illuminate\Support\Facades\URL;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_sends_verification_email_upon_registration()
    {
        // Fake notifications to prevent real emails from being sent
        Notification::fake();

        // Create a new user with unverified email
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);
        dump('User created with unverified email.');

        // Act as the user and request email verification resend
        $this->actingAs($user)
             ->post(route('verification.resend'))
             ->assertStatus(302);  // Assert redirect after resend
        dump('Verification email resent successfully.');

        // Assert that the notification was sent
        Notification::assertSentTo(
            [$user], VerifyEmail::class
        );
        dump('Verification email notification sent.');
    }

    /** @test */
    public function a_user_can_verify_email()
    {
        // Create a user with unverified email
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);
        dump('User created with unverified email.');

        // Fake notifications
        Notification::fake();

        // Generate a temporary signed route for email verification
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );
        dump('Temporary signed URL for email verification generated.');

        // Act as the user and hit the verification URL
        $response = $this->actingAs($user)->get($verificationUrl);

        // Assert the user was redirected to /home with back_url
        $response->assertRedirect('/home?back_url=');
        dump('User redirected to /home after email verification.');

        // Assert the user's email is now verified in the database
        $this->assertNotNull($user->fresh()->email_verified_at);
        dump('Email verified successfully in the database.');
    }

    /** @test */
    public function a_user_is_redirected_to_back_url_after_verification()
    {
        // Create a user with unverified email
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);
        dump('User created with unverified email.');

        // Fake notifications
        Notification::fake();

        // Set 'back_url' in the session
        session(['back_url' => 'https://rgb.irpsc.com/fa']);
        dump('Back URL set to https://rgb.irpsc.com/fa.');

        // Generate a temporary signed URL for email verification
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );
        dump('Temporary signed URL for email verification generated.');

        // Act as the user and send email verification request
        $response = $this->actingAs($user)->get($verificationUrl);

        // Assert the user was redirected to back_url after email verification
        $response->assertRedirect('http://localhost/home?back_url=https://rgb.irpsc.com/fa');
        dump('User redirected to http://localhost/home with back_url after email verification.');

        // Assert the user's email is now verified in the database
        $this->assertNotNull($user->fresh()->email_verified_at);
        dump('Email verified successfully in the database.');
    }
}
