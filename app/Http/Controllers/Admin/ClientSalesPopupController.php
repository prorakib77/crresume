<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientSalesPopup;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ClientSalesPopupController extends Controller
{
    public function index(): View
    {
        $popups = ClientSalesPopup::query()
            ->with('client:id,name,email')
            ->orderBy('sort_order')
            ->orderByDesc('is_active')
            ->orderBy('id')
            ->paginate(12);

        return view('admin.client-sales-popups.index', [
            'popups' => $popups,
            'targetTypeOptions' => ClientSalesPopup::targetTypeOptions(),
        ]);
    }

    public function create(): View
    {
        return view('admin.client-sales-popups.create', [
            'clients' => $this->clientOptions(),
            'targetTypeOptions' => ClientSalesPopup::targetTypeOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRequest($request);

        $popup = new ClientSalesPopup();
        $this->persistPopup($popup, $request, $validated);

        return redirect()
            ->route('admin.client-sales-popups.index')
            ->with('success', 'Client dashboard popup created successfully.');
    }

    public function edit(ClientSalesPopup $clientSalesPopup): View
    {
        return view('admin.client-sales-popups.edit', [
            'popup' => $clientSalesPopup,
            'clients' => $this->clientOptions(),
            'targetTypeOptions' => ClientSalesPopup::targetTypeOptions(),
        ]);
    }

    public function update(Request $request, ClientSalesPopup $clientSalesPopup): RedirectResponse
    {
        $validated = $this->validateRequest($request);
        $this->persistPopup($clientSalesPopup, $request, $validated);

        return redirect()
            ->route('admin.client-sales-popups.index')
            ->with('success', 'Client dashboard popup updated successfully.');
    }

    public function destroy(ClientSalesPopup $clientSalesPopup): RedirectResponse
    {
        if (filled($clientSalesPopup->image_path) && Storage::disk('public')->exists($clientSalesPopup->image_path)) {
            Storage::disk('public')->delete($clientSalesPopup->image_path);
        }

        $clientSalesPopup->delete();

        return redirect()
            ->route('admin.client-sales-popups.index')
            ->with('success', 'Client dashboard popup deleted successfully.');
    }

    private function validateRequest(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'badge_text' => ['nullable', 'string', 'max:120'],
            'message' => ['nullable', 'string', 'max:3000'],
            'price_text' => ['nullable', 'string', 'max:80'],
            'cta_label' => ['nullable', 'string', 'max:50'],
            'cta_link' => ['nullable', 'string', 'max:2048'],
            'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'bg_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'text_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'accent_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'target_type' => ['required', Rule::in(array_keys(ClientSalesPopup::targetTypeOptions()))],
            'target_client_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(function ($query): void {
                    $query->where('role_id', User::ROLE_CLIENT);
                }),
            ],
            'show_delay' => ['nullable', 'integer', 'min:0', 'max:15'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'is_active' => ['nullable', 'boolean'],
            'remove_image' => ['nullable', 'boolean'],
        ]);

        if (
            ($validated['target_type'] ?? null) === ClientSalesPopup::TARGET_SPECIFIC
            && blank($validated['target_client_id'] ?? null)
        ) {
            throw ValidationException::withMessages([
                'target_client_id' => 'Please select a client for specific targeting.',
            ]);
        }

        return $validated;
    }

    private function persistPopup(ClientSalesPopup $popup, Request $request, array $validated): void
    {
        $targetType = (string) $validated['target_type'];

        $popup->fill([
            'title' => trim((string) $validated['title']),
            'badge_text' => trim((string) ($validated['badge_text'] ?? 'Exclusive Offer')) ?: 'Exclusive Offer',
            'message' => trim((string) ($validated['message'] ?? '')) ?: null,
            'price_text' => trim((string) ($validated['price_text'] ?? '')) ?: null,
            'cta_label' => trim((string) ($validated['cta_label'] ?? 'Book Now')) ?: 'Book Now',
            'cta_link' => trim((string) ($validated['cta_link'] ?? '#')) ?: '#',
            'image_url' => trim((string) ($validated['image_url'] ?? '')) ?: null,
            'bg_color' => $validated['bg_color'] ?? '#111111',
            'text_color' => $validated['text_color'] ?? '#FFFFFF',
            'accent_color' => $validated['accent_color'] ?? '#C8A45D',
            'target_type' => $targetType,
            'target_client_id' => $targetType === ClientSalesPopup::TARGET_SPECIFIC
                ? (int) ($validated['target_client_id'] ?? 0)
                : null,
            'show_delay' => (int) ($validated['show_delay'] ?? 1),
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
            'is_active' => $request->boolean('is_active'),
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ]);

        if ($request->boolean('remove_image')) {
            if (filled($popup->image_path) && Storage::disk('public')->exists($popup->image_path)) {
                Storage::disk('public')->delete($popup->image_path);
            }

            $popup->image_path = null;
            $popup->image_url = null;
        }

        if ($request->hasFile('image_file')) {
            if (filled($popup->image_path) && Storage::disk('public')->exists($popup->image_path)) {
                Storage::disk('public')->delete($popup->image_path);
            }

            $popup->image_path = $request->file('image_file')->store('client-sales-popups', 'public');
            $popup->image_url = null;
        }

        $popup->save();
    }

    private function clientOptions()
    {
        return User::query()
            ->where('role_id', User::ROLE_CLIENT)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }
}
