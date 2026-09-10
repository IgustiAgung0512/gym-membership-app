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
            try {
                $validated['image'] = $this->uploadProductImage($request->file('image'));
            } catch (\Throwable $e) {
                return back()->withInput()->with('error', 'Upload gambar gagal: ' . $e->getMessage());
            }
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
            try {
                $newImage = $this->uploadProductImage($request->file('image'));
            } catch (\Throwable $e) {
                return back()->withInput()->with('error', 'Upload gambar gagal: ' . $e->getMessage());
            }
            $this->deleteProductImage($product->image);
            $validated['image'] = $newImage;
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
        $this->deleteProductImage($product->image);

        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Produk berhasil dihapus.');
    }

    /**
     * Upload gambar produk ke disk sesuai FILESYSTEM_DISK di .env
     * (mis. 's3' untuk Supabase Storage / Cloudflare R2 / dll)
     * dan kembalikan URL publik lengkapnya untuk disimpan di kolom `image`.
     */
    private function uploadProductImage($file): string
    {
        $disk = config('filesystems.default', 'public');

        $path = $file->store('products', $disk);

        if ($disk === 's3') {
            return Storage::disk('s3')->url($path);
        }

        // Fallback: masih pakai disk lokal (development di komputer sendiri).
        return $path;
    }

    /**
     * Hapus gambar produk lama, baik yang tersimpan sebagai URL penuh (s3)
     * maupun sebagai path relatif (disk lokal 'public', untuk data lama).
     */
    private function deleteProductImage(?string $image): void
    {
        if (!$image) {
            return;
        }

        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            $s3BaseUrl = rtrim((string) config('filesystems.disks.s3.url'), '/');

            if ($s3BaseUrl && str_starts_with($image, $s3BaseUrl)) {
                $relativePath = ltrim(substr($image, strlen($s3BaseUrl)), '/');
                try {
                    Storage::disk('s3')->delete($relativePath);
                } catch (\Throwable $e) {}
            }

            return;
        }

        // Data lama yang masih pakai disk lokal 'public'.
        if (Storage::disk('public')->exists($image)) {
            Storage::disk('public')->delete($image);
            @unlink(public_path('storage/' . $image));
        }
    }
}