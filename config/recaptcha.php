<?php

return [
    'enabled' => env('RECAPTCHA_ENABLED', true),
    'site_key' => env('RECAPTCHA_SITE_KEY', '0x4AAAAAABtLmkXv5fAhWk16'),
    'secret_key' => env('RECAPTCHA_SECRET_KEY', '0x4AAAAAABtLmsmeaIITLCBef4iUZMvb0Aw'),
    'version' => 'turnstile', // Cloudflare Turnstile
    'score_threshold' => 0.5, // for v3
    'action' => 'form_submit', // for v3
];
