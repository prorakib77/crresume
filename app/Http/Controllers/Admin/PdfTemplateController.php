<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomizationSetting;
use App\Support\PdfTemplateCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class PdfTemplateController extends Controller
{
    public function index(): View
    {
        CustomizationSetting::syncDefaults();

        return view('admin.pdf-templates.index', [
            'templates' => PdfTemplateCatalog::ordered(),
        ]);
    }

    public function edit(string $template): View
    {
        CustomizationSetting::syncDefaults();

        $templateDefinition = PdfTemplateCatalog::find($template);

        abort_if(!$templateDefinition, 404);

        $settings = CustomizationSetting::getAllActive();

        return view('admin.pdf-templates.edit', [
            'templateDefinition' => $templateDefinition,
            'settings' => $settings,
        ]);
    }

    public function update(Request $request, string $template): RedirectResponse
    {
        $templateDefinition = PdfTemplateCatalog::find($template);

        abort_if(!$templateDefinition, 404);

        $validationRules = [];

        foreach ($templateDefinition['fields'] as $field) {
            $validationRules[$field['key']] = 'nullable|string|max:' . ($field['max'] ?? 5000);
        }

        $validated = $request->validate($validationRules);

        foreach ($templateDefinition['fields'] as $field) {
            CustomizationSetting::setValue(
                $field['key'],
                (string) ($validated[$field['key']] ?? ''),
                'text',
                'pdf',
                $field['label']
            );
        }

        CustomizationSetting::clearCache();
        Artisan::call('view:clear');

        return redirect()
            ->route('admin.pdf-templates.edit', ['template' => $template])
            ->with('success', 'PDF template updated successfully.');
    }

    public function reset(string $template): RedirectResponse
    {
        $templateDefinition = PdfTemplateCatalog::find($template);

        abort_if(!$templateDefinition, 404);

        foreach ($templateDefinition['fields'] as $field) {
            CustomizationSetting::setValue(
                $field['key'],
                (string) CustomizationSetting::defaultValue($field['key'], ''),
                'text',
                'pdf',
                $field['label']
            );
        }

        CustomizationSetting::clearCache();
        Artisan::call('view:clear');

        return redirect()
            ->route('admin.pdf-templates.edit', ['template' => $template])
            ->with('success', 'PDF template reset to default.');
    }
}
