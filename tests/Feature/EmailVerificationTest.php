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
    public function user_cannot_verify_another_users_email_via_idor(): void
    {
        $victim = User::factory()->unverified()->create(['email' => 'victim@example.com']);
        $attacker = User::factory()->unverified()->create(['email' => 'attacker@example.com']);

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $victim->id, 'hash' => sha1($victim->email)]
        );

        $this->actingAs($attacker)->get($url)->assertForbidden();

        $this->assertNull($victim->fresh()->email_verified_at);
        $this->assertNull($attacker->fresh()->email_verified_at);
    }

    #[Test]
    public function guest_cannot_verify_email(): void
    {
        $user = User::factory()->unverified()->create();

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $this->get($url)->assertRedirect(route('login'));
        $this->assertNull($user->fresh()->email_verified_at);
    }
}
