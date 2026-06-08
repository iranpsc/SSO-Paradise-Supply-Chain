<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Elliptic\EC;
use kornrunner\Keccak;

class Web3AuthController extends Controller
{
    // Step 1: Generate and return the nonce
    public function getNonce(Request $request)
    {
        $request->validate([
            'address' => ['required', 'string', 'regex:/^0x[a-fA-F0-9]{40}$/'],
        ]);

        $address = strtolower($request->address);

        // Generate a new random nonce for this login attempt
        $nonce = "Sign this message to authenticate with " . config('app.name') . ": " . Str::random(24);

        // Save the nonce in cache for 5 minutes
        Cache::put('web3_nonce_' . $address, $nonce, now()->addMinutes(5));

        return response()->json(['nonce' => $nonce]);
    }

    public function verifySignature(Request $request)
    {
        $request->validate([
            'address' => ['required', 'string', 'regex:/^0x[a-fA-F0-9]{40}$/'],
            'signature' => ['required', 'string', 'regex:/^0x[a-fA-F0-9]{130}$/'],
        ]);

        $address = strtolower($request->address);

        // Retrieve and delete (OTP) the nonce from cache
        $nonce = Cache::pull('web3_nonce_' . $address);

        if (!$nonce) {
            return response()->json(['message' => 'Nonce expired or not found. Please try again.'], 422);
        }

        // 1. Hash the message just like Ethereum's personal_sign does
        // Metamask prefix format: "\x19Ethereum Signed Message:\n" + length of message + message
        $msgLength = strlen($nonce);
        $messagePrefix = "\x19Ethereum Signed Message:\n" . $msgLength . $nonce;
        $msgHash = Keccak::hash($messagePrefix, 256);

        // 2. Break down the signature into its r, s, and v components
        $signature = $request->signature;
        $r = substr($signature, 2, 64);
        $s = substr($signature, 66, 64);
        $v = hexdec(substr($signature, 130, 2));

        // Normalize v parameter
        if ($v < 27) {
            $v += 27;
        }
        $recoveryParam = $v - 27;

        try {
            // 3. Use secp256k1 curve to recover the public key
            $ec = new EC('secp256k1');
            $publicKey = $ec->recoverPubKey($msgHash, [
                'r' => $r,
                's' => $s
            ], $recoveryParam);

            // 4. Derive the Ethereum address from the public key
            $derivedAddress = '0x' . substr(Keccak::hash(hex2bin(substr($publicKey->encode('hex'), 2)), 256), -40);

            // 5. Compare addresses
            if (strtolower($derivedAddress) === $address) {
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

                Auth::login($user);
                $request->session()->regenerate();

                return response()->json(['message' => 'Authenticated successfully']);
            }
        } catch (\Exception $e) {
            // Cryptographic failure
        }

        return response()->json(['message' => 'Signature verification failed'], 401);
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
