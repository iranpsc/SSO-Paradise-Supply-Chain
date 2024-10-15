<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ChangePasswordTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    // Set up method: Create a user before each test and mark the email as verified
    protected function setUp(): void
    {
        parent::setUp();

        // Create a user and set email as verified
        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'testlogin@example.com',
            'password' => Hash::make('zx987654321ZX!'),  // Current password
        ]);

        // Mark the email as verified
        $this->user->markEmailAsVerified();

        // Act as this user in the tests
        $this->actingAs($this->user);
    }

    /** @test */
    public function a_user_can_change_password_with_correct_current_password()
    {
        // Log the start of the test
        dump('Starting test: a user can change password with correct current password.');

        // Send request to change password with correct current password
        $response = $this->put('/change-password', [
            'current_password' => 'zx987654321ZX!',
            'password' => 'NewSecurePassword2024!',
            'password_confirmation' => 'NewSecurePassword2024!',
        ]);

        // Assert the response redirects to the change-password page
        $response->assertRedirect('/change-password');
        dump('Password changed successfully.');

        // Assert the new password is stored in the database
        $this->assertTrue(Hash::check('NewSecurePassword2024!', $this->user->fresh()->password));
        dump('New password is successfully stored in the database.');
    }

    /** @test */
    public function it_fails_to_change_password_with_incorrect_current_password()
    {
        // Log the start of the test
        dump('Starting test: it fails to change password with incorrect current password.');

        // Send request to change password with incorrect current password
        $response = $this->put('/change-password', [
            'current_password' => 'wrongpassword123',
            'password' => 'NewSecurePassword2024!',
            'password_confirmation' => 'NewSecurePassword2024!',
        ]);

        // Assert session has errors for the current_password field
        $response->assertSessionHasErrors('current_password');
        dump('Password change failed due to incorrect current password.');
    }

    /** @test */
    public function it_fails_to_change_password_with_non_matching_password_confirmation()
    {
        // Log the start of the test
        dump('Starting test: it fails to change password with non matching password confirmation.');

        // Send request to change password with non-matching password confirmation
        $response = $this->put('/change-password', [
            'current_password' => 'zx987654321ZX!',
            'password' => 'NewSecurePassword2024!',
            'password_confirmation' => 'NotMatchingPassword2024!',
        ]);

        // Assert session has errors for the password field
        $response->assertSessionHasErrors('password');
        dump('Password change failed due to non-matching password confirmation.');
    }
}
