<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'low') {
                $query->whereRaw('stock <= min_stock_alert');
            } elseif ($request->stock_status === 'out') {
                $query->where('stock', '<=', 0);
            }
        }

        $products = $query->latest()->paginate(12)->withQueryString();
        
        $totalProducts = Product::count();
        $lowStockCount = Product::whereRaw('stock <= min_stock_alert')->where('stock', '>', 0)->count();
        $outOfStockCount = Product::where('stock', '<=', 0)->count();
        $totalStockValue = Product::selectRaw('SUM(stock * price) as total')->value('total') ?? 0;

        return view('admin.products.index', compact(
            'products',
            'totalProducts',
            'lowStockCount',
            'outOfStockCount',
            'totalStockValue'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:50|unique:products,sku',
            'category' => 'required|in:drinks,supplements,snacks,gear,other',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'min_stock_alert' => 'nullable|integer|min:0',
            'unit' => 'required|string|max:30',
            'image' => 'nullable|image|max:2048',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['cost_price'] = $validated['cost_price'] ?? 0;
        $validated['min_stock_alert'] = $validated['min_stock_alert'] ?? 5;
        $validated['is_active'] = $request->has('is_active');

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $validated['image'] = $path;
        }

        Product::create($validated);

        return redirect()->route('admin.products.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:50|unique:products,sku,' . $product->id,
            'category' => 'required|in:drinks,supplements,snacks,gear,other',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'min_stock_alert' => 'nullable|integer|min:0',
            'unit' => 'required|string|max:30',
            'image' => 'nullable|image|max:2048',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['cost_price'] = $validated['cost_price'] ?? 0;
        $validated['min_stock_alert'] = $validated['min_stock_alert'] ?? 5;
        $validated['is_active'] = $request->has('is_active');

        if ($request->hasFile('image')) {
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            $path = $request->file('image')->store('products', 'public');
            $validated['image'] = $path;
        }

        $product->update($validated);

        return redirect()->route('admin.products.index')
            ->with('success', 'Data produk berhasil diperbarui.');
    }

    public function restock(Request $request, Product $product)
    {
        $request->validate([
            'added_stock' => 'required|integer|min:1',
        ]);

        $product->increment('stock', $request->added_stock);

        return back()->with('success', "Stok untuk {$product->name} berhasil ditambah (+{$request->added_stock}).");
    }

    public function destroy(Product $product)
    {
        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Produk berhasil dihapus.');
    }
}
