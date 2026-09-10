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
     * Kalau foto tersimpan sebagai URL penuh (disk 's3' / Supabase Storage),
     * langsung redirect ke URL publiknya. Kalau masih path relatif lama
     * (disk lokal 'public', dari sebelum migrasi ke S3), tetap di-serve
     * lewat controller seperti sebelumnya.
     */
    public function show(Request $request)
    {
        $member = $request->user()->member()->firstOrFail();

        if (! $member->photo) {
            abort(404);
        }

        if (str_starts_with($member->photo, 'http://') || str_starts_with($member->photo, 'https://')) {
            return redirect($member->photo);
        }

        if (! Storage::disk('public')->exists($member->photo)) {
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

        $disk = config('filesystems.default', 'public');

        try {
            $path = $request->file('photo')->store('members', $disk);
        } catch (\Throwable $e) {
            return back()->with('error', 'Upload foto gagal: ' . $e->getMessage());
        }

        $newPhoto = $disk === 's3' ? Storage::disk('s3')->url($path) : $path;

        $member->update(['photo' => $newPhoto]);

        if ($oldPhoto) {
            if (str_starts_with($oldPhoto, 'http://') || str_starts_with($oldPhoto, 'https://')) {
                $s3BaseUrl = rtrim((string) config('filesystems.disks.s3.url'), '/');
                if ($s3BaseUrl && str_starts_with($oldPhoto, $s3BaseUrl)) {
                    try {
                        Storage::disk('s3')->delete(ltrim(substr($oldPhoto, strlen($s3BaseUrl)), '/'));
                    } catch (\Throwable $e) {}
                }
            } elseif (Storage::disk('public')->exists($oldPhoto)) {
                Storage::disk('public')->delete($oldPhoto);
            }
        }

        return back()->with('success', 'Foto profil berhasil diperbarui.');
    }
}