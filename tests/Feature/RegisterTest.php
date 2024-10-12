<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_can_register_successfully()
    {
        // داده‌های ثبت‌نام
        $userData = [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'referral' => null,
            'client_id' => null,
            'redirect_uri' => null,  // اضافه کردن مقدار null برای جلوگیری از خطا
            'back_url' => null,
        ];

        // ارسال درخواست ثبت‌نام
        $response = $this->post('/register', $userData);

        // بررسی ریدایرکت به صفحه تایید ایمیل
        $response->assertRedirect('/email/verify');

        // بررسی ذخیره شدن کاربر در دیتابیس
        $this->assertDatabaseHas('users', [
            'email' => 'testuser@example.com',
        ]);

        // بررسی رمز عبور هش شده در دیتابیس
        $this->assertTrue(Hash::check('Password123!', User::first()->password));
    }

    /** @test */
    public function it_requires_a_valid_email()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'redirect_uri' => null,  // اضافه کردن مقدار null
        ];

        // ارسال درخواست با ایمیل نامعتبر
        $response = $this->post('/register', $userData);

        // بررسی خطای اعتبارسنجی ایمیل
        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function it_requires_a_password_confirmation()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'wrong_password',
            'redirect_uri' => null,  // اضافه کردن مقدار null
        ];

        // ارسال درخواست با تاییدیه رمز اشتباه
        $response = $this->post('/register', $userData);

        // بررسی خطای اعتبارسنجی رمز عبور
        $response->assertSessionHasErrors(['password']);
    }

    /** @test */
    public function it_requires_a_unique_email()
    {
        // ایجاد یک کاربر تست در دیتابیس
        User::create([
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        // داده‌های تکراری ثبت‌نام
        $userData = [
            'name' => 'Test User',
            'email' => 'testuser@example.com',  // ایمیل تکراری
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'redirect_uri' => null,
        ];

        // ارسال درخواست با ایمیل تکراری
        $response = $this->post('/register', $userData);

        // بررسی خطای اعتبارسنجی ایمیل تکراری
        $response->assertSessionHasErrors(['email']);
    }
}
