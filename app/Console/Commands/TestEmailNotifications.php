<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\CustomResetPasswordNotification;
use App\Notifications\CustomVerifyEmailNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmailNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:email-notifications {email} {--type=both} {--locale=en}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test custom email notifications (password reset and email verification) with locale support';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $type = $this->option('type');
        $locale = $this->option('locale');

        // Set the app locale for testing
        $originalLocale = app()->getLocale();
        app()->setLocale($locale);

        // Create a test user if it doesn't exist
        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => 'Test User', 'password' => bcrypt('password123')]
        );

        $this->info("Testing email notifications for: {$user->email}");
        $this->info("Using locale: {$locale} (direction: " . ($locale === 'fa' ? 'RTL' : 'LTR') . ")");

        if ($type === 'password' || $type === 'both') {
            $this->info('Testing password reset notification...');
            try {
                $user->sendPasswordResetNotification('test-token-123');
                $this->info('✅ Password reset notification sent successfully!');
            } catch (\Exception $e) {
                $this->error('❌ Failed to send password reset notification: ' . $e->getMessage());
            }
        }

        if ($type === 'verification' || $type === 'both') {
            $this->info('Testing email verification notification...');
            try {
                $user->sendEmailVerificationNotification();
                $this->info('✅ Email verification notification sent successfully!');
            } catch (\Exception $e) {
                $this->error('❌ Failed to send email verification notification: ' . $e->getMessage());
            }
        }

        // Restore original locale
        app()->setLocale($originalLocale);

        $this->info('Email notification testing completed!');
        $this->line('');
        $this->comment('Note: If using file driver, check storage/logs/laravel.log for email content.');
        $this->comment('If using mail trap or real SMTP, check your inbox.');
        $this->line('');
        $this->comment('Usage examples:');
        $this->comment('  php artisan test:email-notifications user@example.com --locale=en');
        $this->comment('  php artisan test:email-notifications user@example.com --locale=fa --type=password');
    }
}
