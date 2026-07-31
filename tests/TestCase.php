<?php

namespace Tests;

use App\Models\Passport\Client;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Laravel\Passport\ClientRepository;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // Blade layouts use @vite; skip the manifest so feature tests do not
        // require npm run build / npm run dev.
        $this->withoutVite();
    }

    /**
     * Valid registration password that satisfies mixedCase + numbers + symbols.
     */
    protected const VALID_PASSWORD = 'SecurePass!2024';

    /**
     * Alternate strong password for reset / change-password flows.
     */
    protected const NEW_VALID_PASSWORD = 'NewSecurePass!2025';

    /**
     * Build a valid registration payload, with optional overrides.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function validRegistrationData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => self::VALID_PASSWORD,
            'password_confirmation' => self::VALID_PASSWORD,
        ], $overrides);
    }

    /**
     * Build a valid personal-info / KYC payload (includes required scan uploads).
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function validPersonalInfoData(array $overrides = []): array
    {
        return array_merge([
            'is_company' => false,
            'first_name' => 'Ali',
            'last_name' => 'Rezaei',
            'mobile' => '09123456789',
            'telephone' => '02112345678',
            'national_code' => '0013542419',
            'address' => 'Tehran, Valiasr St.',
            'company_name' => null,
            'company_address' => null,
            'company_registration_number' => null,
            'company_national_number' => null,
            'company_tax_number' => null,
            'company_executive_name' => null,
            'melli_card_scan' => UploadedFile::fake()->image('melli.jpg', 400, 300),
            'certificate_scan' => UploadedFile::fake()->image('certificate.jpg', 400, 300),
            'bank_card_scan' => UploadedFile::fake()->image('bank.jpg', 400, 300),
        ], $overrides);
    }

    /**
     * Build a valid company KYC payload.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function validCompanyPersonalInfoData(array $overrides = []): array
    {
        return $this->validPersonalInfoData(array_merge([
            'is_company' => true,
            'company_name' => 'Acme Co',
            'company_address' => 'Tehran Industrial Zone',
            'company_registration_number' => '12345678',
            'company_national_number' => '10101234567',
            'company_tax_number' => '123456789012',
            'company_executive_name' => 'Ali Rezaei',
        ], $overrides));
    }

    /**
     * Fake Have I Been Pwned so Password::uncompromised() does not hit the network.
     */
    protected function fakeUncompromisedPasswordCheck(): void
    {
        Http::fake([
            'api.pwnedpasswords.com/*' => Http::response('', 200),
        ]);
    }

    /**
     * Create an OAuth client with the given redirect URI(s).
     *
     * @param  string|array<int, string>  $redirectUris
     */
    protected function createOAuthClient(string|array $redirectUris = 'https://example.com/callback'): Client
    {
        $uris = (array) $redirectUris;

        return app(ClientRepository::class)->createAuthorizationCodeGrantClient(
            name: 'Test Client',
            redirectUris: $uris,
            confidential: true,
        );
    }

    /**
     * Ensure a Passport personal-access client exists (required for createToken()).
     *
     * OAuth keys are gitignored; generate them when missing so CI and fresh
     * local checkouts behave the same as a machine that already ran passport:keys.
     */
    protected function ensurePersonalAccessClient(): Client
    {
        $privateKey = storage_path('oauth-private.key');
        $publicKey = storage_path('oauth-public.key');

        if (! is_file($privateKey) || ! is_file($publicKey)) {
            Artisan::call('passport:keys', ['--force' => true]);
        }

        foreach ([$privateKey, $publicKey] as $path) {
            if (is_file($path)) {
                @chmod($path, 0600);
            }
        }

        return app(ClientRepository::class)->createPersonalAccessGrantClient(
            'Test Personal Access Client',
            'users',
        );
    }
}
