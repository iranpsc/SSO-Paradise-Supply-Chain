<?php

namespace Tests\Feature;

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
        $this->assertStringContainsString('Sign in to ' . config('app.name'), $nonce);
        $this->assertStringContainsString('Wallet: ' . $address, $nonce);

        $this->assertEquals($nonce, Cache::get("web3_nonce_login_{$address}"));

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
        $key = $this->ec->genKeyPair();
        $publicKey = $key->getPublic()->encode('hex');
        $address = '0x' . substr(Keccak::hash(hex2bin(substr($publicKey, 2)), 256), -40);

        $nonceResponse = $this->getJson("/web3/nonce?address={$address}");
        $nonceResponse->assertStatus(200);
        $nonce = $nonceResponse->json('nonce');

        $signature = $this->signMessage($key, $nonce);

        $verifyResponse = $this->postJson('/web3/verify', [
            'address' => $address,
            'signature' => $signature,
        ]);

        $verifyResponse->assertStatus(200);
        $verifyResponse->assertJson(['message' => 'Authenticated successfully']);

        $this->assertDatabaseHas('users', [
            'wallet_address' => strtolower($address),
        ]);

        $user = User::where('wallet_address', strtolower($address))->firstOrFail();
        $this->assertNotNull($user->email_verified_at);
        $this->assertStringStartsWith('User_', $user->name);

        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function it_redirects_to_intended_url_after_wallet_authentication()
    {
        $intendedUrl = url('/oauth/authorize?client_id=test&redirect_uri=https://example.com/callback');
        session(['url.intended' => $intendedUrl]);

        $key = $this->ec->genKeyPair();
        $publicKey = $key->getPublic()->encode('hex');
        $address = '0x' . substr(Keccak::hash(hex2bin(substr($publicKey, 2)), 256), -40);

        $nonceResponse = $this->getJson("/web3/nonce?address={$address}");
        $nonce = $nonceResponse->json('nonce');
        $signature = $this->signMessage($key, $nonce);

        $verifyResponse = $this->postJson('/web3/verify', [
            'address' => $address,
            'signature' => $signature,
        ]);

        $verifyResponse->assertStatus(200);
        $verifyResponse->assertJson([
            'message' => 'Authenticated successfully',
            'redirect' => $intendedUrl,
        ]);
    }

    /** @test */
    public function it_redirects_browser_requests_to_intended_url_after_wallet_authentication()
    {
        $intendedUrl = url('/oauth/authorize?client_id=test&redirect_uri=https://example.com/callback');
        session(['url.intended' => $intendedUrl]);

        $key = $this->ec->genKeyPair();
        $publicKey = $key->getPublic()->encode('hex');
        $address = '0x' . substr(Keccak::hash(hex2bin(substr($publicKey, 2)), 256), -40);

        $nonceResponse = $this->getJson("/web3/nonce?address={$address}");
        $nonce = $nonceResponse->json('nonce');
        $signature = $this->signMessage($key, $nonce);

        $verifyResponse = $this->post('/web3/verify', [
            'address' => $address,
            'signature' => $signature,
        ]);

        $verifyResponse->assertRedirect($intendedUrl);
    }

    /** @test */
    public function it_prevents_replay_attacks_by_consuming_nonce()
    {
        $key = $this->ec->genKeyPair();
        $publicKey = $key->getPublic()->encode('hex');
        $address = '0x' . substr(Keccak::hash(hex2bin(substr($publicKey, 2)), 256), -40);

        $nonceResponse = $this->getJson("/web3/nonce?address={$address}");
        $nonce = $nonceResponse->json('nonce');
        $signature = $this->signMessage($key, $nonce);

        $verifyResponse1 = $this->postJson('/web3/verify', [
            'address' => $address,
            'signature' => $signature,
        ]);
        $verifyResponse1->assertStatus(200);

        $this->post('/logout');

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
        $key = $this->ec->genKeyPair();
        $publicKey = $key->getPublic()->encode('hex');
        $address = '0x' . substr(Keccak::hash(hex2bin(substr($publicKey, 2)), 256), -40);

        $nonceResponse = $this->getJson("/web3/nonce?address={$address}");
        $nonce = $nonceResponse->json('nonce');

        $differentKey = $this->ec->genKeyPair();
        $signature = $this->signMessage($differentKey, $nonce);

        $verifyResponse = $this->postJson('/web3/verify', [
            'address' => $address,
            'signature' => $signature,
        ]);

        $verifyResponse->assertStatus(401);
        $verifyResponse->assertJson(['message' => 'Signature verification failed']);

        $this->assertGuest();
    }

    /** @test */
    public function authenticated_user_can_link_wallet_to_account()
    {
        $user = User::factory()->create();

        $key = $this->ec->genKeyPair();
        $publicKey = $key->getPublic()->encode('hex');
        $address = '0x' . substr(Keccak::hash(hex2bin(substr($publicKey, 2)), 256), -40);

        $nonceResponse = $this->actingAs($user)->getJson("/web3/link/nonce?address={$address}");
        $nonce = $nonceResponse->json('nonce');
        $signature = $this->signMessage($key, $nonce);

        $linkResponse = $this->actingAs($user)->postJson('/web3/link', [
            'address' => $address,
            'signature' => $signature,
        ]);

        $linkResponse->assertStatus(200);
        $linkResponse->assertJson(['message' => 'Wallet connected successfully']);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'wallet_address' => strtolower($address),
        ]);
    }

    /** @test */
    public function authenticated_user_cannot_link_wallet_already_used_by_another_account()
    {
        $existingUser = User::factory()->create();
        $user = User::factory()->create();

        $key = $this->ec->genKeyPair();
        $publicKey = $key->getPublic()->encode('hex');
        $address = '0x' . substr(Keccak::hash(hex2bin(substr($publicKey, 2)), 256), -40);

        User::whereKey($existingUser->id)->update(['wallet_address' => strtolower($address)]);

        $nonceResponse = $this->actingAs($user)->getJson("/web3/link/nonce?address={$address}");
        $nonceResponse->assertStatus(422);
        $nonceResponse->assertJson(['message' => 'This wallet is already linked to another account.']);
    }

    /** @test */
    public function link_signature_cannot_be_used_for_wallet_login()
    {
        $user = User::factory()->create();

        $key = $this->ec->genKeyPair();
        $publicKey = $key->getPublic()->encode('hex');
        $address = '0x' . substr(Keccak::hash(hex2bin(substr($publicKey, 2)), 256), -40);

        $nonceResponse = $this->actingAs($user)->getJson("/web3/link/nonce?address={$address}");
        $nonce = $nonceResponse->json('nonce');
        $signature = $this->signMessage($key, $nonce);

        auth()->logout();

        $verifyResponse = $this->postJson('/web3/verify', [
            'address' => $address,
            'signature' => $signature,
        ]);

        $verifyResponse->assertStatus(422);
        $verifyResponse->assertJson(['message' => 'Nonce expired or not found. Please try again.']);
        $this->assertGuest();
    }

    /** @test */
    public function login_signature_cannot_be_used_to_link_wallet()
    {
        $user = User::factory()->create();

        $key = $this->ec->genKeyPair();
        $publicKey = $key->getPublic()->encode('hex');
        $address = '0x' . substr(Keccak::hash(hex2bin(substr($publicKey, 2)), 256), -40);

        $nonceResponse = $this->getJson("/web3/nonce?address={$address}");
        $nonce = $nonceResponse->json('nonce');
        $signature = $this->signMessage($key, $nonce);

        $linkResponse = $this->actingAs($user)->postJson('/web3/link', [
            'address' => $address,
            'signature' => $signature,
        ]);

        $linkResponse->assertStatus(422);
        $linkResponse->assertJson(['message' => 'Nonce expired or not found. Please try again.']);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
            'wallet_address' => strtolower($address),
        ]);
    }

    /** @test */
    public function authenticated_user_connecting_wallet_via_verify_does_not_create_new_user_or_code()
    {
        $user = User::factory()->create(['code' => 'hm-2000001']);

        $key = $this->ec->genKeyPair();
        $publicKey = $key->getPublic()->encode('hex');
        $address = '0x' . substr(Keccak::hash(hex2bin(substr($publicKey, 2)), 256), -40);

        $nonceResponse = $this->getJson("/web3/nonce?address={$address}");
        $nonce = $nonceResponse->json('nonce');
        $signature = $this->signMessage($key, $nonce);

        $verifyResponse = $this->actingAs($user)->postJson('/web3/verify', [
            'address' => $address,
            'signature' => $signature,
        ]);

        $verifyResponse->assertStatus(200);
        $verifyResponse->assertJson(['message' => 'Wallet connected successfully']);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'wallet_address' => strtolower($address),
            'code' => 'hm-2000001',
        ]);

        $this->assertDatabaseCount('users', 1);
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function authenticated_users_cannot_request_login_nonce()
    {
        $user = User::factory()->create();
        $address = '0x90f8bfac9c63c35718a7a77e94b002d274950e89';

        $response = $this->actingAs($user)->getJson("/web3/nonce?address={$address}");

        $response->assertRedirect(route('home'));
    }

    private function signMessage($key, string $nonce): string
    {
        $msgLength = strlen($nonce);
        $messagePrefix = "\x19Ethereum Signed Message:\n" . $msgLength . $nonce;
        $msgHash = Keccak::hash($messagePrefix, 256);

        $sig = $key->sign($msgHash);
        $r = str_pad($sig->r->toString(16), 64, '0', STR_PAD_LEFT);
        $s = str_pad($sig->s->toString(16), 64, '0', STR_PAD_LEFT);
        $v = dechex($sig->recoveryParam + 27);

        return '0x' . $r . $s . $v;
    }
}
