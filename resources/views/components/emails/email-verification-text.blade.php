{{ __('Welcome to :app_name - Email Verification', ['app_name' => __(config('app.name'))]) }}

{{ __('Hello :name!', ['name' => $user->name ?? 'User']) }}

{{ __('Thank you for registering with :app_name. To complete your registration and secure your account, please verify your email address.', ['app_name' => __(config('app.name'))]) }}

{{ __('WHY VERIFY YOUR EMAIL?') }}
• {{ __('Ensure account security and prevent unauthorized access') }}
• {{ __('Confirm that you can receive important account notifications') }}
• {{ __('Protect your account from potential misuse') }}
• {{ __('Enable all features and services') }}

{{ __('TO VERIFY YOUR EMAIL ADDRESS:') }}
1. {{ __('Copy and paste this URL into your browser:') }}
   {{ $verificationUrl }}
2. {{ __('You will be automatically logged in and redirected to your account') }}
3. {{ __('Your email will be marked as verified') }}

{{ __('SECURITY INFORMATION:') }}
• {{ __('This verification link will expire in :minutes minutes', ['minutes' => $expireTime ?? 60]) }}
• {{ __('This verification link is unique to your account') }}
• {{ __('Never share this verification link with anyone') }}

{{ __('DIDN\'T CREATE AN ACCOUNT?') }}
{{ __('If you did not register for an account with us, please ignore this email. No action is required, and this verification link will expire automatically.') }}

{{ __('Need help? Contact our support team if you\'re having trouble verifying your email or have questions about your account.') }}

{{ __('This email was sent from a monitored address. Please do not reply to this email.') }}

{{ __('Welcome aboard!') }}
{{ config('app.name') }} {{ __('Team') }}

© {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}
