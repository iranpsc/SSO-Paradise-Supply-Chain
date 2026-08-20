<?php

namespace Tests\Feature;

use App\Models\Passport\Client;
use App\Models\User;
use Elliptic\EC;
use Illuminate\Foundation\Testing\RefreshDatabase;
use kornrunner\Keccak;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OAuthWalletLoginCallbackTest extends TestCase
{
    use RefreshDatabase;

    private EC $ec;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ec = new EC('secp256k1');
    }

    #[Test]
    public function oauth_callback_includes_wallet_login_true_after_wallet_authentication(): void
    {
        $client = $this->createOAuthClient('https://app.example.com/callback');

        $key = $this->ec->genKeyPair();
        $publicKey = $key->getPublic()->encode('hex');
        $address = '0x'.substr(Keccak::hash(hex2bin(substr($publicKey, 2)), 256), -40);

        $nonce = $this->getJson("/web3/nonce?address={$address}")->json('nonce');
        $signature = $this->signMessage($key, $nonce);

        $this->post('/web3/verify', [
            'address' => $address,
            'signature' => $signature,
        ])->assertRedirect();

        $this->assertTrue(session('wallet_login'));

        $response = $this->get($this->authorizeUrl($client));

        $response->assertRedirect();
        $location = $response->headers->get('Location');

        $this->assertStringStartsWith('https://app.example.com/callback', $location);
        $this->assertStringContainsString('code=', $location);
        $this->assertStringContainsString('wallet_login=true', $location);
        $this->assertFalse(session()->has('wallet_login'));
    }

    #[Test]
    public function oauth_callback_omits_wallet_login_after_email_authentication(): void
    {
        $client = $this->createOAuthClient('https://app.example.com/callback');

        $user = User::factory()->create([
            'password' => bcrypt(self::VALID_PASSWORD),
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => self::VALID_PASSWORD,
        ])->assertRedirect();

        $this->assertFalse(session()->has('wallet_login'));

        $response = $this->get($this->authorizeUrl($client));

        $response->assertRedirect();
        $location = $response->headers->get('Location');

        $this->assertStringStartsWith('https://app.example.com/callback', $location);
        $this->assertStringContainsString('code=', $location);
        $this->assertStringNotContainsString('wallet_login=', $location);
    }

    private function authorizeUrl(Client $client): string
    {
        return '/oauth/authorize?'.http_build_query([
            'client_id' => $client->id,
            'redirect_uri' => 'https://app.example.com/callback',
            'response_type' => 'code',
            'scope' => '',
            'state' => 'test-state',
        ]);
    }

    private function signMessage($key, string $nonce): string
    {
        $msgLength = strlen($nonce);
        $messagePrefix = "\x19Ethereum Signed Message:\n".$msgLength.$nonce;
        $msgHash = Keccak::hash($messagePrefix, 256);

        $sig = $key->sign($msgHash);
        $r = str_pad($sig->r->toString(16), 64, '0', STR_PAD_LEFT);
        $s = str_pad($sig->s->toString(16), 64, '0', STR_PAD_LEFT);
        $recoveryParam = $sig->recoveryParam;

        $halfN = '7FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF5D576E7357A4501DDFE92F46681B20A0';
        if (strcasecmp($s, $halfN) > 0) {
            $n = gmp_init('FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEBAAEDCE6AF48A03BBFD25E8CD0364141', 16);
            $sGmp = gmp_init($s, 16);
            $s = str_pad(gmp_strval(gmp_sub($n, $sGmp), 16), 64, '0', STR_PAD_LEFT);
            $recoveryParam ^= 1;
        }

        $v = dechex($recoveryParam + 27);

        return '0x'.$r.$s.$v;
    }
}
