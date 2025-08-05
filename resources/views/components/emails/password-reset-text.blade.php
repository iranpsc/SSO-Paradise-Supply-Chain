{{ __('Password Reset Request') }} - {{ __(config('app.name')) }}

{{ __('Hello :name!', ['name' => $user->name ?? 'User']) }}

{{ __('You are receiving this email because we received a password reset request for your account.') }}

{{ __('SECURITY NOTICE:') }}
• {{ __('This reset link will expire in :minutes minutes for your security', ['minutes' => $expireTime ?? 60]) }}
• {{ __('If you did not request this password reset, no action is required') }}
• {{ __('Never share this reset link with anyone') }}

{{ __('TO RESET YOUR PASSWORD:') }}
1. {{ __('Copy and paste this URL into your browser:') }}
   {{ $resetUrl }}
2. {{ __('Enter your new password (minimum 8 characters)') }}
3. {{ __('Confirm your new password') }}
4. {{ __('Save your changes') }}

{{ __('DIDN\'T REQUEST THIS?') }}
{{ __('If you did not request a password reset, please ignore this email. Your password will remain unchanged. For security concerns, contact our support team immediately.') }}

{{ __('Need help? Contact our support team if you\'re having trouble resetting your password.') }}

{{ __('This email was sent from a monitored address. Please do not reply to this email.') }}

{{ __('Best regards,') }}
{{ config('app.name') }} {{ __('Security Team') }}

© {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}
