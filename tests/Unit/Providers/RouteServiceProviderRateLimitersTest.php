<?php

namespace Tests\Unit\Providers;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RouteServiceProviderRateLimitersTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function api_rate_limiter_keys_by_ip_for_guests(): void
    {
        $limiter = RateLimiter::limiter('api');

        $request = Request::create('/api/me', 'POST', server: [
            'REMOTE_ADDR' => '203.0.113.10',
        ]);

        $limit = $limiter($request);

        $this->assertInstanceOf(Limit::class, $limit);
        $this->assertSame(60, $limit->maxAttempts);
        $this->assertSame('203.0.113.10', $limit->key);
    }

    #[Test]
    public function api_rate_limiter_keys_by_user_id_when_authenticated(): void
    {
        $user = User::factory()->create();
        $limiter = RateLimiter::limiter('api');

        $request = Request::create('/api/me', 'POST');
        $request->setUserResolver(fn () => $user);

        $limit = $limiter($request);

        $this->assertInstanceOf(Limit::class, $limit);
        $this->assertEquals($user->id, $limit->key);
    }

    #[Test]
    public function web3_rate_limiter_keys_by_user_when_authenticated(): void
    {
        $user = User::factory()->create();
        $limiter = RateLimiter::limiter('web3');

        $request = Request::create('/web3/nonce', 'GET');
        $request->setUserResolver(fn () => $user);

        $limit = $limiter($request);

        $this->assertInstanceOf(Limit::class, $limit);
        $this->assertSame(10, $limit->maxAttempts);
        $this->assertSame('web3-user-' . $user->id, $limit->key);
    }
}
