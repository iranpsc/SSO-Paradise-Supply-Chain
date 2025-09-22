@php
    $isRtl = app()->getLocale() === 'fa';
    $dir = $isRtl ? 'rtl' : 'ltr';
    $align = $isRtl ? 'right' : 'left';
    $oppositeAlign = $isRtl ? 'left' : 'right';
    $fontStack = $isRtl
        ? 'Tahoma, "Tehran Sans", "IRANSans", "B Nazanin", Arial, sans-serif'
        : '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $dir }}">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>{{ $title ?? __('Verify Your Email Address') }}</title>
    <!-- All CSS rules have been inlined for maximum Gmail compatibility. -->
    <!-- Plain text fallback (copy if you need a .blade.php text version):
{{ __('Welcome to :app_name - Email Verification', ['app_name' => __(config('app.name'))]) }}\n\n{{ __('Hello :name!', ['name' => $user->name ?? 'User']) }}\n\n{{ __('Thank you for registering with :app_name. To complete your registration and secure your account, please verify your email address.', ['app_name' => __(config('app.name'))]) }}\n\n{{ __('WHY VERIFY YOUR EMAIL?') }}\n- {{ __('Ensure account security and prevent unauthorized access') }}\n- {{ __('Confirm that you can receive important account notifications') }}\n- {{ __('Protect your account from potential misuse') }}\n- {{ __('Enable all features and services') }}\n\n{{ __('TO VERIFY YOUR EMAIL ADDRESS:') }}\n1. {{ __('Copy and paste this URL into your browser:') }}\n   {{ $verificationUrl }}\n2. {{ __('You will be automatically logged in and redirected to your account') }}\n3. {{ __('Your email will be marked as verified') }}\n\n{{ __('SECURITY INFORMATION:') }}\n- {{ __('This verification link will expire in :minutes minutes', ['minutes' => $expireTime ?? 60]) }}\n- {{ __('This verification link is unique to your account') }}\n- {{ __('Never share this verification link with anyone') }}\n\n{{ __('DIDN\'T CREATE AN ACCOUNT?') }}\n{{ __('If you did not register for an account with us, please ignore this email. No action is required, and this verification link will expire automatically.') }}\n\n{{ __('Need help? Contact our support team if you\'re having trouble verifying your email or have questions about your account.') }}\n{{ __('This email was sent from a monitored address. Please do not reply to this email.') }}\n{{ __('Welcome aboard!') }}\n{{ config('app.name') }} {{ __('Team') }}\n© {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}
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
                            <div style="color:#6c757d;font-size:14px;">{{ $emailType ?? __('Email Verification') }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:30px 30px 10px 30px;">
                            <h1 style="margin:0 0 20px 0;font-size:24px;line-height:1.3;color:#212529;text-align:center;">{{ __('Welcome to :app_name!', ['app_name' => config('app.name')]) }}</h1>
                            <div style="font-size:18px;color:#495057;margin:0 0 20px 0;">{{ __('Hello :name!', ['name' => $user->name ?? 'User']) }}</div>
                            <div style="font-size:15px;line-height:1.6;margin:0 0 25px 0;">{{ __('Thank you for registering with :app_name. To complete your registration and secure your account, please verify your email address.', ['app_name' => config('app.name')]) }}</div>

                            <!-- Why verify -->
                            <div style="background-color:#f8f9fa;padding:15px;margin:0 0 20px 0;border-{{ $isRtl ? 'right' : 'left' }}:4px solid #007bff;">
                                <div style="color:#007bff;font-size:16px;font-weight:600;margin:0 0 10px 0;">{{ __('✨ Why verify your email?') }}</div>
                                <ul style="margin:0;padding:0 {{ $isRtl ? '0 20px 0 0' : '0 0 0 20px' }};list-style:disc;">
                                    <li style="margin:0 0 8px 0;">{{ __('Ensure account security and prevent unauthorized access') }}</li>
                                    <li style="margin:0 0 8px 0;">{{ __('Confirm that you can receive important account notifications') }}</li>
                                    <li style="margin:0 0 8px 0;">{{ __('Protect your account from potential misuse') }}</li>
                                    <li style="margin:0;">{{ __('Enable all features and services') }}</li>
                                </ul>
                            </div>

                            <!-- How to verify -->
                            <div style="background-color:#f8f9fa;padding:15px;margin:0 0 25px 0;border-{{ $isRtl ? 'right' : 'left' }}:4px solid #007bff;">
                                <div style="color:#007bff;font-size:16px;font-weight:600;margin:0 0 10px 0;">{{ __('📧 To verify your email address:') }}</div>
                                <ol style="margin:0;padding:0 {{ $isRtl ? '0 20px 0 0' : '0 0 0 20px' }};">
                                    <li style="margin:0 0 8px 0;">{{ __('Click the verification button below') }}</li>
                                    <li style="margin:0 0 8px 0;">{{ __('You will be automatically logged in and redirected to your account') }}</li>
                                    <li style="margin:0;">{{ __('Your email will be marked as verified') }}</li>
                                </ol>
                            </div>

                            <!-- Button -->
                            <div style="text-align:center;margin:30px 0 10px 0;">
                                <a href="{{ $verificationUrl }}" style="display:inline-block;padding:12px 30px;background-color:#007bff;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:600;font-size:16px;">{{ __('Verify Email Address') }}</a>
                            </div>

                            <!-- Expire info -->
                            <div style="background-color:#e7f3ff;border:1px solid #b3d7ff;color:#0c5460;padding:10px;border-radius:4px;margin:15px 0 20px 0;font-size:14px;">
                                <strong style="display:inline-block;margin-{{ $isRtl ? 'left' : 'right' }}:4px;">{{ __('⏰ Important:') }}</strong>{{ __('This verification link will expire in :minutes minutes.', ['minutes' => $expireTime ?? 60]) }}
                            </div>

                            <!-- URL fallback -->
                            <div style="font-size:14px;margin:0 0 10px 0;">
                                <strong>{{ __('Alternative verification:') }}</strong> {{ __('If the button above doesn\'t work, copy and paste this URL into your browser:') }}
                            </div>
                            <div style="background-color:#f8f9fa;padding:10px;border-radius:4px;font-family:monospace;font-size:12px;word-break:break-all;direction:ltr;text-align:left;margin:0 0 25px 0;">{{ $verificationUrl }}</div>

                            <!-- Security notice -->
                            <div style="background-color:#fff3cd;border:1px solid #ffeaa7;border-radius:6px;padding:15px;margin:0 0 20px 0;">
                                <div style="color:#856404;font-size:16px;font-weight:600;margin:0 0 10px 0;">{{ __('🔒 Security Information') }}</div>
                                <ul style="margin:0;padding:0 {{ $isRtl ? '0 20px 0 0' : '0 0 0 20px' }};list-style:disc;">
                                    <li style="margin:0 0 8px 0;">{{ __('This verification link is unique to your account') }}</li>
                                    <li style="margin:0 0 8px 0;">{{ __('Never share this verification link with anyone') }}</li>
                                    <li style="margin:0;">{{ __('The link will expire automatically for security') }}</li>
                                </ul>
                            </div>

                            <!-- Warning -->
                            <div style="background-color:#f8d7da;border:1px solid #f5c6cb;color:#721c24;padding:10px;border-radius:4px;margin:0 0 25px 0;font-size:14px;">
                                <strong style="display:block;margin:0 0 5px 0;">{{ __('⚠️ Didn\'t create an account?') }}</strong>
                                {{ __('If you did not register for an account with us, please ignore this email. No action is required, and this verification link will expire automatically.') }}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 30px 30px 30px;">
                            <div style="border-top:1px solid #e9ecef;margin:0 0 0 0;padding:20px 0 0 0;text-align:center;font-size:13px;color:#6c757d;line-height:1.4;">
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
