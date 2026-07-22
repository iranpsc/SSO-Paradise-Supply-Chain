<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Passport\Client;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Cache;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:50', 'not_regex:/^(HM-|hm-|Hm-|hM-).*$/'],
            'email' => ['required', 'string', 'email:filter', 'max:255', 'unique:users'],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)->mixedCase()->numbers()->symbols(),
                'max:40',
            ],
            'client_id' => ['nullable', 'exists:oauth_clients,id'],
            'redirect_uri' => [
                'nullable',
                'url',
                function ($attribute, $value, $fail) use ($data) {
                    // Passport stores redirect URIs as a comma-separated string in the
                    // "redirect" text column, so match against the set instead of JSON.
                    // When client_id is present, the URI must belong to that client.
                    $query = Client::query()->whereRaw('FIND_IN_SET(?, `redirect`)', [$value]);

                    if (! empty($data['client_id'])) {
                        $query->where('id', $data['client_id']);
                    }

                    if (! $query->exists()) {
                        $fail(__('validation.exists', ['attribute' => $attribute]));
                    }
                },
            ],
            'back_url' => ['nullable', 'url'],
            'referral' => ['nullable', 'string', 'exists:users,code'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'referral' => ! empty($data['referral']) ? $data['referral'] : null,
        ]);
    }

    /**
     * The user has been registered.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     */
    protected function registered(Request $request, $user)
    {
        Cache::put('back_url_' . $user->id, $request->input('back_url'), now()->addHour());

        $user->personalInfo()->create();

        return redirect()->route('home');
    }
}
