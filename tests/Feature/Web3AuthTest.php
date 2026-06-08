<?php

namespace Tests\Feature;

use Illuminate\Container\Attributes\Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Elliptic\EC;
use kornrunner\Keccak;

class Web3AuthTest extends TestCase
{
    use RefreshDatabase;

    private EC $ec;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ec = new EC('secp256k1');
    }

    /** @test */
    public function it_generates_nonce_without_creating_user_in_database()
    {
        $address = '0x90f8bfac9c63c35718a7a77e94b002d274950e89';

        $response = $this->getJson("/web3/nonce?address={$address}");

        $response->assertStatus(200);
        $response->assertJsonStructure(['nonce']);

        $nonce = $response->json('nonce');
        $this->assertStringContainsString('Sign this message to authenticate with ' . config('app.name') . ':', $nonce);

        // Verify the nonce is cached
        $this->assertEquals($nonce, Cache::get("web3_nonce_{$address}"));

        // Verify no user has been created
        $this->assertDatabaseMissing('users', [
            'wallet_address' => $address,
        ]);
    }

    /** @test */
    public function it_fails_nonce_generation_with_invalid_address()
    {
        $response = $this->getJson('/web3/nonce?address=invalid-eth-address');

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['address']);
    }

    /** @test */
    public function it_authenticates_successfully_with_valid_signature()
    {
        // 1. Generate keys and address
        $key = $this->ec->genKeyPair();
        $privateKey = $key->getPrivate('hex');
        $publicKey = $key->getPublic()->encode('hex');
        $address = '0x' . substr(Keccak::hash(hex2bin(substr($publicKey, 2)), 256), -40);

        // 2. Request nonce
        $nonceResponse = $this->getJson("/web3/nonce?address={$address}");
        $nonceResponse->assertStatus(200);
        $nonce = $nonceResponse->json('nonce');

        // 3. Generate signature locally using the private key
        $msgLength = strlen($nonce);
        $messagePrefix = "\x19Ethereum Signed Message:\n" . $msgLength . $nonce;
        $msgHash = Keccak::hash($messagePrefix, 256);

        $sig = $key->sign($msgHash);
        $r = str_pad($sig->r->toString(16), 64, '0', STR_PAD_LEFT);
        $s = str_pad($sig->s->toString(16), 64, '0', STR_PAD_LEFT);
        $v = dechex($sig->recoveryParam + 27);
        $signature = '0x' . $r . $s . $v;

        // 4. Verify signature endpoint
        $verifyResponse = $this->postJson('/web3/verify', [
            'address' => $address,
            'signature' => $signature,
        ]);

        $verifyResponse->assertStatus(200);
        $verifyResponse->assertJson(['message' => 'Authenticated successfully']);

        // 5. Assert database user was created and auto-verified
        $this->assertDatabaseHas('users', [
            'wallet_address' => $address,
        ]);

        $user = User::where('wallet_address', $address)->firstOrFail();
        $this->assertNotNull($user->email_verified_at);
        $this->assertStringStartsWith('User_', $user->name);

        // 6. Assert authenticated as the new user
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function it_prevents_replay_attacks_by_consuming_nonce()
    {
        // 1. Generate keys and address
        $key = $this->ec->genKeyPair();
        $privateKey = $key->getPrivate('hex');
        $publicKey = $key->getPublic()->encode('hex');
        $address = '0x' . substr(Keccak::hash(hex2bin(substr($publicKey, 2)), 256), -40);

        // 2. Request nonce
        $nonceResponse = $this->getJson("/web3/nonce?address={$address}");
        $nonceResponse->assertStatus(200);
        $nonce = $nonceResponse->json('nonce');

        // 3. Generate signature
        $msgLength = strlen($nonce);
        $messagePrefix = "\x19Ethereum Signed Message:\n" . $msgLength . $nonce;
        $msgHash = Keccak::hash($messagePrefix, 256);

        $sig = $key->sign($msgHash);
        $r = str_pad($sig->r->toString(16), 64, '0', STR_PAD_LEFT);
        $s = str_pad($sig->s->toString(16), 64, '0', STR_PAD_LEFT);
        $v = dechex($sig->recoveryParam + 27);
        $signature = '0x' . $r . $s . $v;

        // 4. Verify once -> Success
        $verifyResponse1 = $this->postJson('/web3/verify', [
            'address' => $address,
            'signature' => $signature,
        ]);
        $verifyResponse1->assertStatus(200);

        // 5. Verify twice -> Failure (nonce consumed and deleted from Cache)
        $verifyResponse2 = $this->postJson('/web3/verify', [
            'address' => $address,
            'signature' => $signature,
        ]);
        $verifyResponse2->assertStatus(422);
        $verifyResponse2->assertJson(['message' => 'Nonce expired or not found. Please try again.']);
    }

    /** @test */
    public function it_fails_verification_with_invalid_signature()
    {
        // 1. Generate keys and address
        $key = $this->ec->genKeyPair();
        $privateKey = $key->getPrivate('hex');
        $publicKey = $key->getPublic()->encode('hex');
        $address = '0x' . substr(Keccak::hash(hex2bin(substr($publicKey, 2)), 256), -40);

        // 2. Request nonce
        $nonceResponse = $this->getJson("/web3/nonce?address={$address}");
        $nonceResponse->assertStatus(200);
        $nonce = $nonceResponse->json('nonce');

        // 3. Generate an invalid signature (using a different key pair)
        $differentKey = $this->ec->genKeyPair();
        $msgLength = strlen($nonce);
        $messagePrefix = "\x19Ethereum Signed Message:\n" . $msgLength . $nonce;
        $msgHash = Keccak::hash($messagePrefix, 256);

        $sig = $differentKey->sign($msgHash);
        $r = str_pad($sig->r->toString(16), 64, '0', STR_PAD_LEFT);
        $s = str_pad($sig->s->toString(16), 64, '0', STR_PAD_LEFT);
        $v = dechex($sig->recoveryParam + 27);
        $signature = '0x' . $r . $s . $v;

        // 4. Verify signature -> Fails (401)
        $verifyResponse = $this->postJson('/web3/verify', [
            'address' => $address,
            'signature' => $signature,
        ]);

        $verifyResponse->assertStatus(401);
        $verifyResponse->assertJson(['message' => 'Signature verification failed']);

        // Verify no user is logged in
        $this->assertGuest();
    }
}
