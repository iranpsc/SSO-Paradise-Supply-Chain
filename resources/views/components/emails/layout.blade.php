<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'fa' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>
    <style>
        /* Email-safe CSS */
        body {
            font-family: {{ app()->getLocale() === 'fa' ? '"Tahoma", "Tehran Sans", "IRANSans", "B Nazanin"' : '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial' }}, sans-serif;
            line-height: 1.6;
            color: #333333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
            direction: {{ app()->getLocale() === 'fa' ? 'rtl' : 'ltr' }};
            text-align: {{ app()->getLocale() === 'fa' ? 'right' : 'left' }};
        }

        .email-container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin: 20px 0;
        }

        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
            margin-bottom: 30px;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
        }

        .subtitle {
            color: #6c757d;
            font-size: 14px;
        }

        h1 {
            color: #212529;
            font-size: 24px;
            margin-bottom: 20px;
            text-align: center;
        }

        .greeting {
            font-size: 18px;
            color: #495057;
            margin-bottom: 20px;
        }

        .content {
            margin-bottom: 30px;
        }

        .security-notice {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
        }

        .security-notice h3 {
            color: #856404;
            margin-top: 0;
            font-size: 16px;
        }

        .instruction-box {
            background-color: #f8f9fa;
            border-{{ app()->getLocale() === 'fa' ? 'right' : 'left' }}: 4px solid #007bff;
            padding: 15px;
            margin: 20px 0;
        }

        .instruction-box h3 {
            color: #007bff;
            margin-top: 0;
            font-size: 16px;
        }

        /* RTL-specific styles */
        @if(app()->getLocale() === 'fa')
        .rtl-list {
            padding-right: 20px;
            padding-left: 0;
        }

        .rtl-list li {
            text-align: right;
        }

        .url-fallback {
            direction: ltr;
            text-align: left;
        }
        @else
        .rtl-list {
            padding-left: 20px;
            padding-right: 0;
        }
        @endif

        .btn {
            display: inline-block;
            padding: 12px 30px;
            background-color: #007bff;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            text-align: center;
            margin: 20px 0;
            font-size: 16px;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        .btn-container {
            text-align: center;
            margin: 30px 0;
        }

        .url-fallback {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 12px;
            word-break: break-all;
            margin: 10px 0;
        }

        ul {
            padding-{{ app()->getLocale() === 'fa' ? 'right' : 'left' }}: 20px;
        }

        li {
            margin-bottom: 8px;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
        }

        .warning {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin: 15px 0;
        }

        .expire-info {
            background-color: #e7f3ff;
            border: 1px solid #b3d7ff;
            color: #0c5460;
            padding: 10px;
            border-radius: 4px;
            margin: 15px 0;
        }

        /* Mobile responsive */
        @media only screen and (max-width: 600px) {
            body {
                padding: 10px;
            }

            .email-container {
                padding: 20px;
            }

            .btn {
                padding: 15px 25px;
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="logo">{{ config('app.name') }}</div>
            <div class="subtitle">{{ $emailType ?? 'Account Security' }}</div>
        </div>

        {{ $slot }}

        <div class="footer">
            <p>{{ __('This email was sent from a monitored address. Please do not reply to this email.') }}</p>
            <p>{{ __('If you need assistance, please contact our support team.') }}</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}</p>
        </div>
    </div>
</body>
</html>
