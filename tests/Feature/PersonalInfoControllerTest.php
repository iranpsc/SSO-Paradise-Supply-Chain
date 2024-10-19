<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PersonalInfoControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_displays_personal_info_for_authenticated_user()
    {
        // Create a user and personal info for that user
        $user = User::factory()->create();
        $personalInfo = $user->personalInfo()->create([
            'first_name' => 'Ahmad',
            'last_name' => 'Bahramieh',
            'mobile' => '09121234567',
        ]);

        // Authenticate the user
        $this->actingAs($user);

        // Send request to show personal info page
        $response = $this->get('/personal-info');

        // Assert that the view is loaded with the user's personal info
        $response->assertStatus(200);
        $response->assertViewIs('personal-info.show');
        $response->assertViewHas('personalInfo', $user->personalInfo);
        dump('Personal info displayed successfully for the authenticated user.');
    }

    /** @test */
    public function it_displays_personal_info_edit_form_for_authenticated_user()
    {
        // Create a user and personal info for that user
        $user = User::factory()->create();
        $personalInfo = $user->personalInfo()->create([
            'first_name' => 'Ahmad',
            'last_name' => 'Bahramieh',
            'mobile' => '09121234567',
        ]);

        // Authenticate the user
        $this->actingAs($user);

        // Send request to edit personal info page
        $response = $this->get('/personal-info/edit');

        // Assert that the view is loaded with the user's personal info
        $response->assertStatus(200);
        $response->assertViewIs('personal-info.edit');
        $response->assertViewHas('personalInfo', $user->personalInfo);
        dump('Edit form displayed successfully for the authenticated user.');
    }

    /** @test */
    public function it_updates_personal_info_for_authenticated_user()
    {
        // Create a user and personal info for that user
        $user = User::factory()->create();
        $personalInfo = $user->personalInfo()->create([
            'first_name' => 'Ahmad',
            'last_name' => 'Bahramieh',
            'mobile' => '09121234567',
        ]);

        // Authenticate the user
        $this->actingAs($user);

        // Prepare updated data with valid values
        $updatedData = [
            'first_name' => 'UpdatedName',
            'last_name' => 'UpdatedLastName',
            'mobile' => '09121234568',
            'is_company' => 0,
            'telephone' => '02112345678',  // Correct phone number format
            'national_code' => '1234567890',  // Valid national code
            'address' => 'New Address',
            'melli_card_scan' => UploadedFile::fake()->image('melli_card_scan.jpg'),  // File
            'certificate_scan' => UploadedFile::fake()->image('certificate_scan.jpg'),  // File
            'bank_card_scan' => UploadedFile::fake()->image('bank_card_scan.jpg'),  // File
        ];

        // Send request to update personal info
        $response = $this->put('/personal-info', $updatedData);

        // Assert redirection after successful update
        $response->assertRedirect('/personal-info');
        $response->assertSessionHas('success', __('Your personal information has been updated successfully.'));

        // Check the database to see if the information has been updated
        $this->assertDatabaseHas('personal_infos', [
            'user_id' => $user->id,
            'first_name' => 'UpdatedName',
            'last_name' => 'UpdatedLastName',
            'mobile' => '09121234568',
        ]);

        dump('Personal info updated successfully for the authenticated user.');
    }

    /** @test */
    public function it_uploads_and_stores_melli_card_scan_for_authenticated_user()
    {
        // Create a user and personal info for that user
        $user = User::factory()->create();
        $personalInfo = $user->personalInfo()->create([
            'first_name' => 'Ahmad',
            'last_name' => 'Bahramieh',
        ]);

        // Authenticate the user
        $this->actingAs($user);

        // Mock file upload
        Storage::fake('local');
        $file = UploadedFile::fake()->image('melli_card_scan.jpg');

        // Prepare data with valid values
        $updatedData = [
            'first_name' => 'UpdatedName',
            'last_name' => 'UpdatedLastName',
            'mobile' => '09121234568',
            'is_company' => 0,
            'telephone' => '02112345678',  // Correct phone number format
            'national_code' => '1234567890',  // Valid national code
            'address' => 'New Address',
            'melli_card_scan' => $file,
            'certificate_scan' => UploadedFile::fake()->image('certificate_scan.jpg'),
            'bank_card_scan' => UploadedFile::fake()->image('bank_card_scan.jpg'),
        ];

        // Send request to update personal info with file
        $response = $this->put('/personal-info', $updatedData);

        // Assert redirection and success message
        $response->assertRedirect('/personal-info');
        $response->assertSessionHas('success', __('Your personal information has been updated successfully.'));

        // Check if the file was stored
        $this->assertTrue($user->personalInfo->hasMedia('melli_card_scan'));
        dump('Melli card scan uploaded and stored successfully.');
    }
}
