<?php

namespace App\Services;

use App\Exceptions\MetarangWalletLookupException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetarangWalletClient
{
    /**
     * @return array{already_registered: bool, user_code: string|null}
     */
    public function lookupRegistration(string $walletAddress): array
    {
        $baseUrl = config('services.metarang.url');

        if (! is_string($baseUrl) || $baseUrl === '') {
            throw new MetarangWalletLookupException('Metarang API is not configured.');
        }

        $endpoint = rtrim($baseUrl, '/').'/api/wallets/registered';

        try {
            $response = Http::baseUrl(rtrim($baseUrl, '/'))
                ->timeout(10)
                ->acceptJson()
                ->asJson()
                ->post('/api/wallets/registered', [
                    'wallet_address' => $walletAddress,
                ])
                ->throw();
        } catch (ConnectionException|RequestException $e) {
            Log::error('Metarang wallet registration lookup failed', [
                'endpoint' => $endpoint,
                'wallet_address' => $walletAddress,
                'error' => $e->getMessage(),
            ]);

            throw new MetarangWalletLookupException('Unable to verify wallet registration.', previous: $e);
        }

        $alreadyRegistered = $response->json('already_registered');

        if (! is_bool($alreadyRegistered)) {
            throw new MetarangWalletLookupException('Invalid Metarang wallet registration response.');
        }

        $userCode = $response->json('user_code');

        if ($alreadyRegistered && (! is_string($userCode) || $userCode === '')) {
            throw new MetarangWalletLookupException('Metarang did not return a user code for the registered wallet.');
        }

        return [
            'already_registered' => $alreadyRegistered,
            'user_code' => is_string($userCode) && $userCode !== '' ? $userCode : null,
        ];
    }
}
