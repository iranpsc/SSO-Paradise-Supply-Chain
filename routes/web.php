<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PersonalInfoController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\Web3AuthController;

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

Route::middleware(['throttle:web3', 'guest'])->group(function () {
    Route::get('/web3/nonce', [Web3AuthController::class, 'getLoginNonce']);
    Route::post('/web3/verify', [Web3AuthController::class, 'verifySignature'])->name('web3.verify');
});

Route::middleware(['auth', 'verified', 'auth.session'])->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::middleware('throttle:web3')->group(function () {
        Route::get('/web3/link/nonce', [Web3AuthController::class, 'getLinkNonce'])->name('web3.link.nonce');
        Route::post('/web3/link', [Web3AuthController::class, 'linkWallet'])->name('web3.link');
    });
    Route::singleton('account', AccountController::class);
    Route::singleton('personal-info', PersonalInfoController::class);
    Route::get('/change-password', [NewPasswordController::class, 'showForm'])->name('password.edit');
    Route::put('/change-password', [NewPasswordController::class, 'update'])->name('password.new');
    Route::post('/users/import', [UserController::class, 'import'])->name('users.import');
});

Auth::routes();

Route::controller(VerificationController::class)->prefix('email')->as('verification.')->group(function () {
    Route::get('/verify', 'show')->name('notice');
    Route::post('/verification-notification', 'resend')->name('resend');
    Route::get('/verify/{id}/{hash}', 'verify')->name('verify');
});
