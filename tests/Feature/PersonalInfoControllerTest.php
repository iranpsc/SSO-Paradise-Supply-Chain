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
            'telephone' => '02833674554',
            'national_code' => '1234567891',
            'address' => 'New Address',
            'melli_card_scan' => UploadedFile::fake()->image('melli_card_scan.jpg'),
            'certificate_scan' => UploadedFile::fake()->image('certificate_scan.jpg'),
            'bank_card_scan' => UploadedFile::fake()->image('bank_card_scan.jpg'),
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
public function it_uploads_and_stores_files_for_authenticated_user()
{
    // ساخت کاربر و اطلاعات شخصی مربوط به آن
    $user = User::factory()->create();
    $personalInfo = $user->personalInfo()->create([
        'first_name' => 'Ahmad',
        'last_name' => 'Bahramieh',
    ]);

    // احراز هویت کاربر
    $this->actingAs($user);

    // شبیه‌سازی آپلود فایل
    Storage::fake('local');
    
    // تعریف فایل‌های شبیه‌سازی شده
    $melliCardFile = UploadedFile::fake()->image('melli_card_scan.jpg');
    $certificateFile = UploadedFile::fake()->image('certificate_scan.jpg');
    $bankCardFile = UploadedFile::fake()->image('bank_card_scan.jpg');

    // آماده‌سازی داده‌های به‌روزرسانی همراه با فایل‌ها
    $updatedData = [
        'first_name' => 'UpdatedName',
        'last_name' => 'UpdatedLastName',
        'mobile' => '09121234568',
        'is_company' => 0,
        'telephone' => '02833674554',
        'national_code' => '1234567891',
        'address' => 'New Address',
        'melli_card_scan' => $melliCardFile,  // فایل اجباری melli_card_scan
        'certificate_scan' => $certificateFile,
        'bank_card_scan' => $bankCardFile,
    ];

    // ارسال درخواست برای به‌روزرسانی اطلاعات شخصی همراه با فایل‌ها
    $response = $this->put('/personal-info', $updatedData);

    // بررسی ریدایرکت و پیام موفقیت
    $response->assertRedirect('/personal-info');
    $response->assertSessionHas('success', __('Your personal information has been updated successfully.'));

    // بررسی وجود فایل‌ها در مجموعه مدیا
    $this->assertTrue($user->personalInfo->fresh()->hasMedia('melli_card_scan'));
    $this->assertTrue($user->personalInfo->fresh()->hasMedia('certificate_scan'));
    $this->assertTrue($user->personalInfo->fresh()->hasMedia('bank_card_scan'));

    // بررسی دستی فایل‌ها در فضای ذخیره‌سازی
    Storage::disk('local')->assertExists('melli_card_scan/' . $melliCardFile->hashName());
    Storage::disk('local')->assertExists('certificate_scan/' . $certificateFile->hashName());
    Storage::disk('local')->assertExists('bank_card_scan/' . $bankCardFile->hashName());

    dump('Files uploaded and stored successfully.');
}

    /** @test */
    public function it_returns_validation_error_if_data_is_invalid()
    {
        // Create a user
        $user = User::factory()->create();

        // Authenticate the user
        $this->actingAs($user);

        // Prepare invalid data (e.g. invalid mobile number and missing required fields)
        $invalidData = [
            'first_name' => '',
            'last_name' => '',
            'mobile' => 'invalid_mobile',
        ];

        // Send request to update personal info with invalid data
        $response = $this->put('/personal-info', $invalidData);

        // Assert that validation errors are returned
        $response->assertSessionHasErrors(['first_name', 'last_name', 'mobile']);

        dump('Validation errors returned as expected for invalid data.');
    }
}
