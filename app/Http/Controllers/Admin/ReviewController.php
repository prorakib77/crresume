<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(): View
    {
        $reviews = Review::query()
            ->orderBy('sort_order')
            ->orderByDesc('is_active')
            ->orderBy('id')
            ->paginate(12);

        return view('admin.reviews.index', [
            'reviews' => $reviews,
        ]);
    }

    public function create(): View
    {
        return view('admin.reviews.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRequest($request);

        $review = new Review();
        $this->persistReview($review, $request, $validated);

        return redirect()
            ->route('admin.reviews.index')
            ->with('success', 'Review card created successfully.');
    }

    public function edit(Review $review): View
    {
        return view('admin.reviews.edit', [
            'review' => $review,
        ]);
    }

    public function update(Request $request, Review $review): RedirectResponse
    {
        $validated = $this->validateRequest($request, $review);
        $this->persistReview($review, $request, $validated);

        return redirect()
            ->route('admin.reviews.index')
            ->with('success', 'Review card updated successfully.');
    }

    public function destroy(Review $review): RedirectResponse
    {
        if (filled($review->before_image_path) && Storage::disk('public')->exists($review->before_image_path)) {
            Storage::disk('public')->delete($review->before_image_path);
        }

        if (filled($review->after_image_path) && Storage::disk('public')->exists($review->after_image_path)) {
            Storage::disk('public')->delete($review->after_image_path);
        }

        $review->delete();

        return redirect()
            ->route('admin.reviews.index')
            ->with('success', 'Review card deleted successfully.');
    }

    private function validateRequest(Request $request, ?Review $review = null): array
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'country_label' => ['nullable', 'string', 'max:16'],
            'headline' => ['required', 'string', 'max:190'],
            'review_text' => ['required', 'string', 'max:4000'],
            'product_name' => ['nullable', 'string', 'max:190'],
            'product_link' => ['nullable', 'url', 'max:2048'],
            'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'is_verified' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'remove_image' => ['nullable', 'boolean'],
        ]);

        $hasExistingImage = $review
            && (
                filled($review->before_image_path)
                || filled($review->before_image_url)
                || filled($review->after_image_path)
                || filled($review->after_image_url)
            );

        $willHaveImage = $request->hasFile('image_file')
            || filled($validated['image_url'] ?? null)
            || ($hasExistingImage && !$request->boolean('remove_image'));

        if (!$willHaveImage) {
            throw ValidationException::withMessages([
                'image_file' => 'Add a review image by upload or URL.',
            ]);
        }

        return $validated;
    }

    private function persistReview(Review $review, Request $request, array $validated): void
    {
        $review->fill([
            'customer_name' => trim((string) $validated['customer_name']),
            'country_label' => strtoupper(trim((string) ($validated['country_label'] ?? 'US'))) ?: 'US',
            'headline' => trim((string) $validated['headline']),
            'review_text' => trim((string) $validated['review_text']),
            'product_name' => trim((string) ($validated['product_name'] ?? '')) ?: null,
            'product_link' => trim((string) ($validated['product_link'] ?? '')) ?: null,
            'before_image_url' => trim((string) ($validated['image_url'] ?? '')) ?: null,
            'after_image_url' => null,
            'is_verified' => $request->boolean('is_verified'),
            'is_active' => $request->boolean('is_active'),
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ]);

        if ($request->boolean('remove_image')) {
            if (filled($review->before_image_path) && Storage::disk('public')->exists($review->before_image_path)) {
                Storage::disk('public')->delete($review->before_image_path);
            }
            if (filled($review->after_image_path) && Storage::disk('public')->exists($review->after_image_path)) {
                Storage::disk('public')->delete($review->after_image_path);
            }

            $review->before_image_path = null;
            $review->before_image_url = null;
            $review->after_image_path = null;
            $review->after_image_url = null;
        }

        if ($request->hasFile('image_file')) {
            if (filled($review->before_image_path) && Storage::disk('public')->exists($review->before_image_path)) {
                Storage::disk('public')->delete($review->before_image_path);
            }
            if (filled($review->after_image_path) && Storage::disk('public')->exists($review->after_image_path)) {
                Storage::disk('public')->delete($review->after_image_path);
            }

            $review->before_image_path = $request->file('image_file')->store('reviews/image', 'public');
            $review->before_image_url = null;
            $review->after_image_path = null;
            $review->after_image_url = null;
        }

        $review->save();
    }
}
