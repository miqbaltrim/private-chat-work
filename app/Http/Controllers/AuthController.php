<?php
// app/Http/Controllers/AuthController.php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) return redirect('/chat');
        return view('auth.login');
    }

    public function showRegister()
    {
        if (Auth::check()) return redirect('/chat');
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|string|min:3|max:20|unique:users|alpha_dash',
            'display_name' => 'required|string|min:2|max:50',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = new User();
        $user->username = $request->input('username');
        $user->display_name = $request->input('display_name');
        $user->password = Hash::make($request->input('password'));
        $user->is_online = true;
        $user->save();

        Auth::login($user);
        return redirect('/chat');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::attempt(['username' => $request->username, 'password' => $request->password])) {
            $request->session()->regenerate();
            Auth::user()->update(['is_online' => true]);
            return redirect('/chat');
        }

        return back()->withErrors(['username' => 'Username atau password salah.']);
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            Auth::user()->update(['is_online' => false, 'last_seen' => now()]);
        }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    public function updatePublicKey(Request $request)
    {
        try {
            $request->validate(['public_key' => 'required|string']);
            Auth::user()->update(['public_key' => $request->input('public_key')]);
            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {
            Log::error('updatePublicKey error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}