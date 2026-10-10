<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\CustomVerifyEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function unverified_user_can_view_verification_notice(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get(route('verification.notice'))
            ->assertOk();
    }

    #[Test]
    public function guest_cannot_view_verification_notice(): void
    {
        $this->get(route('verification.notice'))
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function registration_sends_custom_verification_notification(): void
    {
        Notification::fake();

        $this->post('/register', $this->validRegistrationData())->assertRedirect('/home');

        $user = User::where('email', 'testuser@example.com')->firstOrFail();

        Notification::assertSentTo($user, CustomVerifyEmailNotification::class);
    }

    #[Test]
    public function verification_email_does_not_html_escape_signed_url(): void
    {
        $user = User::factory()->unverified()->create();
        $mail = (new CustomVerifyEmailNotification)->toMail($user);

        $text = view($mail->view['text'], $mail->data())->render();
        $html = view($mail->view['html'], $mail->data())->render();

        // Signed URLs contain "&signature="; HTML-escaping to "&amp;signature=" breaks copy/paste validation.
        foreach ([$text, $html] as $body) {
            $this->assertStringContainsString('&signature=', $body);
            $this->assertStringNotContainsString('&amp;signature=', $body);
        }
    }

    #[Test]
    public function unverified_user_can_resend_verification_email(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->post(route('verification.resend'))
            ->assertRedirect();

        Notification::assertSentTo($user, CustomVerifyEmailNotification::class);
    }

    #[Test]
    public function already_verified_user_does_not_receive_resend_notification(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('verification.resend'))
            ->assertRedirect('/home');

        Notification::assertNothingSent();
    }

    #[Test]
    public function user_can_verify_email_with_valid_signed_url(): void
    {
        $user = User::factory()->unverified()->create();

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $this->actingAs($user)
            ->get($url)
            ->assertRedirect(route('home'));

        $user->refresh();

        $this->assertNotNull($user->email_verified_at);
        $this->assertNotNull($user->code);
        $this->assertStringStartsWith('hm-', $user->code);
    }

    #[Test]
    public function verification_assigns_incremental_member_code(): void
    {
        User::factory()->withCode('hm-2000005')->create();
        $user = User::factory()->unverified()->create();

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $this->actingAs($user)->get($url)->assertRedirect(route('home'));

        $this->assertSame('hm-2000006', $user->fresh()->code);
    }

    #[Test]
    public function verification_redirects_to_allowed_back_url_from_cache(): void
    {
        $user = User::factory()->unverified()->create();
        Cache::put('back_url_' . $user->id, 'https://metarang.com/app', now()->addHour());

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $this->actingAs($user)
            ->get($url)
            ->assertRedirect('https://metarang.com/app?verified=1');

        $this->assertNull(Cache::get('back_url_' . $user->id));
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    #[Test]
    public function verification_appends_verified_flag_with_ampersand_when_back_url_has_query(): void
    {
        $user = User::factory()->unverified()->create();
        Cache::put('back_url_'.$user->id, 'https://metarang.com/app?from=sso', now()->addHour());

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $this->actingAs($user)
            ->get($url)
            ->assertRedirect('https://metarang.com/app?from=sso&verified=1');
    }

    #[Test]
    public function verification_rejects_disallowed_back_url_domains(): void
    {
        $user = User::factory()->unverified()->create();
        Cache::put('back_url_' . $user->id, 'https://evil.example.com/phish', now()->addHour());

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $this->actingAs($user)
            ->get($url)
            ->assertRedirect(route('home'))
            ->assertSessionHas('warning', 'Invalid redirect URL.');

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    #[Test]
    public function verification_fails_with_invalid_hash(): void
    {
        $user = User::factory()->unverified()->create();

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('wrong@example.com')]
        );

        $this->actingAs($user)
            ->get($url)
            ->assertForbidden();

        $this->assertNull($user->fresh()->email_verified_at);
    }

    #[Test]
    public function verification_fails_without_valid_signature(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get(route('verification.verify', [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]))
            ->assertForbidden();

        $this->assertNull($user->fresh()->email_verified_at);
    }

    #[Test]
    public function signed_url_does_not_verify_a_different_authenticated_user(): void
    {
        $addressed = User::factory()->unverified()->create(['email' => 'addressed@example.com']);
        $authenticated = User::factory()->unverified()->create(['email' => 'authenticated@example.com']);

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $addressed->id, 'hash' => sha1($addressed->email)]
        );

        $this->actingAs($authenticated)
            ->get($url)
            ->assertForbidden();

        $this->assertNull($addressed->fresh()->email_verified_at);
        $this->assertNull($authenticated->fresh()->email_verified_at);
        $this->assertAuthenticatedAs($authenticated);
    }

    #[Test]
    public function guest_is_redirected_to_login_before_email_verification(): void
    {
        $user = User::factory()->unverified()->create();

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $this->get($url)->assertRedirect(route('login'));

        $this->assertNull($user->fresh()->email_verified_at);
        $this->assertGuest();
    }

    #[Test]
    public function verification_rejects_back_url_without_scheme_and_host(): void
    {
        $user = User::factory()->unverified()->create();
        Cache::put('back_url_' . $user->id, '/relative/path', now()->addHour());

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $this->actingAs($user)
            ->get($url)
            ->assertRedirect(route('home'))
            ->assertSessionHas('warning', 'Invalid redirect URL.');

        $this->assertNotNull($user->fresh()->email_verified_at);
    }
}
