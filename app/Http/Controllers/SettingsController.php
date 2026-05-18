<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class SettingsController extends Controller
{
    /**
     * Display settings management page.
     */
    public function index()
    {
        $settings = SystemSetting::orderBy('category')
                                ->orderBy('key')
                                ->get()
                                ->groupBy('category');

        $categories = SystemSetting::getCategories();

        return view('admin.settings.bootstrap_index', compact('settings', 'categories'));
    }

    /**
     * Update system settings.
     */
    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'nullable',
            'logo' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        try {
            // Handle logo upload if provided
            if ($request->hasFile('logo')) {
                // Delete old logo if exists
                $oldLogo = SystemSetting::get('ui.logo_path');
                if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                    Storage::disk('public')->delete($oldLogo);
                }

                // Store new logo
                $logoPath = $request->file('logo')->store('logos', 'public');

                // Update setting
                SystemSetting::set('ui.logo_path', $logoPath);
            }

            // Update other settings
            foreach ($validatedData['settings'] as $fullKey => $value) {
                [$category, $key] = explode('.', $fullKey, 2);

                $setting = SystemSetting::where('category', $category)
                                       ->where('key', $key)
                                       ->first();

                if ($setting) {
                    $setting->setCastedValue($value);
                    $setting->save();
                }
            }

            // Clear settings cache
            Cache::forget('system_settings');

            return back()->with('success', 'Settings updated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }

    /**
     * Upload logo.
     */
    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        try {
            if ($request->hasFile('logo')) {
                // Delete old logo if exists
                $oldLogo = SystemSetting::get('ui.logo_path');
                if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                    Storage::disk('public')->delete($oldLogo);
                }

                // Store new logo
                $logoPath = $request->file('logo')->store('logos', 'public');

                // Update setting
                SystemSetting::set('ui.logo_path', $logoPath);

                return back()->with('success', 'Logo uploaded successfully!');
            }

            return back()->with('error', 'No logo file provided.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to upload logo: ' . $e->getMessage());
        }
    }

    /**
     * Reset settings to defaults.
     */
    public function reset(Request $request)
    {
        $category = $request->input('category');

        try {
            if ($category) {
                // Reset specific category
                SystemSetting::where('category', $category)->delete();
            } else {
                // Reset all settings
                SystemSetting::truncate();
            }

            // Recreate defaults
            SystemSetting::createDefaults();

            // Clear cache
            Cache::forget('system_settings');

            $message = $category
                ? "Settings for {$category} category have been reset to defaults."
                : 'All settings have been reset to defaults.';

            return back()->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to reset settings: ' . $e->getMessage());
        }
    }

    /**
     * Export settings.
     */
    public function export()
    {
        try {
            $settings = SystemSetting::all()->mapWithKeys(function ($setting) {
                return ["{$setting->category}.{$setting->key}" => $setting->getCastedValue()];
            });

            $fileName = 'system_settings_' . now()->format('Y_m_d_H_i_s') . '.json';

            return response()->json($settings)
                           ->header('Content-Type', 'application/json')
                           ->header('Content-Disposition', "attachment; filename=\"{$fileName}\"");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to export settings: ' . $e->getMessage());
        }
    }

    /**
     * Import settings.
     */
    public function import(Request $request)
    {
        $request->validate([
            'settings_file' => 'required|file|mimes:json',
        ]);

        try {
            $fileContent = file_get_contents($request->file('settings_file')->getRealPath());
            $settings = json_decode($fileContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new ValidationException('Invalid JSON file.');
            }

            foreach ($settings as $fullKey => $value) {
                SystemSetting::set($fullKey, $value);
            }

            Cache::forget('system_settings');

            return back()->with('success', 'Settings imported successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to import settings: ' . $e->getMessage());
        }
    }

    /**
     * Get public settings for frontend.
     */
    public function publicSettings()
    {
        $settings = Cache::remember('public_settings', 3600, function () {
            return SystemSetting::getPublicSettings();
        });

        return response()->json($settings);
    }
}
