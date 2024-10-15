<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    // Create the user before each test
    protected function setUp(): void
    {
        parent::setUp();

        // Assuming the user already exists in the database
        $this->user = User::create([
            'name' => 'Test User Login',
            'email' => 'testlogin@example.com',
            'password' => Hash::make('zx987654321ZX!'),  // Hashed password
        ]);
    }

    /** @test */
    public function a_user_can_login_with_correct_credentials()
    {
        // Login credentials
        $loginData = [
            'email' => 'testlogin@example.com',
            'password' => 'zx987654321ZX!',
        ];

        // Send login request
        $response = $this->post('/login', $loginData);

        // Check redirect to home page after successful login
        $response->assertRedirect('/home');
        dump('User successfully logged in with correct credentials.');

        // Check if the user is authenticated
        $this->assertAuthenticatedAs($this->user);
        dump('User is authenticated after login.');
    }

    /** @test */
    public function a_user_cannot_login_with_incorrect_password()
    {
        // Login credentials with wrong password
        $loginData = [
            'email' => 'testlogin@example.com',
            'password' => 'wrongpassword123',
        ];

        // Send login request
        $response = $this->post('/login', $loginData);

        // Check that the login failed and the user was redirected back with errors
        $response->assertSessionHasErrors();
        dump('Login failed as expected due to incorrect password.');

        // Check that the user is not authenticated
        $this->assertGuest();
        dump('User is not authenticated due to incorrect password.');
    }

    /** @test */
    public function a_user_cannot_login_with_invalid_email()
    {
        // Login credentials with invalid email
        $loginData = [
            'email' => 'invalid@example.com',
            'password' => 'zx987654321ZX!',
        ];

        // Send login request
        $response = $this->post('/login', $loginData);

        // Check that the login failed and the user was redirected back with errors
        $response->assertSessionHasErrors();
        dump('Login failed as expected due to invalid email.');

        // Check that the user is not authenticated
        $this->assertGuest();
        dump('User is not authenticated due to invalid email.');
    }

    /** @test */
    public function a_user_can_logout_successfully()
    {
        // First login the user
        $this->actingAs($this->user);

        // Log out the user
        $response = $this->post('/logout');

        // Check that the user was logged out and redirected to '/'
        $response->assertRedirect('/');
        dump('User successfully logged out.');

        // Check that the user is no longer authenticated
        $this->assertGuest();
        dump('User is no longer authenticated after logout.');
    }
}
