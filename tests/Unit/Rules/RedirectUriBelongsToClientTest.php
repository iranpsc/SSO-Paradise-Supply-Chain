<?php

namespace Tests\Unit\Rules;

use App\Models\Passport\Client;
use App\Rules\RedirectUriBelongsToClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RedirectUriBelongsToClientTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Client::flushEventListeners();
        parent::tearDown();
    }

    #[Test]
    public function it_accepts_uri_when_redirect_attribute_is_an_array(): void
    {
        $client = Client::query()->create([
            'name' => 'Array Redirect Client',
            'secret' => 'secret',
            'redirect' => 'https://placeholder.example.com/callback',
            'personal_access_client' => false,
            'password_client' => false,
            'revoked' => false,
        ]);

        Client::retrieved(function (Client $model) {
            $attrs = $model->getAttributes();
            $attrs['redirect'] = ['https://array.example.com/callback'];
            $model->setRawAttributes($attrs, true);
        });

        $failed = false;
        $rule = new RedirectUriBelongsToClient;
        $rule->setData(['client_id' => $client->id]);
        $rule->validate('redirect_uri', 'https://array.example.com/callback', function () use (&$failed) {
            $failed = true;
        });

        $this->assertFalse($failed);
    }

    #[Test]
    public function it_rejects_uri_not_present_in_array_redirect(): void
    {
        $client = Client::query()->create([
            'name' => 'Array Redirect Client 2',
            'secret' => 'secret',
            'redirect' => 'https://placeholder.example.com/callback',
            'personal_access_client' => false,
            'password_client' => false,
            'revoked' => false,
        ]);

        Client::retrieved(function (Client $model) {
            $attrs = $model->getAttributes();
            $attrs['redirect'] = ['https://array.example.com/callback'];
            $model->setRawAttributes($attrs, true);
        });

        $failed = false;
        $rule = new RedirectUriBelongsToClient;
        $rule->setData(['client_id' => $client->id]);
        $rule->validate('redirect_uri', 'https://evil.example.com/callback', function () use (&$failed) {
            $failed = true;
        });

        $this->assertTrue($failed);
    }
}
