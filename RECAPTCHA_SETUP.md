# Cloudflare reCAPTCHA Setup Guide

## Configuration

The reCAPTCHA functionality is already configured with the provided Cloudflare Turnstile keys:

- **Site Key**: `0x4AAAAAABtLmkXv5fAhWk16`
- **Secret Key**: `0x4AAAAAABtLmsmeaIITLCBef4iUZMvb0Aw`

## Environment Variables

Add the following to your `.env` file:

```env
# Cloudflare reCAPTCHA Configuration
RECAPTCHA_ENABLED=true
RECAPTCHA_SITE_KEY=0x4AAAAAABtLmkXv5fAhWk16
RECAPTCHA_SECRET_KEY=0x4AAAAAABtLmsmeaIITLCBef4iUZMvb0Aw
```

## Features Implemented

### 1. Server-Side Validation
- Custom validation rule for reCAPTCHA verification
- Integration with Laravel's validation system
- Proper error handling and logging

### 2. Client-Side Integration
- Cloudflare Turnstile widget on all authentication forms
- Automatic reset on form errors
- Responsive design integration

### 3. Forms Protected
- User Registration
- User Login
- Password Reset Request

### 4. Error Messages
- English and Persian language support
- User-friendly error messages
- Proper validation feedback

### 5. Testing
- Comprehensive test suite
- Mocked reCAPTCHA service for testing
- Edge case coverage

## Usage

### Enable/Disable reCAPTCHA
Set `RECAPTCHA_ENABLED=true` in your `.env` file to enable reCAPTCHA protection.

### Customization
- Modify error messages in `lang/en/validation.php` and `lang/fa/validation.php`
- Adjust reCAPTCHA settings in `config/recaptcha.php`
- Customize widget appearance using Cloudflare Turnstile options

## Testing

Run the reCAPTCHA tests:

```bash
php artisan test tests/Feature/RecaptchaTest.php
```

## Security Features

- Server-side token verification
- IP address logging
- Comprehensive error logging
- Rate limiting integration ready
- CSRF protection maintained

## Troubleshooting

1. **reCAPTCHA not showing**: Check if `RECAPTCHA_ENABLED=true` in `.env`
2. **Verification failing**: Verify your Cloudflare Turnstile keys
3. **JavaScript errors**: Ensure Cloudflare Turnstile script is loading
4. **Mobile issues**: Cloudflare Turnstile is mobile-responsive by default

## Notes

- The implementation uses Cloudflare Turnstile (reCAPTCHA alternative)
- All authentication forms are protected
- Graceful fallback when reCAPTCHA is disabled
- Multi-language support included
