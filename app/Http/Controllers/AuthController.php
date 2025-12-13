<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('shop.index');
        }

        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:6',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        Auth::login($user);
        try {
            $request->session()->regenerate();
        } catch (\Exception $e) {
            logger()->error('Session regeneration failed during register: ' . $e->getMessage());
        }

        return redirect()->route('shop.index')->with('success', 'Account created and logged in.');
    }

    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('shop.index');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            try {
                $request->session()->regenerate();
            } catch (\Exception $e) {
                // Log the session regeneration error but continue (do not expose internal error to users)
                logger()->error('Session regeneration failed during login: ' . $e->getMessage());
            }
            return redirect()->intended(route('shop.index'))->with('success', 'Logged in.');
        }

        return back()->with('error', 'Login failed — check your credentials.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        try {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        } catch (\Exception $e) {
            logger()->error('Session invalidation failed during logout: ' . $e->getMessage());
        }
        return redirect()->route('shop.index')->with('success', 'Logged out.');
    }
}
