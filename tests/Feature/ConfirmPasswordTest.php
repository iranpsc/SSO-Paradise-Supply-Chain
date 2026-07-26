<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ConfirmPasswordTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'password' => Hash::make(self::VALID_PASSWORD),
        ]);
    }

    #[Test]
    public function authenticated_user_can_view_confirm_password_form(): void
    {
        $this->actingAs($this->user)
            ->get(route('password.confirm'))
            ->assertOk()
            ->assertViewIs('auth.passwords.confirm');
    }

    #[Test]
    public function guest_cannot_view_confirm_password_form(): void
    {
        $this->get(route('password.confirm'))
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function user_can_confirm_password_with_correct_password(): void
    {
        $this->actingAs($this->user)
            ->post(route('password.confirm'), [
                'password' => self::VALID_PASSWORD,
            ])
            ->assertRedirect('/home');

        $this->assertNotNull(session('auth.password_confirmed_at'));
        $this->assertEqualsWithDelta(time(), session('auth.password_confirmed_at'), 5);
    }

    #[Test]
    public function confirm_password_fails_with_incorrect_password(): void
    {
        $this->actingAs($this->user)
            ->from(route('password.confirm'))
            ->post(route('password.confirm'), [
                'password' => 'WrongPass!2024',
            ])
            ->assertRedirect(route('password.confirm'))
            ->assertSessionHasErrors('password');

        $this->assertNull(session('auth.password_confirmed_at'));
    }

    #[Test]
    public function confirm_password_requires_password_field(): void
    {
        $this->actingAs($this->user)
            ->from(route('password.confirm'))
            ->post(route('password.confirm'), [])
            ->assertRedirect(route('password.confirm'))
            ->assertSessionHasErrors('password');
    }

    #[Test]
    public function json_confirm_password_returns_204(): void
    {
        $this->actingAs($this->user)
            ->postJson(route('password.confirm'), [
                'password' => self::VALID_PASSWORD,
            ])
            ->assertNoContent();
    }

    #[Test]
    public function json_confirm_password_returns_validation_errors(): void
    {
        $this->actingAs($this->user)
            ->postJson(route('password.confirm'), [
                'password' => 'wrong',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('password');
    }
}
