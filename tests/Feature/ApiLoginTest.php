<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Passport;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApiLoginTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensurePersonalAccessClient();

        $this->user = User::factory()->create([
            'email' => 'apiuser@example.com',
            'password' => Hash::make(self::VALID_PASSWORD),
        ]);
    }

    #[Test]
    public function user_can_login_via_api_with_valid_credentials(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'apiuser@example.com',
            'password' => self::VALID_PASSWORD,
        ]);

        $response->assertOk()
            ->assertJsonStructure(['message', 'token'])
            ->assertJson([
                'message' => 'Login successful',
            ]);

        $this->assertNotEmpty($response->json('token'));
        $this->assertAuthenticatedAs($this->user);
    }

    #[Test]
    public function api_login_rejects_invalid_credentials(): void
    {
        $this->postJson('/api/login', [
            'email' => 'apiuser@example.com',
            'password' => 'WrongPass!2024',
        ])
            ->assertUnauthorized()
            ->assertJson(['message' => 'Invalid credentials']);

        $this->assertGuest();
    }

    #[Test]
    #[DataProvider('invalidApiLoginProvider')]
    public function api_login_validates_input(array $payload, array $errors): void
    {
        $this->postJson('/api/login', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors($errors);
    }

    /**
     * @return array<string, array{0: array<string, mixed>, 1: array<int, string>}>
     */
    public static function invalidApiLoginProvider(): array
    {
        return [
            'missing all' => [[], ['email', 'password']],
            'invalid email' => [['email' => 'not-email', 'password' => 'x'], ['email']],
            'missing password' => [['email' => 'a@b.com'], ['password']],
        ];
    }

    #[Test]
    public function api_login_is_throttled_after_too_many_failures(): void
    {
        Event::fake([Lockout::class]);

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/login', [
                'email' => 'apiuser@example.com',
                'password' => 'WrongPass!2024',
            ])->assertUnauthorized();
        }

        $this->postJson('/api/login', [
            'email' => 'apiuser@example.com',
            'password' => 'WrongPass!2024',
        ])->assertStatus(429);

        Event::assertDispatched(Lockout::class);
    }

    #[Test]
    public function authenticated_api_user_can_fetch_me_resource(): void
    {
        Passport::actingAs($this->user);

        $this->postJson('/api/me')
            ->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'name', 'code', 'avatar'],
            ])
            ->assertJsonPath('data.id', $this->user->id)
            ->assertJsonMissingPath('data.password')
            ->assertJsonMissingPath('data.remember_token')
            ->assertJsonMissingPath('data.nonce');
    }

    #[Test]
    public function guest_cannot_access_me_endpoint(): void
    {
        $this->postJson('/api/me')->assertUnauthorized();
    }

    #[Test]
    public function authenticated_api_user_can_logout_with_bearer_token(): void
    {
        $token = $this->postJson('/api/login', [
            'email' => 'apiuser@example.com',
            'password' => self::VALID_PASSWORD,
        ])->assertOk()->json('token');

        $this->withToken($token)
            ->postJson('/api/logout')
            ->assertOk()
            ->assertJson(['message' => 'Logged out successfully']);
    }

    #[Test]
    public function issued_api_token_can_access_protected_endpoints(): void
    {
        $token = $this->postJson('/api/login', [
            'email' => 'apiuser@example.com',
            'password' => self::VALID_PASSWORD,
        ])->json('token');

        $this->withToken($token)
            ->postJson('/api/me')
            ->assertOk()
            ->assertJsonPath('data.id', $this->user->id);
    }

    #[Test]
    public function logout_deletes_user_access_token_records(): void
    {
        $token = $this->postJson('/api/login', [
            'email' => 'apiuser@example.com',
            'password' => self::VALID_PASSWORD,
        ])->json('token');

        $this->assertDatabaseHas('oauth_access_tokens', [
            'user_id' => $this->user->id,
        ]);

        $this->withToken($token)
            ->postJson('/api/logout')
            ->assertOk();

        $this->assertDatabaseMissing('oauth_access_tokens', [
            'user_id' => $this->user->id,
        ]);
    }

    #[Test]
    public function guest_cannot_logout_via_api(): void
    {
        $this->postJson('/api/logout')->assertUnauthorized();
    }

    #[Test]
    public function api_user_endpoint_does_not_expose_password_hash(): void
    {
        Passport::actingAs($this->user);

        $response = $this->getJson('/api/user');

        $response->assertOk();
        $this->assertArrayNotHasKey('password', $response->json());
        $this->assertArrayNotHasKey('remember_token', $response->json());
        $this->assertArrayNotHasKey('nonce', $response->json());
    }

    #[Test]
    public function public_users_show_endpoint_does_not_expose_sensitive_fields(): void
    {
        $response = $this->getJson('/api/users/' . $this->user->id);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'name', 'code', 'avatar'],
            ])
            ->assertJsonMissingPath('data.password')
            ->assertJsonMissingPath('data.email')
            ->assertJsonMissingPath('data.wallet_address');
    }

    #[Test]
    public function sql_injection_payload_cannot_bypass_api_login(): void
    {
        $this->postJson('/api/login', [
            'email' => "apiuser@example.com' OR 1=1 --",
            'password' => self::VALID_PASSWORD,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);

        $this->assertGuest();
    }
}
