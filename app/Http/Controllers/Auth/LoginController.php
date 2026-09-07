<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $throttleKey = Str::transliterate(Str::lower($request->input('email')) . '|' . $request->ip());

        // Proteksi Brute-Force: Cek apakah melebihi batas 5x percobaan gagal
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'email' => "Terlalu banyak percobaan login gagal. Silakan tunggu {$seconds} detik sebelum mencoba kembali.",
            ])->onlyInput('email');
        }

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            // Tambahkan hit kegagalan
            RateLimiter::hit($throttleKey, 60);

            $remaining = RateLimiter::retriesLeft($throttleKey, 5);
            $warning = $remaining > 0
                ? "Email atau password salah. (Sisa percobaan: {$remaining})"
                : "Email atau password salah. Batas percobaan habis, akun terkunci sementara selama 1 menit.";

            return back()->withErrors([
                'email' => $warning,
            ])->onlyInput('email');
        }

        // Reset rate limiter setelah login berhasil
        RateLimiter::clear($throttleKey);

        $request->session()->regenerate();

        $user = Auth::user();
        if ($user->role === 'admin') {
            return redirect()->intended(route('admin.dashboard'));
        } elseif ($user->role === 'cashier') {
            return redirect()->intended(route('cashier.pos.index'));
        } else {
            return redirect()->intended(route('member.dashboard'));
        }
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
