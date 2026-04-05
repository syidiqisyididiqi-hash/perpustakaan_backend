<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    /**
     * Mengarahkan user ke halaman login Google.
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Menangani data yang dikirim balik oleh Google.
     */
    public function handleGoogleCallback()
    {
        try {
            /** @var \Laravel\Socialite\Two\User $user */
            $user = Socialite::driver('google')->user();

            $finduser = User::where('google_id', $user->id)->first();

            if ($finduser) {
                $authUser = $finduser;
            } else {
                $existingUser = User::where('email', $user->email)->first();

                if ($existingUser) {
                    $existingUser->update(['google_id' => $user->id]);
                    $authUser = $existingUser;
                } else {
                    $authUser = User::create([
                        'name' => $user->name,
                        'email' => $user->email,
                        'google_id' => $user->id,
                        'password' => null,
                    ]);
                }
            }

            Auth::login($authUser);
            $token = $authUser->createToken('auth_token')->plainTextToken;

            return redirect()->away("http://localhost:3000/member?token={$token}");

        } catch (Exception $e) {
            return redirect()->away('http://localhost:3000/login?error=google_failed');
        }
    }
}