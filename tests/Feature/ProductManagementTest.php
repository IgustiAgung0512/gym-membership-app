<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['filesystems.default' => 'public']);
    }

    public function test_admin_product_page_contains_image_upload_fields(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.products.index'));
        $response->assertStatus(200);
        $response->assertSee('Foto / Gambar Produk');
        $response->assertSee('name="image"', false);
        $response->assertSee('enctype="multipart/form-data"', false);
    }

    public function test_admin_can_store_product_with_image(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);
        $file = UploadedFile::fake()->create('custom_whey.jpg', 500, 'image/jpeg');

        $response = $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'Custom Whey Protein',
            'sku' => 'CUSTOM-WHEY-01',
            'category' => 'supplements',
            'cost_price' => 150000,
            'price' => 250000,
            'stock' => 20,
            'unit' => 'tub',
            'min_stock_alert' => 5,
            'is_active' => '1',
            'image' => $file,
        ]);

        $response->assertRedirect(route('admin.products.index'));

        $product = Product::where('sku', 'CUSTOM-WHEY-01')->first();
        $this->assertNotNull($product);
        $this->assertNotNull($product->image);
        Storage::disk('public')->assertExists($product->image);
    }

    public function test_admin_can_update_product_image(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::create([
            'name' => 'Energy Drink',
            'sku' => 'DRK-ENERGY-01',
            'category' => 'drinks',
            'cost_price' => 8000,
            'price' => 15000,
            'stock' => 50,
            'unit' => 'kaleng',
            'min_stock_alert' => 10,
            'is_active' => true,
        ]);

        $file = UploadedFile::fake()->create('energy_new.png', 400, 'image/png');

        $response = $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'name' => 'Energy Drink XL',
            'sku' => 'DRK-ENERGY-01',
            'category' => 'drinks',
            'cost_price' => 8000,
            'price' => 18000,
            'stock' => 50,
            'unit' => 'kaleng',
            'min_stock_alert' => 10,
            'is_active' => '1',
            'image' => $file,
        ]);

        $response->assertRedirect(route('admin.products.index'));

        $product->refresh();
        $this->assertEquals('Energy Drink XL', $product->name);
        $this->assertNotNull($product->image);
        Storage::disk('public')->assertExists($product->image);
    }
}
