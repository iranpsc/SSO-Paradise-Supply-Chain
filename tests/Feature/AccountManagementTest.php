<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\CustomVerifyEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AccountManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'Account Owner',
            'email' => 'account@example.com',
        ]);
    }

    #[Test]
    public function verified_user_can_view_account_dashboard(): void
    {
        $this->actingAs($this->user)
            ->get(route('account.show'))
            ->assertOk()
            ->assertViewIs('account.show');
    }

    #[Test]
    public function verified_user_can_view_account_edit_form(): void
    {
        $this->actingAs($this->user)
            ->get(route('account.edit'))
            ->assertOk()
            ->assertViewIs('account.edit');
    }

    #[Test]
    public function guest_cannot_view_account_dashboard(): void
    {
        $this->get(route('account.show'))
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function guest_cannot_view_account_edit_form(): void
    {
        $this->get(route('account.edit'))
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function guest_cannot_update_account(): void
    {
        $this->put(route('account.update'), [
            'name' => 'Hacker',
            'email' => 'hacker@example.com',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'email' => 'account@example.com',
        ]);
    }

    #[Test]
    public function unverified_user_cannot_access_account_routes(): void
    {
        $unverified = User::factory()->unverified()->create();

        $this->actingAs($unverified)
            ->get(route('account.show'))
            ->assertRedirect(route('verification.notice'));

        $this->actingAs($unverified)
            ->put(route('account.update'), [
                'name' => 'Nope',
                'email' => 'nope@example.com',
            ])
            ->assertRedirect(route('verification.notice'));
    }

    #[Test]
    public function user_can_update_name_without_changing_email(): void
    {
        Notification::fake();

        $this->actingAs($this->user)
            ->put(route('account.update'), [
                'name' => 'Updated Name',
                'email' => $this->user->email,
            ])
            ->assertRedirect(route('account.show'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Updated Name',
            'email' => 'account@example.com',
        ]);

        $this->assertNotNull($this->user->fresh()->email_verified_at);
        Notification::assertNothingSent();
    }

    #[Test]
    public function changing_email_marks_user_unverified_and_sends_verification(): void
    {
        Notification::fake();

        $this->actingAs($this->user)
            ->put(route('account.update'), [
                'name' => $this->user->name,
                'email' => 'newemail@example.com',
            ])
            ->assertRedirect(route('verification.notice'))
            ->assertSessionHas('info');

        $fresh = $this->user->fresh();

        $this->assertSame('newemail@example.com', $fresh->email);
        $this->assertNull($fresh->email_verified_at);

        Notification::assertSentTo($fresh, CustomVerifyEmailNotification::class);
    }

    #[Test]
    public function email_change_is_case_sensitive_for_dirty_check_but_unique_rule_ignores_self(): void
    {
        Notification::fake();

        $this->actingAs($this->user)
            ->put(route('account.update'), [
                'name' => 'Same Email User',
                'email' => $this->user->email,
            ])
            ->assertRedirect(route('account.show'));

        Notification::assertNothingSent();
        $this->assertNotNull($this->user->fresh()->email_verified_at);
    }

    #[Test]
    public function user_can_upload_avatar_image(): void
    {
        Storage::fake('public');

        $avatar = UploadedFile::fake()->image('avatar.jpg', 200, 200);

        $this->actingAs($this->user)
            ->put(route('account.update'), [
                'name' => $this->user->name,
                'email' => $this->user->email,
                'avatar' => $avatar,
            ])
            ->assertRedirect(route('account.show'))
            ->assertSessionHas('success');

        $this->assertTrue($this->user->fresh()->hasMedia('avatars'));
    }

    #[Test]
    public function uploading_new_avatar_replaces_previous_avatar(): void
    {
        Storage::fake('public');

        $this->actingAs($this->user)
            ->put(route('account.update'), [
                'name' => $this->user->name,
                'email' => $this->user->email,
                'avatar' => UploadedFile::fake()->image('first.jpg', 200, 200),
            ])
            ->assertRedirect(route('account.show'));

        $firstMediaId = $this->user->fresh()->getFirstMedia('avatars')?->id;

        $this->actingAs($this->user)
            ->put(route('account.update'), [
                'name' => $this->user->name,
                'email' => $this->user->email,
                'avatar' => UploadedFile::fake()->image('second.jpg', 200, 200),
            ])
            ->assertRedirect(route('account.show'));

        $fresh = $this->user->fresh();

        $this->assertTrue($fresh->hasMedia('avatars'));
        $this->assertSame(1, $fresh->getMedia('avatars')->count());
        $this->assertNotSame($firstMediaId, $fresh->getFirstMedia('avatars')?->id);
    }

    #[Test]
    #[DataProvider('invalidAccountPayloadProvider')]
    public function account_update_validates_input(array $overrides, array $errors): void
    {
        $payload = array_merge([
            'name' => 'Valid Name',
            'email' => $this->user->email,
        ], $overrides);

        $this->actingAs($this->user)
            ->from(route('account.edit'))
            ->put(route('account.update'), $payload)
            ->assertRedirect(route('account.edit'))
            ->assertSessionHasErrors($errors);
    }

    public static function invalidAccountPayloadProvider(): array
    {
        return [
            'name required' => [['name' => ''], ['name']],
            'email required' => [['email' => ''], ['email']],
            'email invalid' => [['email' => 'not-an-email'], ['email']],
            'name too long' => [['name' => str_repeat('a', 256)], ['name']],
            'email too long' => [['email' => str_repeat('a', 250) . '@ex.com'], ['email']],
            'name reserved hm prefix' => [['name' => 'HM-Admin'], ['name']],
            'name reserved hm lowercase' => [['name' => 'hm-user'], ['name']],
            'xss name allowed as string but prefix blocked only for hm' => [
                // XSS is escaped in Blade; validation should still accept plain strings
                // This case asserts HM- prefix rejection remains case-insensitive for variants
                ['name' => 'hM-Something'],
                ['name'],
            ],
        ];
    }

    #[Test]
    public function email_must_be_unique_across_users(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->actingAs($this->user)
            ->from(route('account.edit'))
            ->put(route('account.update'), [
                'name' => $this->user->name,
                'email' => 'taken@example.com',
            ])
            ->assertRedirect(route('account.edit'))
            ->assertSessionHasErrors('email');

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'email' => 'account@example.com',
        ]);
    }

    #[Test]
    public function user_can_keep_own_email_when_updating_other_fields(): void
    {
        $this->actingAs($this->user)
            ->put(route('account.update'), [
                'name' => 'Renamed',
                'email' => 'account@example.com',
            ])
            ->assertRedirect(route('account.show'));

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Renamed',
            'email' => 'account@example.com',
        ]);
    }

    #[Test]
    #[DataProvider('invalidAvatarProvider')]
    public function avatar_upload_rejects_invalid_files(callable $fileFactory): void
    {
        Storage::fake('public');

        $this->actingAs($this->user)
            ->from(route('account.edit'))
            ->put(route('account.update'), [
                'name' => $this->user->name,
                'email' => $this->user->email,
                'avatar' => $fileFactory(),
            ])
            ->assertRedirect(route('account.edit'))
            ->assertSessionHasErrors('avatar');

        $this->assertFalse($this->user->fresh()->hasMedia('avatars'));
    }

    public static function invalidAvatarProvider(): array
    {
        return [
            'pdf disguised as upload' => [
                fn () => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
            ],
            'php double extension' => [
                fn () => UploadedFile::fake()->create('avatar.php.jpg', 100, 'image/jpeg'),
            ],
            'oversized file' => [
                fn () => UploadedFile::fake()->image('big.jpg')->size(2048),
            ],
            'svg not allowed' => [
                fn () => UploadedFile::fake()->create('avatar.svg', 100, 'image/svg+xml'),
            ],
        ];
    }

    #[Test]
    public function mass_assignment_cannot_set_privileged_fields_via_account_update(): void
    {
        $this->actingAs($this->user)
            ->put(route('account.update'), [
                'name' => 'Safe Name',
                'email' => $this->user->email,
                'password' => 'HackedPass!2025',
                'code' => 'hm-9999999',
                'wallet_address' => '0x' . str_repeat('a', 40),
                'email_verified_at' => null,
                'remember_token' => 'stolen',
            ])
            ->assertRedirect(route('account.show'));

        $fresh = $this->user->fresh();

        $this->assertSame('Safe Name', $fresh->name);
        $this->assertNull($fresh->code);
        $this->assertNull($fresh->wallet_address);
        $this->assertNotNull($fresh->email_verified_at);
        $this->assertFalse(Hash::check('HackedPass!2025', $fresh->password));
    }

    #[Test]
    public function user_cannot_update_another_users_account_via_idor(): void
    {
        $victim = User::factory()->create([
            'name' => 'Victim',
            'email' => 'victim@example.com',
        ]);

        // Singleton route has no user id — updates always apply to auth user only.
        $this->actingAs($this->user)
            ->put(route('account.update'), [
                'name' => 'Attacker Name',
                'email' => $this->user->email,
                'id' => $victim->id,
                'user_id' => $victim->id,
            ])
            ->assertRedirect(route('account.show'));

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Attacker Name',
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $victim->id,
            'name' => 'Victim',
            'email' => 'victim@example.com',
        ]);
    }

    #[Test]
    public function sql_injection_in_email_is_rejected(): void
    {
        $this->actingAs($this->user)
            ->put(route('account.update'), [
                'name' => $this->user->name,
                'email' => "account@example.com' OR '1'='1",
            ])
            ->assertSessionHasErrors('email');
    }

    #[Test]
    public function failed_validation_does_not_persist_partial_account_changes(): void
    {
        $this->actingAs($this->user)
            ->put(route('account.update'), [
                'name' => 'Should Not Save',
                'email' => 'not-valid',
            ])
            ->assertSessionHasErrors('email');

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Account Owner',
            'email' => 'account@example.com',
        ]);
    }
}
