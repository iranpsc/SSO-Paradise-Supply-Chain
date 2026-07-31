<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Elliptic\EC;
use kornrunner\Keccak;
use PHPUnit\Framework\Attributes\Test;

class Web3AuthTest extends TestCase
{
    use RefreshDatabase;

    private EC $ec;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ec = new EC('secp256k1');
    }

    #[Test]
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

    #[Test]
    public function it_fails_nonce_generation_with_invalid_address()
    {
        $response = $this->getJson('/web3/nonce?address=invalid-eth-address');

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['address']);
    }

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
    public function authenticated_users_cannot_request_login_nonce()
    {
        $user = User::factory()->create();
        $address = '0x90f8bfac9c63c35718a7a77e94b002d274950e89';

        $response = $this->actingAs($user)->getJson("/web3/nonce?address={$address}");

        $response->assertRedirect(route('home'));
    }

    #[Test]
    public function link_nonce_rejects_when_wallet_already_connected_to_account(): void
    {
        $user = User::factory()->create([
            'wallet_address' => '0x1111111111111111111111111111111111111111',
        ]);
        $address = '0x90f8bfac9c63c35718a7a77e94b002d274950e89';

        $this->actingAs($user)
            ->getJson("/web3/link/nonce?address={$address}")
            ->assertStatus(422)
            ->assertJson(['message' => 'Wallet already connected to this account.']);
    }

    #[Test]
    public function unverified_wallet_user_is_redirected_to_verification_notice_on_json_login(): void
    {
        $key = $this->ec->genKeyPair();
        $publicKey = $key->getPublic()->encode('hex');
        $address = '0x' . substr(Keccak::hash(hex2bin(substr($publicKey, 2)), 256), -40);

        $user = User::factory()->unverified()->create([
            'wallet_address' => strtolower($address),
            'email' => 'wallet-unverified@example.com',
        ]);

        $nonce = $this->getJson("/web3/nonce?address={$address}")->json('nonce');
        $signature = $this->signMessage($key, $nonce);

        $this->postJson('/web3/verify', [
            'address' => $address,
            'signature' => $signature,
        ])
            ->assertOk()
            ->assertJson([
                'message' => 'Authenticated successfully',
                'redirect' => route('verification.notice'),
            ]);

        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function unverified_wallet_user_is_redirected_to_verification_notice_on_browser_login(): void
    {
        $key = $this->ec->genKeyPair();
        $publicKey = $key->getPublic()->encode('hex');
        $address = '0x' . substr(Keccak::hash(hex2bin(substr($publicKey, 2)), 256), -40);

        User::factory()->unverified()->create([
            'wallet_address' => strtolower($address),
        ]);

        $nonce = $this->getJson("/web3/nonce?address={$address}")->json('nonce');
        $signature = $this->signMessage($key, $nonce);

        $this->post('/web3/verify', [
            'address' => $address,
            'signature' => $signature,
        ])->assertRedirect(route('verification.notice'));
    }

    #[Test]
    public function browser_verify_returns_session_errors_for_invalid_signature(): void
    {
        $key = $this->ec->genKeyPair();
        $publicKey = $key->getPublic()->encode('hex');
        $address = '0x' . substr(Keccak::hash(hex2bin(substr($publicKey, 2)), 256), -40);

        $nonce = $this->getJson("/web3/nonce?address={$address}")->json('nonce');
        $signature = $this->signMessage($this->ec->genKeyPair(), $nonce);

        $this->from('/login')
            ->post('/web3/verify', [
                'address' => $address,
                'signature' => $signature,
            ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('wallet');
    }

    #[Test]
    public function link_wallet_rejects_invalid_signature(): void
    {
        $user = User::factory()->create();

        $key = $this->ec->genKeyPair();
        $publicKey = $key->getPublic()->encode('hex');
        $address = '0x' . substr(Keccak::hash(hex2bin(substr($publicKey, 2)), 256), -40);

        $nonce = $this->actingAs($user)->getJson("/web3/link/nonce?address={$address}")->json('nonce');
        $badSignature = $this->signMessage($this->ec->genKeyPair(), $nonce);

        $this->actingAs($user)
            ->postJson('/web3/link', [
                'address' => $address,
                'signature' => $badSignature,
            ])
            ->assertStatus(401)
            ->assertJson(['message' => 'Signature verification failed']);
    }

    #[Test]
    public function link_wallet_returns_already_connected_when_wallet_set_after_nonce(): void
    {
        $user = User::factory()->create();

        $key = $this->ec->genKeyPair();
        $publicKey = $key->getPublic()->encode('hex');
        $address = '0x' . substr(Keccak::hash(hex2bin(substr($publicKey, 2)), 256), -40);

        $nonce = $this->actingAs($user)->getJson("/web3/link/nonce?address={$address}")->json('nonce');
        $signature = $this->signMessage($key, $nonce);

        // Simulate race: wallet connected after nonce was issued.
        $user->update(['wallet_address' => '0x2222222222222222222222222222222222222222']);

        $this->actingAs($user)
            ->postJson('/web3/link', [
                'address' => $address,
                'signature' => $signature,
            ])
            ->assertStatus(422)
            ->assertJson(['message' => 'Wallet already connected to this account.']);
    }

    #[Test]
    public function browser_link_returns_session_errors_when_already_connected(): void
    {
        $user = User::factory()->create();

        $key = $this->ec->genKeyPair();
        $publicKey = $key->getPublic()->encode('hex');
        $address = '0x' . substr(Keccak::hash(hex2bin(substr($publicKey, 2)), 256), -40);

        $nonce = $this->actingAs($user)->getJson("/web3/link/nonce?address={$address}")->json('nonce');
        $signature = $this->signMessage($key, $nonce);

        $user->update(['wallet_address' => '0x2222222222222222222222222222222222222222']);

        $this->actingAs($user)
            ->from('/home')
            ->post('/web3/link', [
                'address' => $address,
                'signature' => $signature,
            ])
            ->assertRedirect('/home')
            ->assertSessionHasErrors('wallet');
    }

    #[Test]
    public function browser_link_returns_session_errors_when_wallet_already_linked_elsewhere(): void
    {
        $existing = User::factory()->create();
        $user = User::factory()->create();

        $key = $this->ec->genKeyPair();
        $publicKey = $key->getPublic()->encode('hex');
        $address = '0x' . substr(Keccak::hash(hex2bin(substr($publicKey, 2)), 256), -40);

        $nonce = $this->actingAs($user)->getJson("/web3/link/nonce?address={$address}")->json('nonce');
        $signature = $this->signMessage($key, $nonce);

        // Race: another account claims the wallet after nonce issuance.
        $existing->update(['wallet_address' => strtolower($address)]);

        $this->actingAs($user)
            ->from('/home')
            ->post('/web3/link', [
                'address' => $address,
                'signature' => $signature,
            ])
            ->assertRedirect('/home')
            ->assertSessionHasErrors('wallet');
    }

    #[Test]
    public function link_wallet_json_rejects_when_wallet_claimed_after_nonce(): void
    {
        $existing = User::factory()->create();
        $user = User::factory()->create();

        $key = $this->ec->genKeyPair();
        $publicKey = $key->getPublic()->encode('hex');
        $address = '0x' . substr(Keccak::hash(hex2bin(substr($publicKey, 2)), 256), -40);

        $nonce = $this->actingAs($user)->getJson("/web3/link/nonce?address={$address}")->json('nonce');
        $signature = $this->signMessage($key, $nonce);

        $existing->update(['wallet_address' => strtolower($address)]);

        $this->actingAs($user)
            ->postJson('/web3/link', [
                'address' => $address,
                'signature' => $signature,
            ])
            ->assertStatus(422)
            ->assertJson(['message' => 'This wallet is already linked to another account.']);
    }

    #[Test]
    public function new_wallet_user_code_increments_from_existing_codes(): void
    {
        User::factory()->create(['code' => 'hm-2000042']);

        $key = $this->ec->genKeyPair();
        $publicKey = $key->getPublic()->encode('hex');
        $address = '0x' . substr(Keccak::hash(hex2bin(substr($publicKey, 2)), 256), -40);

        $nonce = $this->getJson("/web3/nonce?address={$address}")->json('nonce');
        $signature = $this->signMessage($key, $nonce);

        $this->postJson('/web3/verify', [
            'address' => $address,
            'signature' => $signature,
        ])->assertOk();

        $this->assertDatabaseHas('users', [
            'wallet_address' => strtolower($address),
            'code' => 'hm-2000043',
        ]);
    }

    #[Test]
    public function signature_validation_rejects_malformed_components(): void
    {
        $controller = app(\App\Http\Controllers\Auth\Web3AuthController::class);
        $method = new \ReflectionMethod($controller, 'isValidWalletSignature');

        $address = '0x90f8bfac9c63c35718a7a77e94b002d274950e89';
        $nonce = 'test-nonce';

        // Non-hex r/s (bypasses HTTP validation).
        $this->assertFalse($method->invoke(
            $controller,
            $address,
            '0x'.str_repeat('zz', 32).str_repeat('11', 32).'1b',
            $nonce
        ));

        // High-s signature (EIP-2 rejection).
        $halfN = '7FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF5D576E7357A4501DDFE92F46681B20A0';
        $highS = '8FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF5D576E7357A4501DDFE92F46681B20A0';
        $this->assertFalse($method->invoke(
            $controller,
            $address,
            '0x'.str_repeat('11', 32).$highS.'1b',
            $nonce
        ));

        // Invalid recovery id (v = 31 → recoveryParam = 4).
        $this->assertFalse($method->invoke(
            $controller,
            $address,
            '0x'.str_repeat('11', 32).str_repeat('22', 32).'1f',
            $nonce
        ));

        // Valid hex/low-s/v but unrecoverable point → catch returns false.
        $this->assertFalse($method->invoke(
            $controller,
            $address,
            '0x'.str_repeat('00', 64).'1b',
            $nonce
        ));

        // v < 27 is normalized (+27); still invalid recovery for crafted zeros.
        $this->assertFalse($method->invoke(
            $controller,
            $address,
            '0x'.str_repeat('00', 64).'00',
            $nonce
        ));
    }

    private function signMessage($key, string $nonce): string
    {
        $msgLength = strlen($nonce);
        $messagePrefix = "\x19Ethereum Signed Message:\n" . $msgLength . $nonce;
        $msgHash = Keccak::hash($messagePrefix, 256);

        $sig = $key->sign($msgHash);
        $r = str_pad($sig->r->toString(16), 64, '0', STR_PAD_LEFT);
        $s = str_pad($sig->s->toString(16), 64, '0', STR_PAD_LEFT);
        $recoveryParam = $sig->recoveryParam;

        // Normalize to low-s (EIP-2) to match Web3AuthController verification.
        $halfN = '7FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF5D576E7357A4501DDFE92F46681B20A0';
        if (strcasecmp($s, $halfN) > 0) {
            $n = gmp_init('FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEBAAEDCE6AF48A03BBFD25E8CD0364141', 16);
            $sGmp = gmp_init($s, 16);
            $s = str_pad(gmp_strval(gmp_sub($n, $sGmp), 16), 64, '0', STR_PAD_LEFT);
            $recoveryParam ^= 1;
        }

        $v = dechex($recoveryParam + 27);

        return '0x' . $r . $s . $v;
    }
}
