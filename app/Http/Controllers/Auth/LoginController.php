<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = \App\Models\User::where('email', $request->email)
            ->where('is_active', true)
            ->where('status', 'active')
            ->first();

        if (!$user || !Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Invalid credentials or account inactive.'])->onlyInput('email');
        }

        return $this->redirectBasedOnRole($user);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    public function showPinForm()
    {
        return view('auth.pin-login');
    }

    public function pinLogin(Request $request)
    {
        $request->validate([
            'pin_code' => 'required|string|digits:4',
        ]);

        $user = \App\Models\User::where('is_active', true)
            ->where('status', 'active')
            ->get()
            ->first(fn ($u) => Hash::check($request->pin_code, $u->pin_code));

        if (!$user) {
            return back()->withErrors(['pin_code' => 'Invalid PIN code.'])->onlyInput('pin_code');
        }

        Auth::login($user);
        return $this->redirectBasedOnRole($user);
    }

    protected function redirectBasedOnRole($user)
    {
        return match ($user->role?->name) {
            'Admin', 'Manager', 'Super Admin' => redirect()->route('dashboard'),
            'Cashier' => redirect()->route('pos.index'),
            'Waiter' => redirect()->route('waiter.index'),
            'Kitchen Staff' => redirect()->route('kitchen.index'),
            default => redirect()->route('dashboard'),
        };
    }
}
