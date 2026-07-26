<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\CustomVerifyEmailNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function guest_can_view_registration_form(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertViewIs('auth.register');
    }

    #[Test]
    public function authenticated_user_cannot_view_registration_form(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/register')
            ->assertRedirect('/home');
    }

    #[Test]
    public function user_can_register_with_valid_data(): void
    {
        Notification::fake();

        $response = $this->post('/register', $this->validRegistrationData());

        $response->assertRedirect('/home');

        $this->assertDatabaseHas('users', [
            'email' => 'testuser@example.com',
            'name' => 'Test User',
            'referral' => null,
        ]);

        $user = User::where('email', 'testuser@example.com')->firstOrFail();

        $this->assertTrue(Hash::check(self::VALID_PASSWORD, $user->password));
        $this->assertNull($user->email_verified_at);
        $this->assertNull($user->code);
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('personal_infos', ['user_id' => $user->id]);

        Notification::assertSentTo($user, CustomVerifyEmailNotification::class);
    }

    #[Test]
    public function registration_dispatches_registered_event(): void
    {
        Event::fake([Registered::class]);

        $this->post('/register', $this->validRegistrationData())->assertRedirect('/home');

        $user = User::where('email', 'testuser@example.com')->firstOrFail();

        Event::assertDispatched(
            Registered::class,
            fn (Registered $event) => $event->user->is($user)
        );
    }

    #[Test]
    public function registration_stores_back_url_in_cache_for_one_hour(): void
    {
        Notification::fake();

        $backUrl = 'https://metarang.com/dashboard';

        $this->post('/register', $this->validRegistrationData([
            'back_url' => $backUrl,
        ]))->assertRedirect('/home');

        $user = User::where('email', 'testuser@example.com')->firstOrFail();

        $this->assertSame($backUrl, Cache::get('back_url_' . $user->id));
    }

    #[Test]
    public function registration_stores_valid_referral_code(): void
    {
        Notification::fake();

        $referrer = User::factory()->withCode('hm-2000100')->create();

        $this->post('/register', $this->validRegistrationData([
            'referral' => $referrer->code,
        ]))->assertRedirect('/home');

        $this->assertDatabaseHas('users', [
            'email' => 'testuser@example.com',
            'referral' => 'hm-2000100',
        ]);
    }

    #[Test]
    public function registration_accepts_valid_client_id_and_matching_redirect_uri(): void
    {
        Notification::fake();

        $client = $this->createOAuthClient('https://app.example.com/callback');

        $this->post('/register', $this->validRegistrationData([
            'client_id' => $client->id,
            'redirect_uri' => 'https://app.example.com/callback',
        ]))->assertRedirect('/home');

        $this->assertDatabaseHas('users', ['email' => 'testuser@example.com']);
    }

    #[Test]
    public function registration_accepts_redirect_uri_from_comma_separated_client_redirects(): void
    {
        Notification::fake();

        $client = $this->createOAuthClient([
            'https://app.example.com/callback',
            'https://app.example.com/oauth',
        ]);

        $this->post('/register', $this->validRegistrationData([
            'client_id' => $client->id,
            'redirect_uri' => 'https://app.example.com/oauth',
        ]))->assertRedirect('/home');
    }

    #[Test]
    public function registration_allows_persian_names(): void
    {
        Notification::fake();

        $this->post('/register', $this->validRegistrationData([
            'name' => 'کاربر تستی',
        ]))->assertRedirect('/home');

        $this->assertDatabaseHas('users', [
            'name' => 'کاربر تستی',
            'email' => 'testuser@example.com',
        ]);
    }

    #[Test]
    public function missing_required_fields_fail_validation(): void
    {
        $this->from('/register')
            ->post('/register', [])
            ->assertRedirect('/register')
            ->assertSessionHasErrors(['name', 'email', 'password']);

        $this->assertDatabaseCount('users', 0);
        $this->assertGuest();
    }

    #[Test]
    #[DataProvider('invalidNameProvider')]
    public function name_validation_rejects_invalid_values(mixed $name, string $description): void
    {
        $this->from('/register')
            ->post('/register', $this->validRegistrationData(['name' => $name]))
            ->assertRedirect('/register')
            ->assertSessionHasErrors(['name']);

        $this->assertDatabaseCount('users', 0);
        $this->assertGuest();
    }

    /**
     * @return array<string, array{0: mixed, 1: string}>
     */
    public static function invalidNameProvider(): array
    {
        return [
            'null' => [null, 'null name'],
            'empty string' => ['', 'empty name'],
            'too long' => [str_repeat('a', 51), 'name longer than 50'],
            'HM- uppercase' => ['HM-Admin', 'HM- prefix'],
            'hm- lowercase' => ['hm-admin', 'hm- prefix'],
            'Hm- mixed' => ['Hm-Admin', 'Hm- prefix'],
            'hM- mixed' => ['hM-Admin', 'hM- prefix'],
        ];
    }

    #[Test]
    #[DataProvider('validNameProvider')]
    public function name_validation_accepts_valid_values(string $name): void
    {
        Notification::fake();

        $this->post('/register', $this->validRegistrationData([
            'name' => $name,
            'email' => Str::uuid() . '@example.com',
        ]))->assertRedirect('/home');

        $this->assertDatabaseHas('users', ['name' => $name]);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function validNameProvider(): array
    {
        return [
            'simple ascii' => ['John Doe'],
            'exactly 50 chars' => [str_repeat('a', 50)],
            'contains hm without prefix' => ['John hm-smith'],
            'starts with H without M-' => ['Happy User'],
        ];
    }

    #[Test]
    #[DataProvider('invalidEmailProvider')]
    public function email_validation_rejects_invalid_values(mixed $email): void
    {
        $this->from('/register')
            ->post('/register', $this->validRegistrationData(['email' => $email]))
            ->assertRedirect('/register')
            ->assertSessionHasErrors(['email']);

        $this->assertDatabaseCount('users', 0);
    }

    /**
     * @return array<string, array{0: mixed}>
     */
    public static function invalidEmailProvider(): array
    {
        return [
            'null' => [null],
            'empty' => [''],
            'missing at' => ['not-an-email'],
            'missing domain' => ['user@'],
            'spaces' => ['user @example.com'],
            'too long' => [str_repeat('a', 250) . '@x.com'],
        ];
    }

    #[Test]
    public function email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->from('/register')
            ->post('/register', $this->validRegistrationData([
                'email' => 'taken@example.com',
            ]))
            ->assertRedirect('/register')
            ->assertSessionHasErrors(['email']);

        $this->assertDatabaseCount('users', 1);
    }

    #[Test]
    #[DataProvider('invalidPasswordProvider')]
    public function password_validation_rejects_invalid_values(
        ?string $password,
        ?string $confirmation,
        string $expectedErrorField = 'password'
    ): void {
        $payload = $this->validRegistrationData([
            'password' => $password,
            'password_confirmation' => $confirmation,
        ]);

        $this->from('/register')
            ->post('/register', $payload)
            ->assertRedirect('/register')
            ->assertSessionHasErrors([$expectedErrorField]);

        $this->assertDatabaseCount('users', 0);
    }

    /**
     * @return array<string, array{0: ?string, 1: ?string, 2?: string}>
     */
    public static function invalidPasswordProvider(): array
    {
        return [
            'null' => [null, null],
            'empty' => ['', ''],
            'too short' => ['Ab1!', 'Ab1!'],
            'missing uppercase' => ['securepass!2024', 'securepass!2024'],
            'missing lowercase' => ['SECUREPASS!2024', 'SECUREPASS!2024'],
            'missing number' => ['SecurePass!', 'SecurePass!'],
            'missing symbol' => ['SecurePass2024', 'SecurePass2024'],
            'confirmation mismatch' => ['SecurePass!2024', 'DifferentPass!2024'],
            'too long' => [str_repeat('Aa1!', 11), str_repeat('Aa1!', 11)], // 44 chars
        ];
    }

    #[Test]
    public function password_at_max_length_is_accepted(): void
    {
        Notification::fake();

        // 40 characters matching complexity rules
        $password = 'Aa1!' . str_repeat('x', 36);

        $this->post('/register', $this->validRegistrationData([
            'password' => $password,
            'password_confirmation' => $password,
        ]))->assertRedirect('/home');

        $user = User::where('email', 'testuser@example.com')->firstOrFail();
        $this->assertTrue(Hash::check($password, $user->password));
    }

    #[Test]
    public function referral_must_exist_as_user_code(): void
    {
        $this->from('/register')
            ->post('/register', $this->validRegistrationData([
                'referral' => 'hm-9999999',
            ]))
            ->assertRedirect('/register')
            ->assertSessionHasErrors(['referral']);

        $this->assertDatabaseCount('users', 0);
    }

    #[Test]
    public function empty_referral_is_stored_as_null(): void
    {
        Notification::fake();

        $this->post('/register', $this->validRegistrationData([
            'referral' => '',
        ]))->assertRedirect('/home');

        $this->assertDatabaseHas('users', [
            'email' => 'testuser@example.com',
            'referral' => null,
        ]);
    }

    #[Test]
    public function client_id_must_exist(): void
    {
        $this->from('/register')
            ->post('/register', $this->validRegistrationData([
                'client_id' => 999999,
            ]))
            ->assertRedirect('/register')
            ->assertSessionHasErrors(['client_id']);
    }

    #[Test]
    public function redirect_uri_must_be_a_valid_url(): void
    {
        $this->from('/register')
            ->post('/register', $this->validRegistrationData([
                'redirect_uri' => 'not-a-url',
            ]))
            ->assertRedirect('/register')
            ->assertSessionHasErrors(['redirect_uri']);
    }

    #[Test]
    public function redirect_uri_must_belong_to_an_oauth_client(): void
    {
        $this->from('/register')
            ->post('/register', $this->validRegistrationData([
                'redirect_uri' => 'https://evil.example.com/callback',
            ]))
            ->assertRedirect('/register')
            ->assertSessionHasErrors(['redirect_uri']);
    }

    #[Test]
    public function redirect_uri_must_belong_to_the_given_client_id(): void
    {
        $clientA = $this->createOAuthClient('https://a.example.com/callback');
        $this->createOAuthClient('https://b.example.com/callback');

        $this->from('/register')
            ->post('/register', $this->validRegistrationData([
                'client_id' => $clientA->id,
                'redirect_uri' => 'https://b.example.com/callback',
            ]))
            ->assertRedirect('/register')
            ->assertSessionHasErrors(['redirect_uri']);
    }

    #[Test]
    public function back_url_must_be_a_valid_url(): void
    {
        $this->from('/register')
            ->post('/register', $this->validRegistrationData([
                'back_url' => 'javascript:alert(1)',
            ]))
            ->assertRedirect('/register')
            ->assertSessionHasErrors(['back_url']);
    }

    #[Test]
    public function mass_assignment_cannot_set_privileged_attributes_on_register(): void
    {
        Notification::fake();

        $this->post('/register', $this->validRegistrationData([
            'email_verified_at' => now()->toDateTimeString(),
            'code' => 'hm-hacked',
            'wallet_address' => '0x' . str_repeat('a', 40),
            'remember_token' => 'stolen-token',
            'id' => 99999,
        ]))->assertRedirect('/home');

        $user = User::where('email', 'testuser@example.com')->firstOrFail();

        $this->assertNull($user->email_verified_at);
        $this->assertNull($user->code);
        $this->assertNull($user->wallet_address);
        $this->assertNotSame(99999, $user->id);
    }

    #[Test]
    public function sql_injection_payload_in_email_does_not_compromise_registration(): void
    {
        $payload = "test' OR '1'='1@example.com";

        $this->from('/register')
            ->post('/register', $this->validRegistrationData([
                'email' => $payload,
            ]))
            ->assertSessionHasErrors(['email']);

        $this->assertDatabaseCount('users', 0);
    }

    #[Test]
    public function xss_payload_in_name_is_stored_without_executing_and_does_not_break_registration(): void
    {
        Notification::fake();

        $xssName = '<script>alert("xss")</script>';

        $this->post('/register', $this->validRegistrationData([
            'name' => $xssName,
        ]))->assertRedirect('/home');

        $this->assertDatabaseHas('users', [
            'name' => $xssName,
            'email' => 'testuser@example.com',
        ]);
    }

    #[Test]
    public function password_is_never_stored_in_plain_text(): void
    {
        Notification::fake();

        $this->post('/register', $this->validRegistrationData())->assertRedirect('/home');

        $user = User::where('email', 'testuser@example.com')->firstOrFail();

        $this->assertNotSame(self::VALID_PASSWORD, $user->getAttributes()['password']);
        $this->assertTrue(Hash::check(self::VALID_PASSWORD, $user->password));
    }

    #[Test]
    public function sensitive_attributes_are_hidden_when_user_is_serialized(): void
    {
        Notification::fake();

        $this->post('/register', $this->validRegistrationData())->assertRedirect('/home');

        $user = User::where('email', 'testuser@example.com')->firstOrFail();
        $array = $user->toArray();

        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('remember_token', $array);
        $this->assertArrayNotHasKey('nonce', $array);
    }

    #[Test]
    public function failed_validation_does_not_create_personal_info_or_cache_back_url(): void
    {
        $this->from('/register')
            ->post('/register', $this->validRegistrationData([
                'email' => 'bad-email',
                'back_url' => 'https://metarang.com/x',
            ]))
            ->assertSessionHasErrors(['email']);

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('personal_infos', 0);
    }

    #[Test]
    public function authenticated_user_cannot_register_again(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/register', $this->validRegistrationData([
                'email' => 'another@example.com',
            ]))
            ->assertRedirect('/home');

        $this->assertDatabaseMissing('users', ['email' => 'another@example.com']);
    }
}
