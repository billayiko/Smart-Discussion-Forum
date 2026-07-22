<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User as SocialiteUser;
use Throwable;

class SocialiteController extends Controller
{
    private const PROVIDERS = [
        'google' => 'Google',
        'github' => 'GitHub',
    ];

    public function redirect(string $provider): RedirectResponse
    {
        $driver = Socialite::driver($provider);

        if ($provider === 'github') {
            $driver->scopes(['read:user', 'user:email']);
        }

        return $driver->redirect();
    }

    public function callback(Request $request, string $provider): RedirectResponse
    {
        $label = self::PROVIDERS[$provider];

        if ($request->filled('error')) {
            return redirect()->route('login')->with('status', "{$label} sign-in was cancelled.");
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (InvalidStateException $e) {
            Log::warning('social.callback.invalid_state', ['provider' => $provider, 'message' => $e->getMessage()]);

            return redirect()->route('login')->withErrors([
                'email' => "Your {$label} sign-in session expired before it could complete. Please try again.",
            ]);
        } catch (Throwable $e) {
            Log::warning('social.callback.failure', ['provider' => $provider, 'message' => $e->getMessage()]);

            return redirect()->route('login')->withErrors([
                'email' => "We couldn't complete sign-in with {$label}. Please try again.",
            ]);
        }

        if (! $socialUser->getEmail()) {
            return redirect()->route('login')->withErrors([
                'email' => "Your {$label} account has no verified email address we can use. Please make your email public on {$label} or sign in another way.",
            ]);
        }

        $user = $this->findOrCreateUser($provider, $socialUser);

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        if ($user->role === 'member') {
            return redirect()->route('onboarding.edit');
        }

        return redirect()->route($user->dashboardRouteName());
    }

    private function findOrCreateUser(string $provider, SocialiteUser $socialUser): User
    {
        $idColumn = "{$provider}_id";

        if ($user = User::where($idColumn, $socialUser->getId())->first()) {
            return $user;
        }

        if ($user = User::where('email', $socialUser->getEmail())->first()) {
            $user->forceFill([
                $idColumn => $socialUser->getId(),
                'avatar' => $user->avatar ?: $socialUser->getAvatar(),
            ])->save();

            return $user;
        }

        return User::create([
            'name' => $socialUser->getName() ?: $socialUser->getNickname() ?: 'New Member',
            'email' => $socialUser->getEmail(),
            'password' => Hash::make(Str::random(40)),
            'role' => 'member',
            'email_verified_at' => now(),
            $idColumn => $socialUser->getId(),
            'avatar' => $socialUser->getAvatar(),
        ]);
    }
}
