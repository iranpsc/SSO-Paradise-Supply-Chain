@php
    $isRtl = app()->getLocale() === 'fa';
    $dir = $isRtl ? 'rtl' : 'ltr';
    $align = $isRtl ? 'right' : 'left';
    $fontStack = $isRtl
        ? 'Tahoma, "Tehran Sans", "IRANSans", "B Nazanin", Arial, sans-serif'
        : '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $dir }}">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>{{ $title ?? __('Reset Your Password') }}</title>
    <!-- All CSS styles are inlined for maximum client compatibility -->
    <!-- Plain text fallback (reference only):
{{ __('Password Reset Request') }} - {{ __(config('app.name')) }}\n\n{{ __('Hello :name!', ['name' => $user->name ?? 'User']) }}\n\n{{ __('You are receiving this email because we received a password reset request for your account.') }}\n\n{{ __('SECURITY NOTICE:') }}\n- {{ __('This reset link will expire in :minutes minutes for your security', ['minutes' => $expireTime ?? 60]) }}\n- {{ __('If you did not request this password reset, no action is required') }}\n- {{ __('Never share this reset link with anyone') }}\n\n{{ __('TO RESET YOUR PASSWORD:') }}\n1. {{ __('Copy and paste this URL into your browser:') }}\n   {{ $resetUrl }}\n2. {{ __('Enter your new password (minimum 8 characters)') }}\n3. {{ __('Confirm your new password') }}\n4. {{ __('Save your changes') }}\n\n{{ __('DIDN\'T REQUEST THIS?') }}\n{{ __('If you did not request a password reset, please ignore this email. Your password will remain unchanged. For security concerns, contact our support team immediately.') }}\n\n{{ __('Need help? Contact our support team if you\'re having trouble resetting your password.') }}\n{{ __('This email was sent from a monitored address. Please do not reply to this email.') }}\n{{ __('Best regards,') }}\n{{ config('app.name') }} {{ __('Security Team') }}\n© {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}
    -->
</head>
<body style="margin:0;padding:0;background-color:#f8f9fa;font-family:{{ $fontStack }};direction:{{ $dir }};text-align:{{ $align }};color:#333333;">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border-collapse:collapse;background-color:#f8f9fa;">
        <tr>
            <td align="center" style="padding:20px;">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width:600px;border-collapse:collapse;background-color:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 4px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="padding:30px 30px 10px 30px;border-bottom:2px solid #e9ecef;text-align:center;">
                            <div style="font-size:24px;font-weight:bold;color:#007bff;margin:0 0 5px 0;">{{ __(config('app.name')) }}</div>
                            <div style="color:#6c757d;font-size:14px;">{{ $emailType ?? __('Password Reset') }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:30px 30px 10px 30px;">
                            <h1 style="margin:0 0 20px 0;font-size:24px;line-height:1.3;color:#212529;text-align:center;">{{ __('Password Reset Request') }}</h1>
                            <div style="font-size:18px;color:#495057;margin:0 0 20px 0;">{{ __('Hello :name!', ['name' => $user->name ?? 'User']) }}</div>
                            <div style="font-size:15px;line-height:1.6;margin:0 0 25px 0;">{{ __('You are receiving this email because we received a password reset request for your account.') }}</div>

                            <!-- Security notice -->
                            <div style="background-color:#fff3cd;border:1px solid #ffeaa7;border-radius:6px;padding:15px;margin:0 0 20px 0;">
                                <div style="color:#856404;font-size:16px;font-weight:600;margin:0 0 10px 0;">{{ __('🔒 Security Notice') }}</div>
                                <ul style="margin:0;padding:0 {{ $isRtl ? '0 20px 0 0' : '0 0 0 20px' }};list-style:disc;">
                                    <li style="margin:0 0 8px 0;">{{ __('This reset link will expire in :minutes minutes for your security', ['minutes' => $expireTime ?? 60]) }}</li>
                                    <li style="margin:0 0 8px 0;">{{ __('If you did not request this password reset, no action is required') }}</li>
                                    <li style="margin:0;">{{ __('Never share this reset link with anyone') }}</li>
                                </ul>
                            </div>

                            <!-- Instructions -->
                            <div style="background-color:#f8f9fa;padding:15px;margin:0 0 25px 0;border-{{ $isRtl ? 'right' : 'left' }}:4px solid #007bff;">
                                <div style="color:#007bff;font-size:16px;font-weight:600;margin:0 0 10px 0;">{{ __('📝 To reset your password:') }}</div>
                                <ol style="margin:0;padding:0 {{ $isRtl ? '0 20px 0 0' : '0 0 0 20px' }};">
                                    <li style="margin:0 0 8px 0;">{{ __('Click the button below to access the password reset form') }}</li>
                                    <li style="margin:0 0 8px 0;">{{ __('Enter your new password (minimum 8 characters)') }}</li>
                                    <li style="margin:0 0 8px 0;">{{ __('Confirm your new password') }}</li>
                                    <li style="margin:0;">{{ __('Save your changes') }}</li>
                                </ol>
                            </div>

                            <!-- Button -->
                            <div style="text-align:center;margin:30px 0 10px 0;">
                                <a href="{{ $resetUrl }}" style="display:inline-block;padding:12px 30px;background-color:#007bff;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:600;font-size:16px;">{{ __('Reset Password') }}</a>
                            </div>

                            <!-- Expire info -->
                            <div style="background-color:#e7f3ff;border:1px solid #b3d7ff;color:#0c5460;padding:10px;border-radius:4px;margin:15px 0 20px 0;font-size:14px;">
                                <strong style="display:inline-block;margin-{{ $isRtl ? 'left' : 'right' }}:4px;">{{ __('⏰ Important:') }}</strong>{{ __('This link expires in :minutes minutes for your security.', ['minutes' => $expireTime ?? 60]) }}
                            </div>

                            <!-- URL fallback -->
                            <div style="font-size:14px;margin:0 0 10px 0;">
                                <strong>{{ __('Alternative access:') }}</strong> {{ __('If the button above doesn\'t work, copy and paste this URL into your browser:') }}
                            </div>
                            <div style="background-color:#f8f9fa;padding:10px;border-radius:4px;font-family:monospace;font-size:12px;word-break:break-all;direction:ltr;text-align:left;margin:0 0 25px 0;">{{ $resetUrl }}</div>

                            <!-- Warning -->
                            <div style="background-color:#f8d7da;border:1px solid #f5c6cb;color:#721c24;padding:10px;border-radius:4px;margin:0 0 25px 0;font-size:14px;">
                                <strong style="display:block;margin:0 0 5px 0;">{{ __('⚠️ Didn\'t request this?') }}</strong>
                                {{ __('If you did not request a password reset, please ignore this email. Your password will remain unchanged. For security concerns, contact our support team immediately.') }}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 30px 30px 30px;">
                            <div style="border-top:1px solid #e9ecef;margin:0;padding:20px 0 0 0;text-align:center;font-size:13px;color:#6c757d;line-height:1.4;">
                                <div style="margin:0 0 8px 0;">{{ __('This email was sent from a monitored address. Please do not reply to this email.') }}</div>
                                <div style="margin:0 0 8px 0;">{{ __('If you need assistance, please contact our support team.') }}</div>
                                <div style="margin:0;">&copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}</div>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
