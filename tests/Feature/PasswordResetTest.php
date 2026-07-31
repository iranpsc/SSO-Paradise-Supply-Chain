<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\CustomResetPasswordNotification;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'resetuser@example.com',
            'password' => Hash::make(self::VALID_PASSWORD),
        ]);
    }

    #[Test]
    public function guest_can_view_forgot_password_form(): void
    {
        $this->get('/password/reset')
            ->assertOk()
            ->assertViewIs('auth.passwords.email');
    }

    #[Test]
    public function guest_can_view_reset_password_form_with_token(): void
    {
        $token = Password::createToken($this->user);

        $this->get('/password/reset/' . $token . '?email=' . urlencode($this->user->email))
            ->assertOk()
            ->assertViewIs('auth.passwords.reset')
            ->assertViewHas('token', $token)
            ->assertViewHas('email', $this->user->email);
    }

    #[Test]
    public function authenticated_user_can_still_view_forgot_password_form(): void
    {
        // Auth::routes() does not apply the guest middleware to password reset link routes.
        $this->actingAs($this->user)
            ->get('/password/reset')
            ->assertOk()
            ->assertViewIs('auth.passwords.email');
    }

    #[Test]
    public function password_reset_link_is_sent_for_existing_email(): void
    {
        Notification::fake();

        $this->post('/password/email', [
            'email' => $this->user->email,
        ])
            ->assertRedirect()
            ->assertSessionHas('status');

        Notification::assertSentTo($this->user, CustomResetPasswordNotification::class);

        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => $this->user->email,
        ]);
    }

    #[Test]
    public function password_reset_notification_contains_valid_reset_url(): void
    {
        Notification::fake();

        $this->post('/password/email', ['email' => $this->user->email]);

        Notification::assertSentTo(
            $this->user,
            CustomResetPasswordNotification::class,
            function (CustomResetPasswordNotification $notification) {
                $mail = $notification->toMail($this->user);
                $data = $mail->data();

                $this->assertArrayHasKey('resetUrl', $data);
                $this->assertStringContainsString('/password/reset/', $data['resetUrl']);
                $this->assertStringContainsString('email=' . urlencode($this->user->email), $data['resetUrl']);

                return true;
            }
        );
    }

    #[Test]
    public function password_reset_link_fails_for_unknown_email(): void
    {
        Notification::fake();

        $this->from('/password/reset')
            ->post('/password/email', [
                'email' => 'unknown@example.com',
            ])
            ->assertRedirect('/password/reset')
            ->assertSessionHasErrors('email');

        Notification::assertNothingSent();
    }

    #[Test]
    #[DataProvider('invalidEmailProvider')]
    public function password_reset_link_validates_email(array $payload, array $errors): void
    {
        $this->from('/password/reset')
            ->post('/password/email', $payload)
            ->assertRedirect('/password/reset')
            ->assertSessionHasErrors($errors);
    }

    public static function invalidEmailProvider(): array
    {
        return [
            'missing' => [[], ['email']],
            'empty' => [['email' => ''], ['email']],
            'invalid format' => [['email' => 'not-an-email'], ['email']],
            'sql injection attempt' => [['email' => "' OR 1=1 --"], ['email']],
        ];
    }

    #[Test]
    public function user_can_reset_password_with_valid_token(): void
    {
        Event::fake([PasswordReset::class]);

        $token = Password::createToken($this->user);

        $this->post('/password/reset', [
            'token' => $token,
            'email' => $this->user->email,
            'password' => self::NEW_VALID_PASSWORD,
            'password_confirmation' => self::NEW_VALID_PASSWORD,
        ])
            ->assertRedirect('/home')
            ->assertSessionHas('status');

        $this->assertTrue(Hash::check(self::NEW_VALID_PASSWORD, $this->user->fresh()->password));
        $this->assertAuthenticatedAs($this->user);
        Event::assertDispatched(
            PasswordReset::class,
            fn (PasswordReset $event) => $event->user->is($this->user)
        );
    }

    #[Test]
    public function successful_reset_invalidates_reset_token(): void
    {
        $token = Password::createToken($this->user);

        $this->post('/password/reset', [
            'token' => $token,
            'email' => $this->user->email,
            'password' => self::NEW_VALID_PASSWORD,
            'password_confirmation' => self::NEW_VALID_PASSWORD,
        ])->assertRedirect('/home');

        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => $this->user->email,
        ]);
    }

    #[Test]
    public function successful_reset_rotates_remember_token(): void
    {
        $previousRemember = $this->user->remember_token;
        $token = Password::createToken($this->user);

        $this->post('/password/reset', [
            'token' => $token,
            'email' => $this->user->email,
            'password' => self::NEW_VALID_PASSWORD,
            'password_confirmation' => self::NEW_VALID_PASSWORD,
        ])->assertRedirect('/home');

        $this->assertNotSame($previousRemember, $this->user->fresh()->remember_token);
    }

    #[Test]
    public function reset_fails_with_invalid_token(): void
    {
        $originalHash = $this->user->password;

        $this->from('/password/reset/invalid')
            ->post('/password/reset', [
                'token' => 'invalid-token',
                'email' => $this->user->email,
                'password' => self::NEW_VALID_PASSWORD,
                'password_confirmation' => self::NEW_VALID_PASSWORD,
            ])
            ->assertRedirect('/password/reset/invalid')
            ->assertSessionHasErrors('email');

        $this->assertSame($originalHash, $this->user->fresh()->password);
        $this->assertGuest();
    }

    #[Test]
    public function reset_fails_when_token_belongs_to_another_email(): void
    {
        $other = User::factory()->create(['email' => 'other@example.com']);
        $token = Password::createToken($other);

        $this->from('/password/reset/' . $token)
            ->post('/password/reset', [
                'token' => $token,
                'email' => $this->user->email,
                'password' => self::NEW_VALID_PASSWORD,
                'password_confirmation' => self::NEW_VALID_PASSWORD,
            ])
            ->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check(self::VALID_PASSWORD, $this->user->fresh()->password));
    }

    #[Test]
    public function reset_fails_when_token_is_expired(): void
    {
        $token = Password::createToken($this->user);

        DB::table('password_reset_tokens')
            ->where('email', $this->user->email)
            ->update(['created_at' => now()->subMinutes(config('auth.passwords.users.expire') + 1)]);

        $this->from('/password/reset/' . $token)
            ->post('/password/reset', [
                'token' => $token,
                'email' => $this->user->email,
                'password' => self::NEW_VALID_PASSWORD,
                'password_confirmation' => self::NEW_VALID_PASSWORD,
            ])
            ->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check(self::VALID_PASSWORD, $this->user->fresh()->password));
    }

    #[Test]
    public function reset_token_cannot_be_reused(): void
    {
        $token = Password::createToken($this->user);

        $this->post('/password/reset', [
            'token' => $token,
            'email' => $this->user->email,
            'password' => self::NEW_VALID_PASSWORD,
            'password_confirmation' => self::NEW_VALID_PASSWORD,
        ])->assertRedirect('/home');

        auth()->logout();

        $this->post('/password/reset', [
            'token' => $token,
            'email' => $this->user->email,
            'password' => 'AnotherPass!2026',
            'password_confirmation' => 'AnotherPass!2026',
        ])->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check(self::NEW_VALID_PASSWORD, $this->user->fresh()->password));
    }

    #[Test]
    #[DataProvider('invalidResetPayloadProvider')]
    public function reset_validates_payload(array $overrides, array $errors): void
    {
        $token = Password::createToken($this->user);

        $payload = array_merge([
            'token' => $token,
            'email' => $this->user->email,
            'password' => self::NEW_VALID_PASSWORD,
            'password_confirmation' => self::NEW_VALID_PASSWORD,
        ], $overrides);

        $this->from('/password/reset/' . $token)
            ->post('/password/reset', $payload)
            ->assertRedirect('/password/reset/' . $token)
            ->assertSessionHasErrors($errors);

        $this->assertTrue(Hash::check(self::VALID_PASSWORD, $this->user->fresh()->password));
    }

    public static function invalidResetPayloadProvider(): array
    {
        return [
            'missing token' => [['token' => ''], ['token']],
            'missing email' => [['email' => ''], ['email']],
            'invalid email' => [['email' => 'bad'], ['email']],
            'missing password' => [['password' => '', 'password_confirmation' => ''], ['password']],
            'unconfirmed password' => [
                ['password_confirmation' => 'DifferentPass!2025'],
                ['password'],
            ],
            'password too short' => [
                ['password' => 'Ab1!', 'password_confirmation' => 'Ab1!'],
                ['password'],
            ],
        ];
    }

    #[Test]
    public function reset_link_request_is_throttled(): void
    {
        Notification::fake();

        $this->post('/password/email', ['email' => $this->user->email])
            ->assertSessionHas('status');

        $this->from('/password/reset')
            ->post('/password/email', ['email' => $this->user->email])
            ->assertRedirect('/password/reset')
            ->assertSessionHasErrors('email');
    }

    #[Test]
    public function wallet_only_user_without_email_cannot_request_reset(): void
    {
        Notification::fake();

        User::factory()->walletOnly()->create();

        $this->from('/password/reset')
            ->post('/password/email', ['email' => ''])
            ->assertSessionHasErrors('email');

        Notification::assertNothingSent();
    }

    #[Test]
    public function xss_payload_in_email_is_rejected_by_validation(): void
    {
        $this->post('/password/email', [
            'email' => '<script>alert(1)</script>@example.com',
        ])->assertSessionHasErrors('email');
    }

    #[Test]
    public function password_reset_logs_out_other_devices(): void
    {
        $this->post('/login', [
            'email' => $this->user->email,
            'password' => self::VALID_PASSWORD,
        ])->assertRedirect('/home');

        $this->get('/home')->assertOk();

        $otherDeviceSession = session()->all();
        $this->assertArrayHasKey('password_hash_web', $otherDeviceSession);

        auth()->logout();
        $this->flushSession();
        $this->assertGuest();

        $token = Password::createToken($this->user);

        $this->post('/password/reset', [
            'token' => $token,
            'email' => $this->user->email,
            'password' => self::NEW_VALID_PASSWORD,
            'password_confirmation' => self::NEW_VALID_PASSWORD,
        ])
            ->assertRedirect('/home')
            ->assertSessionHas('status');

        $this->assertAuthenticatedAs($this->user);
        $this->get('/home')->assertOk();

        $this->flushSession();

        $this->withSession($otherDeviceSession)
            ->get('/home')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
