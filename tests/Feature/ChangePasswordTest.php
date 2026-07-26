<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ChangePasswordTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fakeUncompromisedPasswordCheck();

        $this->user = User::factory()->create([
            'email' => 'changepass@example.com',
            'password' => Hash::make(self::VALID_PASSWORD),
        ]);
    }

    #[Test]
    public function verified_user_can_view_change_password_form(): void
    {
        $this->actingAs($this->user)
            ->get(route('password.edit'))
            ->assertOk()
            ->assertViewIs('auth.passwords.edit');
    }

    #[Test]
    public function guest_cannot_view_change_password_form(): void
    {
        $this->get(route('password.edit'))
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function unverified_user_cannot_view_change_password_form(): void
    {
        $unverified = User::factory()->unverified()->create([
            'password' => Hash::make(self::VALID_PASSWORD),
        ]);

        $this->actingAs($unverified)
            ->get(route('password.edit'))
            ->assertRedirect(route('verification.notice'));
    }

    #[Test]
    public function guest_cannot_change_password(): void
    {
        $this->put(route('password.new'), [
            'current_password' => self::VALID_PASSWORD,
            'password' => self::NEW_VALID_PASSWORD,
            'password_confirmation' => self::NEW_VALID_PASSWORD,
        ])->assertRedirect(route('login'));
    }

    #[Test]
    public function verified_user_can_change_password_with_correct_current_password(): void
    {
        $this->actingAs($this->user)
            ->put(route('password.new'), [
                'current_password' => self::VALID_PASSWORD,
                'password' => self::NEW_VALID_PASSWORD,
                'password_confirmation' => self::NEW_VALID_PASSWORD,
            ])
            ->assertRedirect(route('password.edit'))
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check(self::NEW_VALID_PASSWORD, $this->user->fresh()->password));
        $this->assertAuthenticatedAs($this->user);
    }

    #[Test]
    public function password_is_hashed_and_never_stored_plaintext(): void
    {
        $this->actingAs($this->user)
            ->put(route('password.new'), [
                'current_password' => self::VALID_PASSWORD,
                'password' => self::NEW_VALID_PASSWORD,
                'password_confirmation' => self::NEW_VALID_PASSWORD,
            ])
            ->assertRedirect(route('password.edit'));

        $stored = $this->user->fresh()->getRawOriginal('password');

        $this->assertNotSame(self::NEW_VALID_PASSWORD, $stored);
        $this->assertTrue(Hash::check(self::NEW_VALID_PASSWORD, $stored));
    }

    #[Test]
    public function change_password_requires_current_password_when_user_has_password(): void
    {
        $this->actingAs($this->user)
            ->from(route('password.edit'))
            ->put(route('password.new'), [
                'password' => self::NEW_VALID_PASSWORD,
                'password_confirmation' => self::NEW_VALID_PASSWORD,
            ])
            ->assertRedirect(route('password.edit'))
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check(self::VALID_PASSWORD, $this->user->fresh()->password));
    }

    #[Test]
    public function change_password_fails_with_incorrect_current_password(): void
    {
        $this->actingAs($this->user)
            ->from(route('password.edit'))
            ->put(route('password.new'), [
                'current_password' => 'WrongPass!2024',
                'password' => self::NEW_VALID_PASSWORD,
                'password_confirmation' => self::NEW_VALID_PASSWORD,
            ])
            ->assertRedirect(route('password.edit'))
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check(self::VALID_PASSWORD, $this->user->fresh()->password));
    }

    #[Test]
    public function wallet_only_user_can_set_password_without_current_password(): void
    {
        $walletUser = User::factory()->walletOnly()->create([
            'email' => 'wallet-pass@example.com',
            'email_verified_at' => now(),
        ]);

        $this->assertNull($walletUser->password);

        $this->actingAs($walletUser)
            ->put(route('password.new'), [
                'password' => self::NEW_VALID_PASSWORD,
                'password_confirmation' => self::NEW_VALID_PASSWORD,
            ])
            ->assertRedirect(route('password.edit'))
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check(self::NEW_VALID_PASSWORD, $walletUser->fresh()->password));
    }

    #[Test]
    #[DataProvider('invalidNewPasswordProvider')]
    public function change_password_validates_new_password_rules(
        string $password,
        string $confirmation,
        array $errors
    ): void {
        $this->actingAs($this->user)
            ->from(route('password.edit'))
            ->put(route('password.new'), [
                'current_password' => self::VALID_PASSWORD,
                'password' => $password,
                'password_confirmation' => $confirmation,
            ])
            ->assertRedirect(route('password.edit'))
            ->assertSessionHasErrors($errors);

        $this->assertTrue(Hash::check(self::VALID_PASSWORD, $this->user->fresh()->password));
    }

    public static function invalidNewPasswordProvider(): array
    {
        return [
            'missing' => ['', '', ['password']],
            'unconfirmed' => [self::NEW_VALID_PASSWORD, 'Mismatch!2025', ['password']],
            'too short' => ['Ab1!xy', 'Ab1!xy', ['password']],
            'no uppercase' => ['securepass!2025', 'securepass!2025', ['password']],
            'no lowercase' => ['SECUREPASS!2025', 'SECUREPASS!2025', ['password']],
            'no number' => ['SecurePass!!!!', 'SecurePass!!!!', ['password']],
            'no symbol' => ['SecurePass2025', 'SecurePass2025', ['password']],
        ];
    }

    #[Test]
    public function compromised_password_is_rejected(): void
    {
        $this->mock(\Illuminate\Contracts\Validation\UncompromisedVerifier::class, function ($mock) {
            $mock->shouldReceive('verify')->once()->andReturn(false);
        });

        $this->actingAs($this->user)
            ->from(route('password.edit'))
            ->put(route('password.new'), [
                'current_password' => self::VALID_PASSWORD,
                'password' => 'Password1!',
                'password_confirmation' => 'Password1!',
            ])
            ->assertRedirect(route('password.edit'))
            ->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check(self::VALID_PASSWORD, $this->user->fresh()->password));
    }

    #[Test]
    public function mass_assignment_cannot_alter_unrelated_user_fields_via_change_password(): void
    {
        $originalEmail = $this->user->email;

        $this->actingAs($this->user)
            ->put(route('password.new'), [
                'current_password' => self::VALID_PASSWORD,
                'password' => self::NEW_VALID_PASSWORD,
                'password_confirmation' => self::NEW_VALID_PASSWORD,
                'email' => 'hacked@example.com',
                'name' => 'Hacker',
                'code' => 'hm-9999999',
            ])
            ->assertRedirect(route('password.edit'));

        $fresh = $this->user->fresh();

        $this->assertSame($originalEmail, $fresh->email);
        $this->assertNotSame('Hacker', $fresh->name);
        $this->assertNotSame('hm-9999999', $fresh->code);
        $this->assertTrue(Hash::check(self::NEW_VALID_PASSWORD, $fresh->password));
    }

    #[Test]
    public function sql_injection_in_current_password_does_not_change_password(): void
    {
        $this->actingAs($this->user)
            ->put(route('password.new'), [
                'current_password' => "' OR '1'='1",
                'password' => self::NEW_VALID_PASSWORD,
                'password_confirmation' => self::NEW_VALID_PASSWORD,
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check(self::VALID_PASSWORD, $this->user->fresh()->password));
    }

    #[Test]
    public function unverified_user_cannot_change_password(): void
    {
        $unverified = User::factory()->unverified()->create([
            'password' => Hash::make(self::VALID_PASSWORD),
        ]);

        $this->actingAs($unverified)
            ->put(route('password.new'), [
                'current_password' => self::VALID_PASSWORD,
                'password' => self::NEW_VALID_PASSWORD,
                'password_confirmation' => self::NEW_VALID_PASSWORD,
            ])
            ->assertRedirect(route('verification.notice'));

        $this->assertTrue(Hash::check(self::VALID_PASSWORD, $unverified->fresh()->password));
    }

    #[Test]
    public function changing_password_logs_out_other_devices(): void
    {
        $this->post('/login', [
            'email' => $this->user->email,
            'password' => self::VALID_PASSWORD,
        ])->assertRedirect('/home');

        $this->get('/home')->assertOk();

        $otherDeviceSession = session()->all();
        $this->assertArrayHasKey('password_hash_web', $otherDeviceSession);

        $this->flushSession();

        $this->post('/login', [
            'email' => $this->user->email,
            'password' => self::VALID_PASSWORD,
        ])->assertRedirect('/home');

        $this->put(route('password.new'), [
            'current_password' => self::VALID_PASSWORD,
            'password' => self::NEW_VALID_PASSWORD,
            'password_confirmation' => self::NEW_VALID_PASSWORD,
        ])
            ->assertRedirect(route('password.edit'))
            ->assertSessionHas('success');

        $this->assertAuthenticatedAs($this->user);
        $this->get('/home')->assertOk();

        $this->flushSession();

        $this->withSession($otherDeviceSession)
            ->get('/home')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    #[Test]
    public function changing_password_with_remember_cookie_calls_logout_other_devices(): void
    {
        $recaller = Auth::guard()->getRecallerName();

        $this->actingAs($this->user)
            ->withCookie($recaller, 'remember-cookie-value')
            ->put(route('password.new'), [
                'current_password' => self::VALID_PASSWORD,
                'password' => self::NEW_VALID_PASSWORD,
                'password_confirmation' => self::NEW_VALID_PASSWORD,
            ])
            ->assertRedirect(route('password.edit'))
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check(self::NEW_VALID_PASSWORD, $this->user->fresh()->password));
        $this->assertAuthenticatedAs($this->user);
    }
}
