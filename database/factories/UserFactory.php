<?php

namespace Database\Factories;

use App\Models\PersonalInfo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'code' => null,
            'referral' => null,
            'wallet_address' => null,
            'nonce' => null,
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (User $user) {
            if (! $user->personalInfo()->exists()) {
                PersonalInfo::factory()->create(['user_id' => $user->id]);
            }
        });
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Assign an SSO member code.
     */
    public function withCode(string $code = 'hm-2000001'): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => $code,
        ]);
    }

    /**
     * Create a wallet-only user (no email/password).
     */
    public function walletOnly(?string $address = null): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'User_' . Str::lower(Str::random(6)),
            'email' => null,
            'password' => null,
            'email_verified_at' => now(),
            'wallet_address' => $address ?? ('0x' . Str::lower(Str::random(40))),
            'code' => 'hm-' . str_pad((string) fake()->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
        ]);
    }
}
