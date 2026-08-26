<?php

namespace Tests\Unit\Services;

use App\Exceptions\MetarangWalletLookupException;
use App\Services\MetarangWalletClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MetarangWalletClientTest extends TestCase
{
    #[Test]
    public function it_posts_the_wallet_address_to_the_metarang_registered_endpoint(): void
    {
        Http::fake([
            'https://metarang.test/api/wallets/registered' => Http::response([
                'already_registered' => false,
                'user_code' => 'hm-123',
            ], 200),
        ]);

        $result = app(MetarangWalletClient::class)->lookupRegistration('0x00');

        $this->assertFalse($result['already_registered']);
        $this->assertSame('hm-123', $result['user_code']);

        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://metarang.test/api/wallets/registered'
                && $request->method() === 'POST'
                && $request['wallet_address'] === '0x00';
        });
    }

    #[Test]
    public function it_returns_the_user_code_when_the_wallet_is_already_registered(): void
    {
        Http::fake([
            '*/api/wallets/registered' => Http::response([
                'already_registered' => true,
                'user_code' => 'hm-123',
            ], 200),
        ]);

        $result = app(MetarangWalletClient::class)->lookupRegistration('0xabc');

        $this->assertTrue($result['already_registered']);
        $this->assertSame('hm-123', $result['user_code']);
    }

    #[Test]
    public function it_fails_when_the_api_is_not_configured(): void
    {
        config(['services.metarang.url' => null]);

        $this->expectException(MetarangWalletLookupException::class);
        $this->expectExceptionMessage('Metarang API is not configured.');

        app(MetarangWalletClient::class)->lookupRegistration('0x00');
    }

    #[Test]
    public function it_fails_when_the_upstream_request_returns_an_error_status(): void
    {
        Http::fake([
            '*/api/wallets/registered' => Http::response(['message' => 'error'], 500),
        ]);

        $this->expectException(MetarangWalletLookupException::class);
        $this->expectExceptionMessage('Unable to verify wallet registration.');

        app(MetarangWalletClient::class)->lookupRegistration('0x00');
    }

    #[Test]
    public function it_fails_when_already_registered_is_missing_a_user_code(): void
    {
        Http::fake([
            '*/api/wallets/registered' => Http::response([
                'already_registered' => true,
                'user_code' => '',
            ], 200),
        ]);

        $this->expectException(MetarangWalletLookupException::class);
        $this->expectExceptionMessage('Metarang did not return a user code for the registered wallet.');

        app(MetarangWalletClient::class)->lookupRegistration('0x00');
    }

    #[Test]
    public function it_fails_when_the_response_payload_is_invalid(): void
    {
        Http::fake([
            '*/api/wallets/registered' => Http::response(['foo' => 'bar'], 200),
        ]);

        $this->expectException(MetarangWalletLookupException::class);
        $this->expectExceptionMessage('Invalid Metarang wallet registration response.');

        app(MetarangWalletClient::class)->lookupRegistration('0x00');
    }
}
