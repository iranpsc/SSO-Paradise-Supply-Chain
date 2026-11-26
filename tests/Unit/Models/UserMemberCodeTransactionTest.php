<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserMemberCodeTransactionTest extends TestCase
{
    #[Test]
    public function allocates_a_member_code_without_an_existing_transaction(): void
    {
        config(['database.connections.member_code_test' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]]);
        DB::setDefaultConnection('member_code_test');
        Schema::create('users', function (Blueprint $table): void {
            $table->string('code')->unique();
        });
        DB::table('users')->insert(['code' => 'hm-2000005']);

        $this->assertSame(0, DB::transactionLevel());
        $this->assertSame('hm-2000006', User::allocateMemberCode());
        $this->assertSame(0, DB::transactionLevel());
    }
}
