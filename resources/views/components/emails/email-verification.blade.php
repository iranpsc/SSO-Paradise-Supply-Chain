<x-emails.layout
    :title="__('Verify Your Email Address')"
    :email-type="__('Email Verification')">

    <h1>{{ __('Welcome to :app_name!', ['app_name' => config('app.name')]) }}</h1>

    <div class="greeting">
        {{ __('Hello :name!', ['name' => $user->name ?? 'User']) }}
    </div>

    <div class="content">
        <p>{{ __('Thank you for registering with :app_name. To complete your registration and secure your account, please verify your email address.', ['app_name' => config('app.name')]) }}</p>

        <div class="instruction-box">
            <h3>{{ __('✨ Why verify your email?') }}</h3>
            <ul class="rtl-list">
                <li>{{ __('Ensure account security and prevent unauthorized access') }}</li>
                <li>{{ __('Confirm that you can receive important account notifications') }}</li>
                <li>{{ __('Protect your account from potential misuse') }}</li>
                <li>{{ __('Enable all features and services') }}</li>
            </ul>
        </div>

        <div class="instruction-box">
            <h3>{{ __('📧 To verify your email address:') }}</h3>
            <ol class="rtl-list">
                <li>{{ __('Click the verification button below') }}</li>
                <li>{{ __('You will be automatically logged in and redirected to your account') }}</li>
                <li>{{ __('Your email will be marked as verified') }}</li>
            </ol>
        </div>

        <div class="btn-container">
            <a href="{{ $verificationUrl }}" class="btn">{{ __('Verify Email Address') }}</a>
        </div>

        <div class="expire-info">
            <strong>{{ __('⏰ Important:') }}</strong> {{ __('This verification link will expire in :minutes minutes.', ['minutes' => $expireTime ?? 60]) }}
        </div>

        <p><strong>{{ __('Alternative verification:') }}</strong> {{ __('If the button above doesn\'t work, copy and paste this URL into your browser:') }}</p>
        <div class="url-fallback">{{ $verificationUrl }}</div>

        <div class="security-notice">
            <h3>{{ __('🔒 Security Information') }}</h3>
            <ul class="rtl-list">
                <li>{{ __('This verification link is unique to your account') }}</li>
                <li>{{ __('Never share this verification link with anyone') }}</li>
                <li>{{ __('The link will expire automatically for security') }}</li>
            </ul>
        </div>

        <div class="warning">
            <strong>{{ __('⚠️ Didn\'t create an account?') }}</strong><br>
            {{ __('If you did not register for an account with us, please ignore this email. No action is required, and this verification link will expire automatically.') }}
        </div>
    </div>
</x-emails.layout>
