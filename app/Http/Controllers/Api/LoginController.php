<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    use ThrottlesLogins;

    /**
     * Login the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|' . $this->username(),
            'password' => 'required|string',
        ]);

        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            return $this->sendLockoutResponse($request);
        }

        if (Auth::attempt($request->only('email', 'password'))) {
            $request->session()->regenerate();

            return response()->json([
                'message' => 'Login successful',
                'token' => $request->user()->createToken('api')->plainTextToken,
            ]);
        }

        $this->incrementLoginAttempts($request);
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    /**
     * Get the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(Request $request)
    {
        return new UserResource($request->user());
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }

    /**
     * Get username to authentication
     *
     * @return string
     */
    public function username()
    {
        return 'email';
    }
}
