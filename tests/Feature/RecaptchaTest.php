<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\RecaptchaService;

class RecaptchaTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_without_recaptcha_fails_when_enabled()
    {
        config(['recaptcha.enabled' => true]);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertSessionHasErrors('cf-turnstile-response');
    }

    public function test_login_without_recaptcha_fails_when_enabled()
    {
        config(['recaptcha.enabled' => true]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('cf-turnstile-response');
    }

    public function test_password_reset_without_recaptcha_fails_when_enabled()
    {
        config(['recaptcha.enabled' => true]);

        $response = $this->post('/password/email', [
            'email' => 'test@example.com',
        ]);

        $response->assertSessionHasErrors('cf-turnstile-response');
    }

    public function test_registration_with_recaptcha_succeeds_when_enabled()
    {
        config(['recaptcha.enabled' => true]);

        // Mock the recaptcha service to return true
        $this->mock(RecaptchaService::class, function ($mock) {
            $mock->shouldReceive('verify')->andReturn(true);
        });

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'cf-turnstile-response' => 'fake-token',
        ]);

        $response->assertRedirect('/home');
    }

    public function test_login_with_recaptcha_succeeds_when_enabled()
    {
        config(['recaptcha.enabled' => true]);

        // Create a user first
        $user = \App\Models\User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // Mock the recaptcha service to return true
        $this->mock(RecaptchaService::class, function ($mock) {
            $mock->shouldReceive('verify')->andReturn(true);
        });

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'cf-turnstile-response' => 'fake-token',
        ]);

        $response->assertRedirect('/home');
    }

    public function test_recaptcha_disabled_does_not_require_token()
    {
        config(['recaptcha.enabled' => false]);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect('/home');
    }

    public function test_recaptcha_verification_failure_returns_error()
    {
        config(['recaptcha.enabled' => true]);

        // Mock the recaptcha service to return false
        $this->mock(RecaptchaService::class, function ($mock) {
            $mock->shouldReceive('verify')->andReturn(false);
        });

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'cf-turnstile-response' => 'invalid-token',
        ]);

        $response->assertSessionHasErrors('cf-turnstile-response');
    }
}
