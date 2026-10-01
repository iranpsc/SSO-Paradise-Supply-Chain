<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

class UserMemberCodeTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        User::flushEventListeners();

        parent::tearDown();
    }

    #[Test]
    public function first_member_code_starts_at_two_million(): void
    {
        $user = User::factory()->create();

        $user->assignMemberCode();

        $this->assertSame('hm-2000000', $user->fresh()->code);
    }

    #[Test]
    public function member_code_increments_from_the_highest_numeric_suffix(): void
    {
        User::factory()->withCode('hm-2000005')->create();
        User::factory()->withCode('hm-999999')->create();
        $user = User::factory()->create();

        $user->assignMemberCode();

        $this->assertSame('hm-2000006', $user->fresh()->code);
    }

    #[Test]
    public function member_code_allocation_works_inside_an_open_transaction(): void
    {
        $user = User::factory()->create();

        DB::transaction(function () use ($user) {
            $user->assignMemberCode();
        });

        $this->assertSame('hm-2000000', $user->fresh()->code);
    }

    #[Test]
    public function member_code_retries_when_the_chosen_code_is_claimed(): void
    {
        $user = User::factory()->create();
        $stolen = false;

        User::saving(function (User $saving) use (&$stolen) {
            if ($stolen || ! $saving->isDirty('code') || $saving->code === null) {
                return;
            }

            $stolen = true;
            $code = $saving->code;
            User::withoutEvents(fn () => User::factory()->create(['code' => $code]));
        });

        $user->assignMemberCode();

        $this->assertSame('hm-2000001', $user->fresh()->code);
        $this->assertDatabaseHas('users', ['code' => 'hm-2000000']);
    }

    #[Test]
    public function member_code_allocation_fails_when_every_attempt_collides(): void
    {
        $user = User::factory()->create();

        User::saving(function (User $saving) {
            if (! $saving->isDirty('code') || $saving->code === null) {
                return;
            }

            $code = $saving->code;
            User::withoutEvents(fn () => User::factory()->create(['code' => $code]));
        });

        $this->expectException(RuntimeException::class);

        $user->assignMemberCode(2);
    }
}
