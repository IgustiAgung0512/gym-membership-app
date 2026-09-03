<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PhotoController extends Controller
{
    /**
     * Tampilkan foto profil member yang sedang login.
     *
     * Di-serve langsung lewat controller (bukan lewat symlink public/storage)
     * supaya tetap jalan walau `php artisan storage:link` belum/tidak bisa
     * dijalankan (sering terjadi di Windows/Laragon/XAMPP tanpa privilege admin).
     */
    public function show(Request $request)
    {
        $member = $request->user()->member()->firstOrFail();

        if (! $member->photo || ! Storage::disk('public')->exists($member->photo)) {
            abort(404);
        }

        return Storage::disk('public')->response($member->photo);
    }

    /**
     * Ganti foto profil member yang sedang login.
     */
    public function update(Request $request)
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ], [
            'photo.required' => 'Silakan pilih foto terlebih dahulu.',
            'photo.image' => 'File yang diunggah harus berupa gambar.',
            'photo.mimes' => 'Format foto harus JPG, PNG, atau WEBP.',
            'photo.max' => 'Ukuran foto maksimal 2MB.',
        ]);

        $member = $request->user()->member()->firstOrFail();

        $oldPhoto = $member->photo;

        $path = $request->file('photo')->store('members', 'public');

        $member->update(['photo' => $path]);

        if ($oldPhoto && Storage::disk('public')->exists($oldPhoto)) {
            Storage::disk('public')->delete($oldPhoto);
        }

        return back()->with('success', 'Foto profil berhasil diperbarui.');
    }
}
