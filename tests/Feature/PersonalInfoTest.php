<?php

namespace Tests\Feature;

use App\Models\PersonalInfo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PersonalInfoTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->user = User::factory()->create([
            'email' => 'kyc@example.com',
        ]);
    }

    #[Test]
    public function verified_user_can_view_personal_info(): void
    {
        $this->actingAs($this->user)
            ->get(route('personal-info.show'))
            ->assertOk()
            ->assertViewIs('personal-info.show')
            ->assertViewHas('personalInfo');
    }

    #[Test]
    public function verified_user_can_view_personal_info_edit_form(): void
    {
        $this->actingAs($this->user)
            ->get(route('personal-info.edit'))
            ->assertOk()
            ->assertViewIs('personal-info.edit')
            ->assertViewHas('personalInfo');
    }

    #[Test]
    public function guest_cannot_view_or_update_personal_info(): void
    {
        $this->get(route('personal-info.show'))
            ->assertRedirect(route('login'));

        $this->get(route('personal-info.edit'))
            ->assertRedirect(route('login'));

        $this->put(route('personal-info.update'), $this->validPersonalInfoData())
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function unverified_user_cannot_access_personal_info(): void
    {
        $unverified = User::factory()->unverified()->create();

        $this->actingAs($unverified)
            ->get(route('personal-info.show'))
            ->assertRedirect(route('verification.notice'));

        $this->actingAs($unverified)
            ->put(route('personal-info.update'), $this->validPersonalInfoData())
            ->assertRedirect(route('verification.notice'));
    }

    #[Test]
    public function user_can_update_individual_personal_info_and_upload_kyc_scans(): void
    {
        $payload = $this->validPersonalInfoData([
            'first_name' => 'Sara',
            'last_name' => 'Ahmadi',
            'national_code' => '0860170470',
        ]);

        $this->actingAs($this->user)
            ->put(route('personal-info.update'), $payload)
            ->assertRedirect(route('personal-info.show'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('personal_infos', [
            'user_id' => $this->user->id,
            'is_company' => 0,
            'first_name' => 'Sara',
            'last_name' => 'Ahmadi',
            'mobile' => '09123456789',
            'telephone' => '02112345678',
            'national_code' => '0860170470',
            'address' => 'Tehran, Valiasr St.',
            'is_verified' => 0,
        ]);

        $personalInfo = $this->user->fresh()->personalInfo;

        $this->assertTrue($personalInfo->hasMedia('melli_card_scan'));
        $this->assertTrue($personalInfo->hasMedia('certificate_scan'));
        $this->assertTrue($personalInfo->hasMedia('bank_card_scan'));
    }

    #[Test]
    public function user_can_update_company_personal_info(): void
    {
        $this->actingAs($this->user)
            ->put(route('personal-info.update'), $this->validCompanyPersonalInfoData())
            ->assertRedirect(route('personal-info.show'));

        $this->assertDatabaseHas('personal_infos', [
            'user_id' => $this->user->id,
            'is_company' => 1,
            'company_name' => 'Acme Co',
            'company_address' => 'Tehran Industrial Zone',
            'company_registration_number' => '12345678',
            'company_national_number' => '10101234567',
            'company_tax_number' => '123456789012',
            'company_executive_name' => 'Ali Rezaei',
        ]);
    }

    #[Test]
    public function update_or_create_persists_when_personal_info_row_is_missing(): void
    {
        $this->user->personalInfo()->delete();
        $this->assertDatabaseMissing('personal_infos', ['user_id' => $this->user->id]);

        $this->actingAs($this->user->fresh())
            ->put(route('personal-info.update'), $this->validPersonalInfoData([
                'first_name' => 'Created',
            ]))
            ->assertRedirect(route('personal-info.show'));

        $this->assertDatabaseHas('personal_infos', [
            'user_id' => $this->user->id,
            'first_name' => 'Created',
        ]);

        $personalInfo = PersonalInfo::where('user_id', $this->user->id)->firstOrFail();
        $this->assertTrue($personalInfo->hasMedia('melli_card_scan'));
    }

    #[Test]
    public function reuploading_scans_replaces_previous_media_per_collection(): void
    {
        $this->actingAs($this->user)
            ->put(route('personal-info.update'), $this->validPersonalInfoData())
            ->assertRedirect(route('personal-info.show'));

        $personalInfo = $this->user->fresh()->personalInfo;
        $firstMelliId = $personalInfo->getFirstMedia('melli_card_scan')?->id;
        $firstCertId = $personalInfo->getFirstMedia('certificate_scan')?->id;
        $firstBankId = $personalInfo->getFirstMedia('bank_card_scan')?->id;

        $this->actingAs($this->user)
            ->put(route('personal-info.update'), $this->validPersonalInfoData([
                'melli_card_scan' => UploadedFile::fake()->image('melli2.jpg', 400, 300),
                'certificate_scan' => UploadedFile::fake()->image('cert2.jpg', 400, 300),
                'bank_card_scan' => UploadedFile::fake()->image('bank2.jpg', 400, 300),
            ]))
            ->assertRedirect(route('personal-info.show'));

        $fresh = $this->user->fresh()->personalInfo;

        $this->assertSame(1, $fresh->getMedia('melli_card_scan')->count());
        $this->assertSame(1, $fresh->getMedia('certificate_scan')->count());
        $this->assertSame(1, $fresh->getMedia('bank_card_scan')->count());
        $this->assertNotSame($firstMelliId, $fresh->getFirstMedia('melli_card_scan')?->id);
        $this->assertNotSame($firstCertId, $fresh->getFirstMedia('certificate_scan')?->id);
        $this->assertNotSame($firstBankId, $fresh->getFirstMedia('bank_card_scan')?->id);
    }

    #[Test]
    public function mass_assignment_cannot_force_kyc_verification_flags(): void
    {
        $this->actingAs($this->user)
            ->put(route('personal-info.update'), array_merge(
                $this->validPersonalInfoData(),
                [
                    'is_verified' => true,
                    'verification_messages' => ['approved' => true],
                    'user_id' => 999999,
                ]
            ))
            ->assertRedirect(route('personal-info.show'));

        $this->assertDatabaseHas('personal_infos', [
            'user_id' => $this->user->id,
            'is_verified' => 0,
        ]);

        $this->assertNull($this->user->fresh()->personalInfo->verification_messages);
        $this->assertDatabaseMissing('personal_infos', ['user_id' => 999999]);
    }

    #[Test]
    public function user_cannot_update_another_users_personal_info_via_idor(): void
    {
        $victim = User::factory()->create([
            'email' => 'victim-kyc@example.com',
        ]);
        $victim->personalInfo->update([
            'first_name' => 'Victim',
            'last_name' => 'User',
        ]);

        $this->actingAs($this->user)
            ->put(route('personal-info.update'), array_merge(
                $this->validPersonalInfoData(['first_name' => 'Attacker']),
                ['id' => $victim->personalInfo->id, 'user_id' => $victim->id]
            ))
            ->assertRedirect(route('personal-info.show'));

        $this->assertDatabaseHas('personal_infos', [
            'user_id' => $this->user->id,
            'first_name' => 'Attacker',
        ]);
        $this->assertDatabaseHas('personal_infos', [
            'user_id' => $victim->id,
            'first_name' => 'Victim',
            'last_name' => 'User',
        ]);
    }

    #[Test]
    #[DataProvider('requiredFieldProvider')]
    public function personal_info_requires_core_fields(string $field): void
    {
        $payload = $this->validPersonalInfoData();
        unset($payload[$field]);

        $this->actingAs($this->user)
            ->from(route('personal-info.edit'))
            ->put(route('personal-info.update'), $payload)
            ->assertRedirect(route('personal-info.edit'))
            ->assertSessionHasErrors($field);
    }

    public static function requiredFieldProvider(): array
    {
        return [
            'is_company' => ['is_company'],
            'first_name' => ['first_name'],
            'last_name' => ['last_name'],
            'mobile' => ['mobile'],
            'telephone' => ['telephone'],
            'national_code' => ['national_code'],
            'address' => ['address'],
            'melli_card_scan' => ['melli_card_scan'],
            'certificate_scan' => ['certificate_scan'],
            'bank_card_scan' => ['bank_card_scan'],
        ];
    }

    #[Test]
    #[DataProvider('invalidIranianFieldProvider')]
    public function personal_info_validates_iranian_identity_fields(
        string $field,
        mixed $value
    ): void {
        $this->actingAs($this->user)
            ->from(route('personal-info.edit'))
            ->put(route('personal-info.update'), $this->validPersonalInfoData([
                $field => $value,
            ]))
            ->assertRedirect(route('personal-info.edit'))
            ->assertSessionHasErrors($field);
    }

    public static function invalidIranianFieldProvider(): array
    {
        return [
            'mobile too short' => ['mobile', '0912345678'],
            'mobile too long' => ['mobile', '091234567890'],
            'mobile wrong prefix' => ['mobile', '08123456789'],
            'mobile with letters' => ['mobile', '0912abcdefg'],
            'telephone missing area code' => ['telephone', '12345678'],
            'telephone too short' => ['telephone', '0211234567'],
            'national code invalid checksum' => ['national_code', '1234567890'],
            'national code too short' => ['national_code', '001354241'],
            'first name too long' => ['first_name', str_repeat('a', 256)],
            'address too long' => ['address', str_repeat('a', 256)],
        ];
    }

    #[Test]
    #[DataProvider('companyRequiredFieldProvider')]
    public function company_fields_are_required_when_is_company_is_true(string $field): void
    {
        $payload = $this->validCompanyPersonalInfoData();
        $payload[$field] = null;

        $this->actingAs($this->user)
            ->from(route('personal-info.edit'))
            ->put(route('personal-info.update'), $payload)
            ->assertRedirect(route('personal-info.edit'))
            ->assertSessionHasErrors($field);
    }

    public static function companyRequiredFieldProvider(): array
    {
        return [
            'company_name' => ['company_name'],
            'company_address' => ['company_address'],
            'company_registration_number' => ['company_registration_number'],
            'company_national_number' => ['company_national_number'],
            'company_tax_number' => ['company_tax_number'],
            'company_executive_name' => ['company_executive_name'],
        ];
    }

    #[Test]
    public function company_fields_are_optional_when_is_company_is_false(): void
    {
        $this->actingAs($this->user)
            ->put(route('personal-info.update'), $this->validPersonalInfoData([
                'is_company' => false,
                'company_name' => null,
                'company_address' => null,
                'company_registration_number' => null,
                'company_national_number' => null,
                'company_tax_number' => null,
                'company_executive_name' => null,
            ]))
            ->assertRedirect(route('personal-info.show'))
            ->assertSessionDoesntHaveErrors();
    }

    #[Test]
    #[DataProvider('invalidScanProvider')]
    public function kyc_scans_must_be_valid_images(string $field, callable $fileFactory): void
    {
        $this->actingAs($this->user)
            ->from(route('personal-info.edit'))
            ->put(route('personal-info.update'), $this->validPersonalInfoData([
                $field => $fileFactory(),
            ]))
            ->assertRedirect(route('personal-info.edit'))
            ->assertSessionHasErrors($field);
    }

    public static function invalidScanProvider(): array
    {
        return [
            'melli pdf' => [
                'melli_card_scan',
                fn () => UploadedFile::fake()->create('melli.pdf', 100, 'application/pdf'),
            ],
            'certificate oversized' => [
                'certificate_scan',
                fn () => UploadedFile::fake()->image('cert.jpg')->size(2048),
            ],
            'bank card text file' => [
                'bank_card_scan',
                fn () => UploadedFile::fake()->create('bank.txt', 10, 'text/plain'),
            ],
        ];
    }

    #[Test]
    public function failed_validation_does_not_persist_personal_info_changes(): void
    {
        $this->user->personalInfo->update([
            'first_name' => 'Original',
            'last_name' => 'Name',
        ]);

        $this->actingAs($this->user)
            ->put(route('personal-info.update'), $this->validPersonalInfoData([
                'first_name' => 'Changed',
                'national_code' => 'invalid',
            ]))
            ->assertSessionHasErrors('national_code');

        $this->assertDatabaseHas('personal_infos', [
            'user_id' => $this->user->id,
            'first_name' => 'Original',
            'last_name' => 'Name',
        ]);
    }

    #[Test]
    public function sql_injection_in_national_code_is_rejected(): void
    {
        $this->actingAs($this->user)
            ->put(route('personal-info.update'), $this->validPersonalInfoData([
                'national_code' => "0013542419' OR '1'='1",
            ]))
            ->assertSessionHasErrors('national_code');
    }

    #[Test]
    public function xss_payload_in_name_fields_is_stored_escaped_in_views(): void
    {
        $xss = '<script>alert("xss")</script>';

        $this->actingAs($this->user)
            ->put(route('personal-info.update'), $this->validPersonalInfoData([
                'first_name' => $xss,
                'last_name' => $xss,
                'address' => $xss,
            ]))
            ->assertRedirect(route('personal-info.show'));

        $response = $this->actingAs($this->user)->get(route('personal-info.show'));

        $response->assertOk();
        $response->assertDontSee('<script>alert("xss")</script>', false);
        $response->assertSee('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', false);
    }

    #[Test]
    public function api_user_resource_exposes_full_name_only_when_kyc_verified(): void
    {
        $this->user->personalInfo->update([
            'first_name' => 'Verified',
            'last_name' => 'Person',
            'is_verified' => false,
        ]);

        $unverifiedResponse = $this->getJson('/api/users/' . $this->user->id);
        $unverifiedResponse->assertOk()
            ->assertJsonPath('data.name', $this->user->name)
            ->assertJsonMissingPath('data.password')
            ->assertJsonMissingPath('data.remember_token');

        // Support both wrapped and unwrapped resource shapes.
        $name = $unverifiedResponse->json('data.name') ?? $unverifiedResponse->json('name');
        $this->assertSame($this->user->name, $name);

        $this->user->personalInfo->update(['is_verified' => true]);

        $verifiedResponse = $this->getJson('/api/users/' . $this->user->id);
        $verifiedName = $verifiedResponse->json('data.name') ?? $verifiedResponse->json('name');
        $this->assertSame('Verified Person', $verifiedName);
    }

    #[Test]
    public function api_user_endpoint_does_not_expose_sensitive_fields(): void
    {
        $response = $this->getJson('/api/users/' . $this->user->id)->assertOk();

        $payload = $response->json('data') ?? $response->json();

        $this->assertArrayNotHasKey('password', $payload);
        $this->assertArrayNotHasKey('remember_token', $payload);
        $this->assertArrayNotHasKey('nonce', $payload);
        $this->assertArrayNotHasKey('national_code', $payload);
        $this->assertArrayNotHasKey('mobile', $payload);
    }
}
