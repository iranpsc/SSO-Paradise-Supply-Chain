<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'testlogin@example.com',
            'password' => Hash::make(self::VALID_PASSWORD),
        ]);
    }

    #[Test]
    public function guest_can_view_login_form(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertViewIs('auth.login');
    }

    #[Test]
    public function authenticated_user_cannot_view_login_form(): void
    {
        $this->actingAs($this->user)
            ->get('/login')
            ->assertRedirect('/home');
    }

    #[Test]
    public function verified_user_can_login_with_correct_credentials(): void
    {
        $response = $this->post('/login', [
            'email' => 'testlogin@example.com',
            'password' => self::VALID_PASSWORD,
        ]);

        $response->assertRedirect('/home');
        $this->assertAuthenticatedAs($this->user);
    }

    #[Test]
    public function login_regenerates_session_id_to_prevent_fixation(): void
    {
        $this->startSession();
        $previousId = session()->getId();

        $this->post('/login', [
            'email' => 'testlogin@example.com',
            'password' => self::VALID_PASSWORD,
        ])->assertRedirect('/home');

        $this->assertNotSame($previousId, session()->getId());
        $this->assertAuthenticatedAs($this->user);
    }

    #[Test]
    public function unverified_user_is_redirected_to_verification_notice_after_login(): void
    {
        $unverified = User::factory()->unverified()->create([
            'email' => 'unverified@example.com',
            'password' => Hash::make(self::VALID_PASSWORD),
        ]);

        $this->post('/login', [
            'email' => 'unverified@example.com',
            'password' => self::VALID_PASSWORD,
        ])
            ->assertRedirect(route('verification.notice'));

        $this->assertAuthenticatedAs($unverified);
    }

    #[Test]
    public function unverified_user_is_always_redirected_to_verification_notice_ignoring_intended_url(): void
    {
        $unverified = User::factory()->unverified()->create([
            'email' => 'unverified-intended@example.com',
            'password' => Hash::make(self::VALID_PASSWORD),
        ]);

        $intended = url('/change-password');

        $this->withSession(['url.intended' => $intended])
            ->post('/login', [
                'email' => 'unverified-intended@example.com',
                'password' => self::VALID_PASSWORD,
            ])
            ->assertRedirect(route('verification.notice'));

        $this->assertAuthenticatedAs($unverified);
    }

    #[Test]
    public function login_respects_intended_url_for_verified_users(): void
    {
        $intended = url('/change-password');

        $this->withSession(['url.intended' => $intended])
            ->post('/login', [
                'email' => 'testlogin@example.com',
                'password' => self::VALID_PASSWORD,
            ])
            ->assertRedirect($intended);

        $this->assertAuthenticatedAs($this->user);
    }

    #[Test]
    public function user_cannot_login_with_incorrect_password(): void
    {
        $this->from('/login')
            ->post('/login', [
                'email' => 'testlogin@example.com',
                'password' => 'WrongPass!2024',
            ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors();

        $this->assertGuest();
    }

    #[Test]
    public function user_cannot_login_with_unknown_email(): void
    {
        $this->from('/login')
            ->post('/login', [
                'email' => 'missing@example.com',
                'password' => self::VALID_PASSWORD,
            ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors();

        $this->assertGuest();
    }

    #[Test]
    #[DataProvider('missingLoginFieldsProvider')]
    public function login_requires_email_and_password(array $payload, array $errors): void
    {
        $this->from('/login')
            ->post('/login', $payload)
            ->assertRedirect('/login')
            ->assertSessionHasErrors($errors);

        $this->assertGuest();
    }

    /**
     * @return array<string, array{0: array<string, mixed>, 1: array<int, string>}>
     */
    public static function missingLoginFieldsProvider(): array
    {
        return [
            'both missing' => [[], ['email', 'password']],
            'email missing' => [['password' => 'secret'], ['email']],
            'password missing' => [['email' => 'a@b.com'], ['password']],
            'email empty' => [['email' => '', 'password' => 'secret'], ['email']],
            'password empty' => [['email' => 'a@b.com', 'password' => ''], ['password']],
        ];
    }

    #[Test]
    public function remember_me_sets_remember_cookie_when_requested(): void
    {
        $response = $this->post('/login', [
            'email' => 'testlogin@example.com',
            'password' => self::VALID_PASSWORD,
            'remember' => 'on',
        ]);

        $response->assertRedirect('/home');
        $this->assertAuthenticatedAs($this->user);
        $response->assertCookie(auth()->guard()->getRecallerName());
    }

    #[Test]
    public function login_is_throttled_after_too_many_failed_attempts(): void
    {
        Event::fake([Lockout::class]);

        for ($i = 0; $i < 5; $i++) {
            $this->from('/login')->post('/login', [
                'email' => 'testlogin@example.com',
                'password' => 'WrongPass!2024',
            ]);
        }

        $this->from('/login')
            ->post('/login', [
                'email' => 'testlogin@example.com',
                'password' => 'WrongPass!2024',
            ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        Event::assertDispatched(Lockout::class);
        $this->assertGuest();
    }

    #[Test]
    public function throttled_user_cannot_login_even_with_correct_password(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'testlogin@example.com',
                'password' => 'WrongPass!2024',
            ]);
        }

        $this->from('/login')
            ->post('/login', [
                'email' => 'testlogin@example.com',
                'password' => self::VALID_PASSWORD,
            ])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    #[Test]
    public function authenticated_user_can_logout(): void
    {
        $this->actingAs($this->user)
            ->post('/logout')
            ->assertRedirect('/');

        $this->assertGuest();
    }

    #[Test]
    public function logout_invalidates_session_and_regenerates_csrf_token(): void
    {
        $this->actingAs($this->user);
        $this->startSession();
        $oldToken = session()->token();
        $oldId = session()->getId();

        $this->post('/logout')->assertRedirect('/');

        $this->assertGuest();
        $this->assertNotSame($oldId, session()->getId());
        $this->assertNotSame($oldToken, session()->token());
    }

    #[Test]
    public function logout_returns_204_for_json_requests(): void
    {
        $this->actingAs($this->user)
            ->postJson('/logout')
            ->assertNoContent();

        $this->assertGuest();
    }

    #[Test]
    public function guest_logout_is_idempotent_and_redirects_home_root(): void
    {
        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    #[Test]
    public function sql_injection_in_login_email_does_not_authenticate(): void
    {
        $this->from('/login')
            ->post('/login', [
                'email' => "testlogin@example.com' OR '1'='1",
                'password' => self::VALID_PASSWORD,
            ])
            ->assertRedirect('/login');

        $this->assertGuest();
    }

    #[Test]
    public function wallet_only_user_cannot_login_with_email_password(): void
    {
        User::factory()->walletOnly()->create([
            'email' => null,
            'password' => null,
        ]);

        $this->from('/login')
            ->post('/login', [
                'email' => 'wallet@example.com',
                'password' => self::VALID_PASSWORD,
            ])
            ->assertSessionHasErrors();

        $this->assertGuest();
    }

    #[Test]
    public function authenticated_user_cannot_post_login_again(): void
    {
        $this->actingAs($this->user)
            ->post('/login', [
                'email' => 'testlogin@example.com',
                'password' => self::VALID_PASSWORD,
            ])
            ->assertRedirect('/home');
    }

    #[Test]
    public function register_then_logout_then_login_as_verified_user_reaches_home(): void
    {
        // Simulate a user who registered and later verified their email.
        $user = User::factory()->create([
            'email' => 'cycle@example.com',
            'password' => Hash::make(self::VALID_PASSWORD),
        ]);

        $this->actingAs($user)->post('/logout')->assertRedirect('/');
        $this->assertGuest();

        $this->post('/login', [
            'email' => 'cycle@example.com',
            'password' => self::VALID_PASSWORD,
        ])->assertRedirect('/home');

        $this->assertAuthenticatedAs($user);
    }
}
