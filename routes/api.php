<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/users/get', function (Request $request) {

    $token = '#x#NmgxBHV3KA(4c)EpzH$xxY(C@%Z)uthcLvJM&';

    if (!$request->has('token') || $request->token !== $token) {
        throw ValidationException::withMessages([
            'token' => 'Invalid token',
        ]);
    }

    try {
        $request = Http::asForm()->post('https://api.rgb.irpsc.com/api/users/get', [
            'token' => 'K^mLq%k5wY*T9WIHC%dpyqph57x^gfeTjs(2WSZV',
        ]);

        $users = json_decode($request->body(), true);

        $users = collect($users)->map(function ($user) {
            $kyc = $user['kyc'];

            $user = [
                'name' => $user['name'],
                'email' => $user['email'],
                'mobile' => $user['phone'],
                'password' => $user['password'],
                'code' => $user['code'],
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $user = User::updateOrCreate([
                'email' => $user['email'],
            ], $user);

            $user->markEmailAsVerified();

            $user->personalInfo()->updateOrCreate([
                'first_name' => $kyc['fname'] ?? '',
                'last_name' => $kyc['lname'] ?? '',
                'mobile' => $user->mobile,
                'address' => $kyc['address'] ?? '',
                'national_code' => $kyc['melli_code'] ?? '',
                'is_verified' => $kyc['status'] ?? 0,
            ]);

            return $user;
        });
    } catch (\Throwable $th) {
        return response()->json([
            'status' => 'error',
            'message' => $th->getMessage(),
        ]);
    }

    return response()->json([
        'status' => 'success',
        'message' => 'Users retrieved successfully',
    ]);
});
