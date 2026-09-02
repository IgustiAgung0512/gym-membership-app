<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MembershipPackage;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function index()
    {
        $packages = MembershipPackage::withCount('members')->orderBy('duration_months')->get();

        return view('admin.packages.index', compact('packages'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'duration_months' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
        ]);

        MembershipPackage::create($data);

        return back()->with('success', 'Paket membership berhasil ditambahkan.');
    }

    public function update(Request $request, MembershipPackage $package)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'duration_months' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $package->update($data);

        return back()->with('success', 'Paket membership berhasil diperbarui.');
    }

    public function destroy(MembershipPackage $package)
    {
        $package->delete();

        return back()->with('success', 'Paket membership berhasil dihapus.');
    }
}
