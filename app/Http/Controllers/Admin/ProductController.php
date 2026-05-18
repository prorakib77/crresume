<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $typeOptions = Product::typeOptions();
        $selectedType = $this->resolveType($request->string('type')->toString(), $typeOptions);

        $products = Product::query()
            ->forType($selectedType)
            ->orderBy('sort_order')
            ->orderByDesc('is_active')
            ->orderBy('id')
            ->paginate(12)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'typeOptions' => $typeOptions,
            'selectedType' => $selectedType,
        ]);
    }

    public function create(Request $request): View
    {
        $typeOptions = Product::typeOptions();
        $selectedType = $this->resolveType($request->string('type')->toString(), $typeOptions);

        return view('admin.products.create', [
            'typeOptions' => $typeOptions,
            'selectedType' => $selectedType,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $typeOptions = Product::typeOptions();
        $validated = $this->validateRequest($request, $typeOptions);

        $product = new Product();
        $this->persistProduct($product, $request, $validated);

        return redirect()
            ->route('admin.products.index', ['type' => $product->type])
            ->with('success', 'Product card created successfully.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.edit', [
            'product' => $product,
            'typeOptions' => Product::typeOptions(),
            'selectedType' => $product->type,
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $this->validateRequest($request, Product::typeOptions());
        $this->persistProduct($product, $request, $validated);

        return redirect()
            ->route('admin.products.index', ['type' => $product->type])
            ->with('success', 'Product card updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $type = $product->type;

        if (filled($product->image_path) && Storage::disk('public')->exists($product->image_path)) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();

        return redirect()
            ->route('admin.products.index', ['type' => $type])
            ->with('success', 'Product card deleted successfully.');
    }

    private function validateRequest(Request $request, array $typeOptions): array
    {
        $validated = $request->validate([
            'type' => ['required', 'string', Rule::in(array_keys($typeOptions))],
            'title' => ['required', 'string', 'max:190'],
            'badge_text' => ['nullable', 'string', 'max:120'],
            'regular_price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'sale_price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'cta_label' => ['nullable', 'string', 'max:40'],
            'cta_link' => ['nullable', 'string', 'max:2048'],
            'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'is_active' => ['nullable', 'boolean'],
            'remove_image' => ['nullable', 'boolean'],
        ]);

        if (
            filled($validated['regular_price'] ?? null)
            && (float) $validated['sale_price'] > (float) $validated['regular_price']
        ) {
            throw ValidationException::withMessages([
                'sale_price' => 'Sale price cannot be greater than regular price.',
            ]);
        }

        return $validated;
    }

    private function persistProduct(Product $product, Request $request, array $validated): void
    {
        $product->fill([
            'type' => $validated['type'],
            'title' => trim((string) $validated['title']),
            'badge_text' => trim((string) ($validated['badge_text'] ?? 'ONLY ONE SPOT LEFT')) ?: 'ONLY ONE SPOT LEFT',
            'regular_price' => $validated['regular_price'] ?? null,
            'sale_price' => $validated['sale_price'],
            'cta_label' => trim((string) ($validated['cta_label'] ?? 'Buy Now')) ?: 'Buy Now',
            'cta_link' => trim((string) ($validated['cta_link'] ?? '#')) ?: '#',
            'image_url' => $validated['image_url'] ?? null,
            'is_active' => $request->boolean('is_active'),
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ]);

        $removeImage = $request->boolean('remove_image');

        if ($removeImage && filled($product->image_path) && Storage::disk('public')->exists($product->image_path)) {
            Storage::disk('public')->delete($product->image_path);
            $product->image_path = null;
        }

        if ($request->hasFile('image_file')) {
            if (filled($product->image_path) && Storage::disk('public')->exists($product->image_path)) {
                Storage::disk('public')->delete($product->image_path);
            }

            $product->image_path = $request->file('image_file')->store('products', 'public');
            $product->image_url = null;
        }

        $product->save();
    }

    private function resolveType(?string $type, array $typeOptions): string
    {
        if ($type && isset($typeOptions[$type])) {
            return $type;
        }

        return Product::TYPE_FULL_SERVICE;
    }
}
