<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LockController extends Controller
{
    public function showLock()
    {
        if (!session()->has('locked_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.lock-screen');
    }

    public function lock(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        session()->put('locked_user_id', $user->id);
        session()->put('locked_user_name', $user->name);
        session()->put('locked_user_email', $user->email);
        session()->put('locked_intended_url', url()->previous());

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('lock.screen');
    }

    public function unlock(Request $request)
    {
        $request->validate([
            'pin_code' => 'required|string|digits:4',
        ]);

        $userId = session('locked_user_id');

        if (!$userId) {
            return redirect()->route('login');
        }

        $user = \App\Models\User::find($userId);

        if (!$user || !Hash::check($request->pin_code, $user->pin_code)) {
            return back()->withErrors(['pin_code' => 'Invalid PIN.'])->onlyInput('pin_code');
        }

        $intendedUrl = session('locked_intended_url');

        Auth::login($user);
        $request->session()->regenerate();

        session()->forget(['locked_user_id', 'locked_user_name', 'locked_user_email', 'locked_intended_url']);

        return $intendedUrl && $intendedUrl !== url('lock')
            ? redirect($intendedUrl)
            : redirect()->route('dashboard');
    }
}
