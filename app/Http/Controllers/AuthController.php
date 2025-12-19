<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // --- LOGIN ---
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();
            if ($user) {
                Booking::whereNull('user_id')
                    ->where('guest_email', $user->email)
                    ->update(['user_id' => $user->id]);
            }

            // Redirect based on role
            if (Auth::user()->role === 'admin') {
                return redirect()->intended('/admin/dashboard');
            }

            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    // --- REGISTER ---
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed', // checks password_confirmation field
            'dob' => 'required|date|before:today',
        ]);

        $age = Carbon::parse($request->dob)->age;

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'age' => $age,
        ]);

        // role is intentionally not mass-assignable; set explicitly
        $user->role = 'user';
        $user->save();

        Booking::whereNull('user_id')
            ->where('guest_email', $user->email)
            ->update(['user_id' => $user->id]);

        Auth::login($user);

        return redirect('/');
    }

    // --- LOGOUT ---
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
