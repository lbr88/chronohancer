<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    /**
     * Redirect the user to the provider authentication page.
     *
     * @param  string  $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle provider callback and authenticate the user.
     *
     * @param  string  $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function callback($provider)
    {
        try {
            $providerUser = Socialite::driver($provider)->user();

            // Find or create user
            $user = User::findOrCreateFromSocialite($provider, $providerUser);

            // Log the user in
            Auth::login($user);

            // Regenerate session
            session()->regenerate();

            return redirect()->route('dashboard');

        } catch (Exception $e) {
            return redirect()->route('login')
                ->withErrors(['error' => 'An error occurred during '.$provider.' authentication: '.$e->getMessage()]);
        }
    }
}
