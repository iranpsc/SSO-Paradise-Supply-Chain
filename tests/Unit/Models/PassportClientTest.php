<?php

namespace Tests\Unit\Models;

use App\Models\Passport\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PassportClientTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function skips_authorization_always_returns_true(): void
    {
        $client = new Client;
        $user = User::factory()->make();

        $this->assertTrue($client->skipsAuthorization($user, []));
    }

    #[Test]
    public function grant_types_uses_stored_json_when_present(): void
    {
        $client = new Client;
        $client->setRawAttributes([
            'id' => 1,
            'name' => 'JSON Grants',
            'secret' => 'secret',
            'redirect' => '',
            'personal_access_client' => false,
            'password_client' => false,
            'revoked' => false,
            'grant_types' => json_encode(['client_credentials', 'refresh_token']),
        ], true);

        $this->assertSame(['client_credentials', 'refresh_token'], $client->grant_types);
    }

    #[Test]
    public function grant_types_infers_personal_access_for_confidential_clients(): void
    {
        $client = Client::query()->create([
            'name' => 'Personal Access',
            'secret' => 'secret-value',
            'redirect' => '',
            'personal_access_client' => true,
            'password_client' => false,
            'revoked' => false,
        ]);

        $this->assertContains('personal_access', $client->grant_types);
    }

    #[Test]
    public function grant_types_infers_password_and_refresh_for_password_clients(): void
    {
        $client = Client::query()->create([
            'name' => 'Password Client',
            'secret' => 'secret-value',
            'redirect' => '',
            'personal_access_client' => false,
            'password_client' => true,
            'revoked' => false,
        ]);

        $this->assertSame(['password', 'refresh_token'], $client->grant_types);
    }

    #[Test]
    public function grant_types_infers_authorization_code_when_redirect_is_present(): void
    {
        $client = Client::query()->create([
            'name' => 'Auth Code Client',
            'secret' => 'secret-value',
            'redirect' => 'https://example.com/callback',
            'personal_access_client' => false,
            'password_client' => false,
            'revoked' => false,
        ]);

        $this->assertSame(['authorization_code', 'refresh_token'], $client->grant_types);
    }
}
