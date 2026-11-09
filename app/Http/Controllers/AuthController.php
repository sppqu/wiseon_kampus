<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
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
            $request->session()->regenerate();
            return $this->redirectBasedOnRole(Auth::user());
        }

        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    public function quickLogin(Request $request)
    {
        $role = $request->input('role', 'dosen');
        $emails = [
            'dosen' => 'dosen@kampus.ac.id',
            'dosen2' => 'dosen2@kampus.ac.id',
            'kaprodi' => 'kaprodi@kampus.ac.id',
            'admin' => 'admin@kampus.ac.id',
        ];

        $email = $emails[$role] ?? 'dosen@kampus.ac.id';
        $user = \App\Models\User::where('email', $email)->first();

        if ($user) {
            Auth::login($user);
            $request->session()->regenerate();
            return $this->redirectBasedOnRole($user);
        }

        return redirect()->route('login');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar dari sistem.');
    }

    protected function redirectBasedOnRole($user)
    {
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif ($user->role === 'kaprodi') {
            return redirect()->route('kaprodi.dashboard');
        } else {
            return redirect()->route('dosen.dashboard');
        }
    }
}
