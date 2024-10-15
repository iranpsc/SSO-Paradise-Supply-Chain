<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_can_register_receive_verification_email_login_logout_and_login_again()
    {
        // Disable sending real notifications
        Notification::fake();

        // Registration data
        $userData = [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'SecurePass!2024',
            'password_confirmation' => 'SecurePass!2024',
            'referral' => null,
            'client_id' => null,
            'redirect_uri' => null,
            'back_url' => null,
        ];

        // Send registration request
        $response = $this->post('/register', $userData);

        // Check redirect to home page
        $response->assertRedirect('/home');
        dump('User successfully registered and redirected to /home.');

        // Check that user is stored in the database
        $this->assertDatabaseHas('users', [
            'email' => 'testuser@example.com',
        ]);
        dump('User data is stored in the database.');

        // Check if verification email notification was sent
        $user = User::where('email', 'testuser@example.com')->first();
        Notification::assertSentTo(
            [$user], VerifyEmail::class
        );
        dump('Verification email sent successfully.');

        // Check that password is correctly hashed
        $this->assertTrue(Hash::check('SecurePass!2024', $user->password));
        dump('Password hashed and stored correctly.');

        // Check if the user is authenticated after registration
        $this->assertAuthenticatedAs($user);
        dump('User is successfully logged in after registration.');

        // Log out the user
        $this->post('/logout');
        $this->assertGuest();
        dump('User successfully logged out.');

        // Login again
        $loginData = [
            'email' => 'testuser@example.com',
            'password' => 'SecurePass!2024',
        ];

        // Send login request
        $response = $this->post('/login', $loginData);

        // Check redirect to home page after login
        $response->assertRedirect('/home');
        dump('User successfully logged in again after logout.');

        // Check if the user is authenticated again
        $this->assertAuthenticatedAs($user);
        dump('User is authenticated after re-login.');
    }

    /** @test */
    public function it_requires_a_valid_email()
    {
        // Invalid email data
        $userData = [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'SecurePass!2024',
            'password_confirmation' => 'SecurePass!2024',
            'redirect_uri' => null,
        ];

        // Send registration request with invalid email
        $response = $this->post('/register', $userData);

        // Check for email validation error
        $response->assertSessionHasErrors(['email']);
        dump('Email validation failed as expected.');
    }

    /** @test */
    public function it_requires_a_password_confirmation()
    {
        // Mismatching password confirmation data
        $userData = [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'SecurePass!2024',
            'password_confirmation' => 'wrong_password',
            'redirect_uri' => null,
        ];

        // Send registration request with incorrect password confirmation
        $response = $this->post('/register', $userData);

        // Check for password confirmation validation error
        $response->assertSessionHasErrors(['password']);
        dump('Password confirmation validation failed as expected.');
    }

    /** @test */
    public function it_requires_a_unique_email()
    {
        // Create a user with a duplicate email
        User::create([
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => Hash::make('SecurePass!2024'),
        ]);

        // Duplicate registration data
        $userData = [
            'name' => 'Test User',
            'email' => 'testuser@example.com',  // Duplicate email
            'password' => 'SecurePass!2024',
            'password_confirmation' => 'SecurePass!2024',
            'redirect_uri' => null,
        ];

        // Send registration request with duplicate email
        $response = $this->post('/register', $userData);

        // Check for unique email validation error
        $response->assertSessionHasErrors(['email']);
        dump('Duplicate email validation failed as expected.');
    }
}

