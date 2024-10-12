<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_can_login_successfully()
    {
        // ایجاد یک کاربر برای تست
        $user = User::create([
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        // ارسال درخواست ورود با اعتبارنامه‌های درست
        $response = $this->post('/login', [
            'email' => 'testuser@example.com',
            'password' => 'Password123!',
        ]);

        // بررسی ریدایرکت به صفحه خانه یا هر مقصد مشخص شده
        $response->assertRedirect('/home');

        // تایید اینکه کاربر وارد شده است
        $this->assertAuthenticatedAs($user);

        // نمایش نام کاربر
        echo 'Logged in as: ' . $user->name . "\n";
    }

    /** @test */
    public function it_fails_with_invalid_credentials()
    {
        // ایجاد یک کاربر برای تست
        $user = User::create([
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        // ارسال درخواست ورود با رمز اشتباه
        $response = $this->post('/login', [
            'email' => 'testuser@example.com',
            'password' => 'wrongpassword',
        ]);

        // بررسی عدم ورود و نمایش خطا
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /** @test */
    public function it_requires_email_field_to_login()
    {
        // ارسال درخواست ورود با ایمیل خالی
        $response = $this->post('/login', [
            'email' => '',  // ایمیل خالی
            'password' => 'Password123!',
        ]);

        // بررسی خطای اعتبارسنجی ایمیل
        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function a_user_can_logout_successfully()
    {
        // ایجاد یک کاربر برای تست
        $user = User::create([
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        // شبیه‌سازی ورود کاربر
        $this->actingAs($user);

        // ارسال درخواست خروج
        $response = $this->post('/logout');

        // تایید اینکه کاربر خارج شده است
        $this->assertGuest();
    }
}
