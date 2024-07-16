<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PersonalInfoController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::redirect('/', '/home');

Route::middleware(['auth' , 'verified' ])->group(function() {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::singleton('account', AccountController::class);
    Route::singleton('personal-info', PersonalInfoController::class);
    Route::get('/change-password', [NewPasswordController::class, 'showForm'])->name('password.edit');
    Route::put('/change-password', [NewPasswordController::class, 'update'])->name('password.new');
});


Auth::routes();

Route::view('/logout', 'auth.logout')->middleware('auth');

Route::controller(VerificationController::class)->prefix('email')->as('verification.')->group(function () {
    Route::get('/verify', 'show')->name('notice');
    Route::post('/verification-notification', 'resend')->name('resend');
    Route::get('/verify/{id}/{hash}', 'verify')->name('verify');
});
