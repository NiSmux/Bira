<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash; // Šis "importas" bus naudojamas žemiau

class VartotojasController extends Controller
{
    public function showRegistrationForm() {
    // Kelias: resources/views/prisijungimas/registracija/registracija.blade.php
    return view('prisijungimas.registracija.registracija');
    }

    public function register(Request $request) {
        $request->validate([
            'vardas' => 'required|string|max:120',
            'e_pastas' => 'required|email|unique:users,email',
            'slaptazodis' => 'required|min:6|confirmed',
            'role' => 'required|integer'
        ]);

        // ČIA PANAUDOJAMAS HASH:
        User::create([
            'name' => $request->vardas,
            'email' => $request->e_pastas,
            'password_hash' => Hash::make($request->slaptazodis),
            'role_id' => $request->role,
            'is_active' => 1
        ]);

        return redirect()->route('login')->with('success', 'Registration successful!');
    }

    public function showLoginForm() {
    if (Auth::check()) {
        return redirect()->route('pagrindinis');
    }
    return view('prisijungimas.registracija.prisijungimas');
}

    public function login(Request $request) {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Auth::attempt will automatically use the Hash class for checking in the background
        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']])) {
            $request->session()->regenerate();
            return redirect()->intended('/');
        }

        return back()->withErrors(['login_error' => 'Invalid credentials.']);
    }

    public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}