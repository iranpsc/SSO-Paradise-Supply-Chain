<x-emails.layout
    :title="__('Reset Your Password')"
    :email-type="__('Password Reset')">

    <h1>{{ __('Password Reset Request') }}</h1>

    <div class="greeting">
        {{ __('Hello :name!', ['name' => $user->name ?? 'User']) }}
    </div>

    <div class="content">
        <p>{{ __('You are receiving this email because we received a password reset request for your account.') }}</p>

        <div class="security-notice">
            <h3>{{ __('🔒 Security Notice') }}</h3>
            <ul class="rtl-list">
                <li>{{ __('This reset link will expire in :minutes minutes for your security', ['minutes' => $expireTime ?? 60]) }}</li>
                <li>{{ __('If you did not request this password reset, no action is required') }}</li>
                <li>{{ __('Never share this reset link with anyone') }}</li>
            </ul>
        </div>

        <div class="instruction-box">
            <h3>{{ __('📝 To reset your password:') }}</h3>
            <ol class="rtl-list">
                <li>{{ __('Click the button below to access the password reset form') }}</li>
                <li>{{ __('Enter your new password (minimum 8 characters)') }}</li>
                <li>{{ __('Confirm your new password') }}</li>
                <li>{{ __('Save your changes') }}</li>
            </ol>
        </div>

        <div class="btn-container">
            <a href="{{ $resetUrl }}" class="btn">{{ __('Reset Password') }}</a>
        </div>

        <div class="expire-info">
            <strong>{{ __('⏰ Important:') }}</strong> {{ __('This link expires in :minutes minutes for your security.', ['minutes' => $expireTime ?? 60]) }}
        </div>

        <p><strong>{{ __('Alternative access:') }}</strong> {{ __('If the button above doesn\'t work, copy and paste this URL into your browser:') }}</p>
        <div class="url-fallback">{{ $resetUrl }}</div>

        <div class="warning">
            <strong>{{ __('⚠️ Didn\'t request this?') }}</strong><br>
            {{ __('If you did not request a password reset, please ignore this email. Your password will remain unchanged. For security concerns, contact our support team immediately.') }}
        </div>
    </div>
</x-emails.layout>
