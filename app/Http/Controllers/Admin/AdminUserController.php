<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminUserController extends Controller
{
    /**
     * Tampilkan semua admin & kasir.
     */
    public function index()
    {
        $admins  = User::where('role', 'admin')->orderBy('name')->get();
        $cashiers = User::where('role', 'cashier')->orderBy('name')->get();

        return view('admin.user-management.index', compact('admins', 'cashiers'));
    }

    /**
     * Simpan admin baru.
     */
    public function storeAdmin(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'phone'    => ['required', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'phone'    => $data['phone'],
            'password' => Hash::make($data['password']),
            'role'     => 'admin',
        ]);

        return redirect()->route('admin.user-management.index')
            ->with('success', "Admin \"{$data['name']}\" berhasil ditambahkan.");
    }

    /**
     * Tambah kasir baru.
     */
    public function storeCashier(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'phone'    => ['required', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'phone'    => $data['phone'],
            'password' => Hash::make($data['password']),
            'role'     => 'cashier',
        ]);

        return redirect()->route('admin.user-management.index')
            ->with('success', "Kasir \"{$data['name']}\" berhasil ditambahkan.");
    }

    /**
     * Ganti password admin atau kasir.
     */
    public function changePassword(Request $request, User $user)
    {
        // Pastikan target hanya admin atau kasir (bukan member)
        if ($user->role === 'member') {
            abort(403, 'Aksi ini tidak diizinkan untuk akun member.');
        }

        $rules = [
            'password' => ['required', 'confirmed', Password::min(8)],
        ];

        // Jika admin ganti password dirinya sendiri, wajib input password lama
        if ($user->id === auth()->id()) {
            $rules['current_password'] = ['required', function ($attribute, $value, $fail) {
                if (! Hash::check($value, auth()->user()->password)) {
                    $fail('Password lama tidak sesuai.');
                }
            }];
        }

        $request->validate($rules);

        $user->update([
            'password'            => Hash::make($request->password),
            'must_change_password' => false,
        ]);

        $label = $user->id === auth()->id() ? 'Password Anda' : "Password {$user->name}";

        return redirect()->route('admin.user-management.index')
            ->with('success', "{$label} berhasil diubah.");
    }

    /**
     * Hapus admin (tidak boleh hapus diri sendiri).
     */
    public function destroyAdmin(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak bisa menghapus akun Anda sendiri.');
        }

        if ($user->role !== 'admin') {
            abort(403, 'Hanya akun admin yang bisa dihapus melalui halaman ini.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('admin.user-management.index')
            ->with('success', "Admin \"{$name}\" berhasil dihapus.");
    }

    /**
     * Hapus kasir.
     */
    public function destroyCashier(User $user)
    {
        if ($user->role !== 'cashier') {
            abort(403, 'Hanya akun kasir yang bisa dihapus melalui halaman ini.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('admin.user-management.index')
            ->with('success', "Kasir \"{$name}\" berhasil dihapus.");
    }
}
