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
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ];

        // Kalau ini bukan pemaksaan ganti password pertama kali, wajib masukkan password lama dulu.
        if (! $user->must_change_password) {
            $rules['current_password'] = ['required', 'string'];
        }

        $data = $request->validate($rules);

        if (! $user->must_change_password && ! Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Password lama yang kamu masukkan salah.']);
        }

        $user->update([
            'password' => Hash::make($data['password']),
            'must_change_password' => false,
        ]);

        return redirect()->route('member.dashboard')->with('success', 'Password berhasil diganti.');
    }
}
