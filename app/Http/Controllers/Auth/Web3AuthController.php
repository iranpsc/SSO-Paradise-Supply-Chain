<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Elliptic\EC;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use kornrunner\Keccak;

class Web3AuthController extends Controller
{
    private const NONCE_TTL_MINUTES = 5;

    private const SECP256K1_HALF_N = '7FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF5D576E7357A4501DDFE92F46681B20A0';

    public function getLoginNonce(Request $request)
    {
        $request->validate([
            'address' => ['required', 'string', 'regex:/^0x[a-fA-F0-9]{40}$/'],
        ]);

        $address = strtolower($request->address);
        $nonce = $this->buildLoginMessage($address);

        Cache::put($this->loginNonceCacheKey($address), $nonce, now()->addMinutes(self::NONCE_TTL_MINUTES));

        return response()->json(['nonce' => $nonce]);
    }

    public function getLinkNonce(Request $request)
    {
        $request->validate([
            'address' => ['required', 'string', 'regex:/^0x[a-fA-F0-9]{40}$/'],
        ]);

        $user = $request->user();
        $address = strtolower($request->address);

        if ($user->wallet_address) {
            return response()->json(['message' => 'Wallet already connected to this account.'], 422);
        }

        if (User::where('wallet_address', $address)->exists()) {
            return response()->json(['message' => 'This wallet is already linked to another account.'], 422);
        }

        $nonce = $this->buildLinkMessage($user->id, $address);

        Cache::put(
            $this->linkNonceCacheKey($user->id, $address),
            $nonce,
            now()->addMinutes(self::NONCE_TTL_MINUTES)
        );

        return response()->json(['nonce' => $nonce]);
    }

    public function verifySignature(Request $request)
    {
        $request->validate([
            'address' => ['required', 'string', 'regex:/^0x[a-fA-F0-9]{40}$/'],
            'signature' => ['required', 'string', 'regex:/^0x[a-fA-F0-9]{130}$/'],
        ]);

        $address = strtolower($request->address);
        $nonce = Cache::pull($this->loginNonceCacheKey($address));

        if (!$nonce) {
            Log::warning('Web3 login rejected: nonce missing or expired', [
                'address' => $address,
                'ip' => $request->ip(),
            ]);

            return $this->walletLoginError(
                $request,
                'Nonce expired or not found. Please try again.',
                422
            );
        }

        if (!$this->isValidWalletSignature($address, $request->signature, $nonce)) {
            Log::warning('Web3 login rejected: invalid signature', [
                'address' => $address,
                'ip' => $request->ip(),
            ]);

            return $this->walletLoginError($request, 'Signature verification failed', 401);
        }

        $user = User::where('wallet_address', $address)->first();
        if (!$user) {
            $user = new User();
            $user->wallet_address = $address;
            $user->name = 'User_' . substr($address, 2, 6);
            $user->email_verified_at = now();
            $user->code = $this->generateCode();
            $user->save();
            $user->personalInfo()->create();
        }

        return $this->completeWalletLogin($request, $user);
    }

    private function completeWalletLogin(Request $request, User $user)
    {
        Auth::login($user);
        $request->session()->regenerate();

        if (!$user->hasVerifiedEmail()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Authenticated successfully',
                    'redirect' => route('verification.notice'),
                ]);
            }

            return redirect()->route('verification.notice');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Authenticated successfully',
                'redirect' => redirect()->intended(RouteServiceProvider::HOME)->getTargetUrl(),
            ]);
        }

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    private function walletLoginError(Request $request, string $message, int $status)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], $status);
        }

        return back()->withErrors(['wallet' => $message]);
    }

    public function linkWallet(Request $request)
    {
        $request->validate([
            'address' => ['required', 'string', 'regex:/^0x[a-fA-F0-9]{40}$/'],
            'signature' => ['required', 'string', 'regex:/^0x[a-fA-F0-9]{130}$/'],
        ]);

        $address = strtolower($request->address);
        $user = $request->user();
        $nonce = Cache::pull($this->linkNonceCacheKey($user->id, $address));

        if (!$nonce) {
            Log::warning('Web3 wallet link rejected: nonce missing or expired', [
                'user_id' => $user->id,
                'address' => $address,
                'ip' => $request->ip(),
            ]);

            return response()->json(['message' => 'Nonce expired or not found. Please try again.'], 422);
        }

        if (!$this->isValidWalletSignature($address, $request->signature, $nonce)) {
            Log::warning('Web3 wallet link rejected: invalid signature', [
                'user_id' => $user->id,
                'address' => $address,
                'ip' => $request->ip(),
            ]);

            return response()->json(['message' => 'Signature verification failed'], 401);
        }

        $result = DB::transaction(function () use ($user, $address) {
            $lockedUser = User::whereKey($user->id)->lockForUpdate()->firstOrFail();

            if ($lockedUser->wallet_address) {
                return 'already_connected';
            }

            if (User::where('wallet_address', $address)->lockForUpdate()->exists()) {
                return 'already_linked';
            }

            $lockedUser->wallet_address = $address;
            $lockedUser->save();

            return 'success';
        });

        return match ($result) {
            'already_connected' => response()->json(['message' => 'Wallet already connected to this account.'], 422),
            'already_linked' => response()->json(['message' => 'This wallet is already linked to another account.'], 422),
            default => response()->json(['message' => 'Wallet connected successfully']),
        };
    }

    private function buildLoginMessage(string $address): string
    {
        return implode("\n", [
            'Sign in to ' . config('app.name') . ' at ' . $this->applicationDomain() . '.',
            '',
            'Wallet: ' . $address,
            'Nonce: ' . Str::random(32),
        ]);
    }

    private function buildLinkMessage(int $userId, string $address): string
    {
        return implode("\n", [
            'Link wallet to your ' . config('app.name') . ' account at ' . $this->applicationDomain() . '.',
            '',
            'Account ID: ' . $userId,
            'Wallet: ' . $address,
            'Nonce: ' . Str::random(32),
        ]);
    }

    private function applicationDomain(): string
    {
        $host = parse_url(config('app.url'), PHP_URL_HOST);

        return is_string($host) && $host !== '' ? $host : 'localhost';
    }

    private function loginNonceCacheKey(string $address): string
    {
        return 'web3_nonce_login_' . $address;
    }

    private function linkNonceCacheKey(int $userId, string $address): string
    {
        return 'web3_nonce_link_' . $userId . '_' . $address;
    }

    private function isValidWalletSignature(string $address, string $signature, string $nonce): bool
    {
        $r = substr($signature, 2, 64);
        $s = substr($signature, 66, 64);
        $v = hexdec(substr($signature, 130, 2));

        if (!ctype_xdigit($r) || !ctype_xdigit($s)) {
            return false;
        }

        if (strcasecmp($s, self::SECP256K1_HALF_N) > 0) {
            return false;
        }

        if ($v < 27) {
            $v += 27;
        }

        $recoveryParam = $v - 27;
        if ($recoveryParam !== 0 && $recoveryParam !== 1) {
            return false;
        }

        $msgLength = strlen($nonce);
        $messagePrefix = "\x19Ethereum Signed Message:\n" . $msgLength . $nonce;
        $msgHash = Keccak::hash($messagePrefix, 256);

        try {
            $ec = new EC('secp256k1');
            $publicKey = $ec->recoverPubKey($msgHash, [
                'r' => $r,
                's' => $s,
            ], $recoveryParam);

            $derivedAddress = '0x' . substr(Keccak::hash(hex2bin(substr($publicKey->encode('hex'), 2)), 256), -40);

            return strtolower($derivedAddress) === $address;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Generate user code.
     *
     * @return string
     */
    public function generateCode()
    {
        $lastCode = User::orderBy('code', 'desc')->first()?->code;

        if (!$lastCode) {
            return 'hm-2000000';
        }

        $lastCodeNumber = intval(substr($lastCode, 3));
        $newCodeNumber = $lastCodeNumber + 1;
        $newCode = 'hm-' . str_pad($newCodeNumber, 7, '0', STR_PAD_LEFT);

        return $newCode;
    }
}
