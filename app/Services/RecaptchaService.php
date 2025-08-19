<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecaptchaService
{
    public function verify($token, $action = null)
    {
        if (!config('recaptcha.enabled')) {
            return true;
        }

        if (empty($token)) {
            Log::warning('reCAPTCHA token is empty');
            return false;
        }

        try {
            $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret' => config('recaptcha.secret_key'),
                'response' => $token,
                'remoteip' => request()->ip(),
            ]);

            $result = $response->json();

            if (!$result['success']) {
                Log::warning('reCAPTCHA verification failed', [
                    'errors' => $result['error-codes'] ?? [],
                    'ip' => request()->ip(),
                    'response' => $result,
                ]);
                return false;
            }

            Log::info('reCAPTCHA verification successful', [
                'ip' => request()->ip(),
                'challenge_ts' => $result['challenge_ts'] ?? null,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('reCAPTCHA verification error', [
                'message' => $e->getMessage(),
                'ip' => request()->ip(),
            ]);
            return false;
        }
    }
}
