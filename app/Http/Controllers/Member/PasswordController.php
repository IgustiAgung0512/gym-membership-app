<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    public function edit(Request $request)
    {
        return view('member.password', [
            'forceChange' => $request->user()->must_change_password,
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $rules = [
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];

        // Kalau ini bukan pemaksaan ganti password pertama kali, wajib masukkan password lama dulu.
        if (! $user->must_change_password) {
            $rules['current_password'] = ['required', 'string'];
        }

        $messages = [
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal harus 8 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'current_password.required' => 'Password saat ini wajib diisi.',
        ];

        $data = $request->validate($rules, $messages);

        if (! $user->must_change_password && ! Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Password lama yang kamu masukkan salah.']);
        }

        $user->update([
            'password' => Hash::make($data['password']),
            'must_change_password' => false,
        ]);

        $request->session()->regenerate();

        return redirect()->route('member.dashboard')->with('success', 'Password berhasil diperbarui dengan aman.');
    }
}
