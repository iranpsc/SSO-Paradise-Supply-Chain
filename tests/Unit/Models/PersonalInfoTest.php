<?php

namespace Tests\Unit\Models;

use App\Models\PersonalInfo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PersonalInfoTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_belongs_to_a_user(): void
    {
        $user = User::factory()->create();
        $info = PersonalInfo::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($info->user->is($user));
        $this->assertInstanceOf(User::class, $info->user()->first());
    }
}
