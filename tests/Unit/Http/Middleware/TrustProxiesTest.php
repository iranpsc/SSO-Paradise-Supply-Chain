<?php

namespace Tests\Unit\Http\Middleware;

use App\Http\Middleware\TrustProxies as AppTrustProxies;
use Illuminate\Http\Middleware\TrustProxies;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use Tests\TestCase;

class TrustProxiesTest extends TestCase
{
    #[Test]
    public function application_bootstrap_configures_wildcard_proxy_trust(): void
    {
        $trusted = (new ReflectionClass(TrustProxies::class))
            ->getStaticPropertyValue('alwaysTrustProxies');

        $this->assertSame('*', $trusted);
    }

    #[Test]
    public function application_proxy_fallback_matches_the_private_network_list(): void
    {
        $proxies = (new ReflectionClass(AppTrustProxies::class))
            ->getDefaultProperties()['proxies'];

        $this->assertSame([
            '127.0.0.1',
            '10.0.0.0/8',
            '172.16.0.0/12',
            '192.168.0.0/16',
        ], $proxies);
    }
}
