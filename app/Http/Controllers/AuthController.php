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

    public function showMobileLogin()
    {
        if (Auth::check()) {
            if (Auth::user()->isDosen()) {
                return redirect()->route('dosen.presensi-mobile');
            }
            return $this->redirectBasedOnRole(Auth::user());
        }
        return view('auth.mobile-login');
    }

    public function mobileLogin(Request $request)
    {
        $inputPhone = trim($request->input('no_hp', $request->input('login', '')));
        $password = (string) $request->input('password', '');
        $remember = $request->boolean('remember', true);

        if (empty($inputPhone)) {
            return back()->withErrors([
                'no_hp' => 'Silakan masukkan nomor HP Anda yang terdaftar.',
            ])->withInput();
        }

        $cleanPhone = preg_replace('/[^0-9]/', '', $inputPhone);
        if (strlen($cleanPhone) < 6) {
            return back()->withErrors([
                'no_hp' => 'Format nomor HP tidak valid (minimal 6 digit angka).',
            ])->withInput();
        }

        $last8 = substr($cleanPhone, -8);
        $dosen = \App\Models\Dosen::where('no_hp', $inputPhone)
            ->orWhere('no_hp', $cleanPhone)
            ->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE(no_hp, '-', ''), ' ', ''), '+', ''), '.', '') LIKE ?", ['%' . $last8])
            ->first();

        if (!$dosen || !$dosen->user) {
            return back()->withErrors([
                'no_hp' => 'Nomor HP "' . $inputPhone . '" tidak terdaftar sebagai dosen atau karyawan.',
            ])->withInput();
        }

        $user = $dosen->user;

        if (!$user->is_active) {
            return back()->withErrors([
                'no_hp' => 'Akun Anda sedang dinonaktifkan. Hubungi admin BAAK / Kepegawaian.',
            ])->withInput();
        }

        // 6 digit dari belakang nomor HP terdaftar
        $cleanDosenPhone = preg_replace('/[^0-9]/', '', $dosen->no_hp ?? '');
        $sixDigits = (strlen($cleanDosenPhone) >= 6) ? substr($cleanDosenPhone, -6) : null;

        // Jika password di form kosong, otomatis gunakan 6 digit belakang no HP
        if (empty($password) && $sixDigits) {
            $password = $sixDigits;
        }

        $passwordValid = false;
        if (\Illuminate\Support\Facades\Hash::check($password, $user->password)) {
            $passwordValid = true;
        } elseif ($sixDigits && $password === $sixDigits) {
            $passwordValid = true;
            $user->update([
                'password' => \Illuminate\Support\Facades\Hash::make($sixDigits),
            ]);
        }

        if ($passwordValid) {
            Auth::login($user, $remember);
            $request->session()->regenerate();
            return redirect()->route('dosen.presensi-mobile');
        }

        return back()->withErrors([
            'no_hp' => 'Kata sandi / PIN yang Anda masukkan salah. Kata sandi default adalah 6 digit terakhir nomor HP Anda.',
        ])->withInput();
    }

    public function login(Request $request)
    {
        $inputLogin = trim($request->input('login', $request->input('email', '')));
        $password = (string) $request->input('password', '');
        $remember = $request->boolean('remember');

        if (empty($inputLogin)) {
            return back()->withErrors([
                'login' => 'Nomor HP atau Email wajib diisi.',
            ])->withInput();
        }

        $user = null;
        $dosen = null;
        $isPhone = false;

        // Cek apakah input berupa format nomor HP atau email
        if (!filter_var($inputLogin, FILTER_VALIDATE_EMAIL)) {
            $isPhone = true;
            $cleanInputPhone = preg_replace('/[^0-9]/', '', $inputLogin);

            if (strlen($cleanInputPhone) >= 6) {
                // Ambil 8 digit terakhir untuk mencocokkan berbagai awalan (08, 628, +62)
                $last8 = substr($cleanInputPhone, -8);
                $dosen = \App\Models\Dosen::where('no_hp', $inputLogin)
                    ->orWhere('no_hp', $cleanInputPhone)
                    ->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE(no_hp, '-', ''), ' ', ''), '+', ''), '.', '') LIKE ?", ['%' . $last8])
                    ->first();

                if ($dosen && $dosen->user) {
                    $user = $dosen->user;
                }
            }
        } else {
            // Pencarian berdasarkan Email
            $user = \App\Models\User::where('email', $inputLogin)->first();
            if ($user && $user->dosen) {
                $dosen = $user->dosen;
            }
        }

        if (!$user) {
            return back()->withErrors([
                'login' => 'Akun dengan ' . ($isPhone ? 'Nomor HP terdaftar' : 'Email') . ' "' . $inputLogin . '" tidak ditemukan.',
            ])->withInput();
        }

        if (!$user->is_active) {
            return back()->withErrors([
                'login' => 'Akun Anda sedang dinonaktifkan. Silakan hubungi bagian BAAK / Kepegawaian.',
            ])->withInput();
        }

        // Hitung password default 6 digit terakhir nomor HP jika dosen memiliki no HP
        $phoneSixDigits = null;
        if ($dosen && !empty($dosen->no_hp)) {
            $cleanDosenPhone = preg_replace('/[^0-9]/', '', $dosen->no_hp);
            if (strlen($cleanDosenPhone) >= 6) {
                $phoneSixDigits = substr($cleanDosenPhone, -6);
            }
        }

        // Jika password di form tidak diisi dan no HP memiliki default 6 digit, otomatis gunakan 6 digit tersebut
        if (empty($password) && $phoneSixDigits) {
            $password = $phoneSixDigits;
        }

        // Verifikasi password:
        // 1. Cocokkan dengan Hash password akun saat ini
        // 2. ATAU cocokkan dengan 6 digit terakhir nomor HP (default password dosen)
        $passwordValid = false;
        if (\Illuminate\Support\Facades\Hash::check($password, $user->password)) {
            $passwordValid = true;
        } elseif ($phoneSixDigits && $password === $phoneSixDigits) {
            $passwordValid = true;
            // Sinkronkan hash password user ke 6 digit belakang no HP
            $user->update([
                'password' => \Illuminate\Support\Facades\Hash::make($phoneSixDigits),
            ]);
        }

        if ($passwordValid) {
            Auth::login($user, $remember);
            $request->session()->regenerate();
            return $this->redirectBasedOnRole($user);
        }

        $errorMessage = 'Kata sandi yang Anda masukkan salah.';
        if ($phoneSixDigits) {
            $errorMessage .= ' Kata sandi default akun Anda adalah 6 digit terakhir nomor HP terdaftar.';
        }

        return back()->withErrors([
            'login' => $errorMessage,
        ])->withInput();
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
        $fromMobile = $request->input('from') === 'mobile' || ($request->header('referer') && str_contains($request->header('referer'), 'mobile'));
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($fromMobile) {
            return redirect()->route('presensi.login')->with('success', 'Anda telah berhasil keluar dari sesi Presensi Mobile.');
        }

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
