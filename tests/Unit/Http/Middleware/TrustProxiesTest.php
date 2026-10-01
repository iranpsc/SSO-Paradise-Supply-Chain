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
    public function trusted_proxies_are_private_networks_and_not_a_wildcard(): void
    {
        $trusted = (new ReflectionClass(TrustProxies::class))
            ->getStaticPropertyValue('alwaysTrustProxies');

        $this->assertIsArray($trusted);
        $this->assertNotContains('*', $trusted);
        $this->assertSame([
            '127.0.0.1',
            '10.0.0.0/8',
            '172.16.0.0/12',
            '192.168.0.0/16',
        ], $trusted);
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
