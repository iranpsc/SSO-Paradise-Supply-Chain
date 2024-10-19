<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;

class AccountControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_displays_account_dashboard_for_authenticated_user()
    {
        // Create a user
        $user = User::factory()->create();

        // Authenticate the user
        $this->actingAs($user);

        // Send request to show account dashboard
        $response = $this->get('/account');

        // Assert that the account dashboard is displayed
        $response->assertStatus(200);
        $response->assertViewIs('account.show');
        dump('Account dashboard displayed successfully for the authenticated user.');
    }

    /** @test */
    public function it_displays_account_edit_form_for_authenticated_user()
    {
        // Create a user
        $user = User::factory()->create();

        // Authenticate the user
        $this->actingAs($user);

        // Send request to show account edit form
        $response = $this->get('/account/edit');

        // Assert that the account edit form is displayed
        $response->assertStatus(200);
        $response->assertViewIs('account.edit');
        dump('Account edit form displayed successfully for the authenticated user.');
    }

    /** @test */
    public function it_updates_account_for_authenticated_user_and_sends_email_verification_if_email_changed()
    {
        // Disable actual email notifications
        Notification::fake();

        // Create a user
        $user = User::factory()->create(['email' => 'oldemail@example.com']);

        // Authenticate the user
        $this->actingAs($user);

        // Prepare updated data with a new email
        $updatedData = [
            'name' => 'Updated Name',
            'email' => 'newemail@example.com',
        ];

        // Send request to update the account
        $response = $this->put('/account', $updatedData);

        // Assert that the user is redirected to the email verification notice
        $response->assertRedirect(route('verification.notice'));
        $response->assertSessionHas('info', __('Your account has been updated! Please verify your new email address.'));

        // Assert that the user's email is updated but not verified
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'newemail@example.com',
            'email_verified_at' => null,  // Email should be unverified
        ]);

        // Assert that the email verification notification was sent
        Notification::assertSentTo([$user], VerifyEmail::class);

        dump('Account updated successfully and email verification sent for the new email.');
    }

    /** @test */
    public function it_updates_account_avatar_for_authenticated_user()
    {
        // Create a user with a verified email
        $user = User::factory()->create(['email_verified_at' => now()]);

        // Authenticate the user
        $this->actingAs($user);

        // Mock file upload
        Storage::fake('local');
        $file = UploadedFile::fake()->image('avatar.jpg');

        // Prepare updated data with an avatar
        $updatedData = [
            'name' => 'Updated Name',
            'email' => $user->email,  // Email remains unchanged
            'avatar' => $file,
        ];

        // Send request to update the account with an avatar
        $response = $this->put('/account', $updatedData);

        // Assert that the user is redirected to the account dashboard
        $response->assertRedirect('/account');
        $response->assertSessionHas('success', __('Your account has been updated!'));

        // Assert that the avatar was uploaded to the 'avatars' media collection
        $this->assertTrue($user->fresh()->hasMedia('avatars'));
        dump('Account avatar uploaded and updated successfully.');
    }
    /** @test */
public function it_does_not_send_verification_email_if_email_does_not_change()
{
    // Disable actual email notifications
    Notification::fake();

    // Create a user
    $user = User::factory()->create();

    // Authenticate the user
    $this->actingAs($user);

    // Prepare updated data with the same email
    $updatedData = [
        'name' => 'Updated Name',
        'email' => $user->email,  // Email remains unchanged
    ];

    // Send request to update the account
    $response = $this->put('/account', $updatedData);

    // Assert that the user is redirected to the account dashboard
    $response->assertRedirect('/account');
    $response->assertSessionHas('success', __('Your account has been updated!'));

    // Assert that the email verification notification was NOT sent
    Notification::assertNotSentTo([$user], VerifyEmail::class);

    dump('Account updated without sending email verification since email did not change.');
}

/** @test */
public function it_validates_required_fields()
{
    // Create a user
    $user = User::factory()->create();

    // Authenticate the user
    $this->actingAs($user);

    // Prepare data without required fields
    $updatedData = [
        'name' => '',
        'email' => '',
    ];

    // Send request to update the account
    $response = $this->put('/account', $updatedData);

    // Assert that validation errors are present
    $response->assertSessionHasErrors(['name', 'email']);
    dump('Validation errors for required fields are displayed.');
}

/** @test */
public function it_validates_email_format()
{
    // Create a user
    $user = User::factory()->create();

    // Authenticate the user
    $this->actingAs($user);

    // Prepare data with an invalid email
    $updatedData = [
        'name' => 'Valid Name',
        'email' => 'invalid-email',
    ];

    // Send request to update the account
    $response = $this->put('/account', $updatedData);

    // Assert that validation error for email format is present
    $response->assertSessionHasErrors(['email']);
    dump('Validation error for invalid email format is displayed.');
}

/** @test */
public function it_fails_if_avatar_is_not_an_image()
{
    // Create a user
    $user = User::factory()->create();

    // Authenticate the user
    $this->actingAs($user);

    // Mock file upload with a non-image file
    Storage::fake('local');
    $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

    // Prepare updated data with an invalid avatar file
    $updatedData = [
        'name' => 'Updated Name',
        'email' => $user->email,
        'avatar' => $file,
    ];

    // Send request to update the account
    $response = $this->put('/account', $updatedData);

    // Assert validation error for the avatar field
    $response->assertSessionHasErrors(['avatar']);
    dump('Validation error for uploading a non-image file as avatar is displayed.');
}

}
