<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SaleCountdown;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SaleCountdownController extends Controller
{
    public function index(): View
    {
        $countdowns = SaleCountdown::query()
            ->orderBy('sort_order')
            ->orderByDesc('is_active')
            ->orderBy('end_at')
            ->orderBy('id')
            ->paginate(12);

        return view('admin.sale-countdowns.index', [
            'countdowns' => $countdowns,
        ]);
    }

    public function create(): View
    {
        return view('admin.sale-countdowns.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRequest($request);

        $countdown = new SaleCountdown();
        $this->persistCountdown($countdown, $request, $validated);

        return redirect()
            ->route('admin.sale-countdowns.index')
            ->with('success', 'Countdown sale created successfully.');
    }

    public function edit(SaleCountdown $saleCountdown): View
    {
        return view('admin.sale-countdowns.edit', [
            'saleCountdown' => $saleCountdown,
        ]);
    }

    public function update(Request $request, SaleCountdown $saleCountdown): RedirectResponse
    {
        $validated = $this->validateRequest($request);
        $this->persistCountdown($saleCountdown, $request, $validated);

        return redirect()
            ->route('admin.sale-countdowns.index')
            ->with('success', 'Countdown sale updated successfully.');
    }

    public function destroy(SaleCountdown $saleCountdown): RedirectResponse
    {
        if (filled($saleCountdown->image_path) && Storage::disk('public')->exists($saleCountdown->image_path)) {
            Storage::disk('public')->delete($saleCountdown->image_path);
        }

        $saleCountdown->delete();

        return redirect()
            ->route('admin.sale-countdowns.index')
            ->with('success', 'Countdown sale deleted successfully.');
    }

    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'badge_text' => ['nullable', 'string', 'max:120'],
            'subtitle' => ['nullable', 'string', 'max:2000'],
            'offer_text' => ['nullable', 'string', 'max:190'],
            'end_at' => ['required', 'date'],
            'cta_label' => ['nullable', 'string', 'max:50'],
            'cta_link' => ['nullable', 'string', 'max:2048'],
            'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'bg_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'text_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'accent_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'is_active' => ['nullable', 'boolean'],
            'remove_image' => ['nullable', 'boolean'],
        ]);
    }

    private function persistCountdown(SaleCountdown $countdown, Request $request, array $validated): void
    {
        $countdown->fill([
            'title' => trim((string) $validated['title']),
            'badge_text' => trim((string) ($validated['badge_text'] ?? 'Limited Time Deal')) ?: 'Limited Time Deal',
            'subtitle' => trim((string) ($validated['subtitle'] ?? '')) ?: null,
            'offer_text' => trim((string) ($validated['offer_text'] ?? '')) ?: null,
            'end_at' => $validated['end_at'],
            'cta_label' => trim((string) ($validated['cta_label'] ?? 'Book Now')) ?: 'Book Now',
            'cta_link' => trim((string) ($validated['cta_link'] ?? '#')) ?: '#',
            'image_url' => trim((string) ($validated['image_url'] ?? '')) ?: null,
            'bg_color' => $validated['bg_color'] ?? '#111111',
            'text_color' => $validated['text_color'] ?? '#FFFFFF',
            'accent_color' => $validated['accent_color'] ?? '#C8A45D',
            'is_active' => $request->boolean('is_active'),
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ]);

        if ($request->boolean('remove_image')) {
            if (filled($countdown->image_path) && Storage::disk('public')->exists($countdown->image_path)) {
                Storage::disk('public')->delete($countdown->image_path);
            }

            $countdown->image_path = null;
            $countdown->image_url = null;
        }

        if ($request->hasFile('image_file')) {
            if (filled($countdown->image_path) && Storage::disk('public')->exists($countdown->image_path)) {
                Storage::disk('public')->delete($countdown->image_path);
            }

            $countdown->image_path = $request->file('image_file')->store('sale-countdowns', 'public');
            $countdown->image_url = null;
        }

        $countdown->save();
    }
}
