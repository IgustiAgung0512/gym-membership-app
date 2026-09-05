<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StoreController extends Controller
{
    public function index(Request $request)
    {
        $member = Auth::user()->member;

        $query = Product::where('is_active', true);

        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('category')->orderBy('name')->get();

        $categories = [
            'all' => ['label' => 'Semua Produk', 'icon' => '🏷️'],
            'drinks' => ['label' => 'Minuman & Elektrolit', 'icon' => '🥤'],
            'supplements' => ['label' => 'Suplemen & Whey', 'icon' => '💪'],
            'snacks' => ['label' => 'Camilan Sehat', 'icon' => '🥑'],
            'gear' => ['label' => 'Aksesoris Gym', 'icon' => '🏋️'],
        ];

        return view('member.store.index', compact('products', 'categories', 'member'));
    }
}
