<?php

namespace Database\Factories;

use App\Models\PersonalInfo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PersonalInfo>
 */
class PersonalInfoFactory extends Factory
{
    protected $model = PersonalInfo::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'is_company' => false,
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'mobile' => '09123456789',
            'telephone' => '02112345678',
            'national_code' => '0013542419',
            'address' => fake()->streetAddress(),
            'company_name' => null,
            'company_address' => null,
            'company_registration_number' => null,
            'company_national_number' => null,
            'company_tax_number' => null,
            'company_executive_name' => null,
            'is_verified' => false,
            'verification_messages' => null,
        ];
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified' => true,
        ]);
    }

    public function company(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_company' => true,
            'company_name' => fake()->company(),
            'company_address' => fake()->address(),
            'company_registration_number' => (string) fake()->numerify('########'),
            'company_national_number' => (string) fake()->numerify('###########'),
            'company_tax_number' => (string) fake()->numerify('############'),
            'company_executive_name' => fake()->name(),
        ]);
    }
}
