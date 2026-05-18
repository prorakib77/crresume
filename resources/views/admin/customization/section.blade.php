<x-app-layout>
    <x-slot name="title">Customization - {{ $sections[$activeSection]['label'] ?? 'Settings' }}</x-slot>
    <x-slot name="pageTitle">Customization</x-slot>
    <x-slot name="pageSubtitle">{{ $sections[$activeSection]['description'] ?? 'Manage website customization settings.' }}</x-slot>

    @php
        $preserveBlankSettingKeys = [
            'footer_branding_prefix',
            'footer_branding_name',
        ];

        $settingValue = function (string $key) use ($settings, $preserveBlankSettingKeys) {
            $storedValue = $settings->get($key)?->setting_value;

            if ($storedValue === null) {
                if (in_array($key, $preserveBlankSettingKeys, true)) {
                    return '';
                }

                return \App\Models\CustomizationSetting::defaultValue($key, '');
            }

            if ($storedValue === '' && !in_array($key, $preserveBlankSettingKeys, true)) {
                return \App\Models\CustomizationSetting::defaultValue($key, '');
            }

            return $storedValue;
        };

        $colorGroups = [
            ['primary_color', 'Primary'],
            ['secondary_color', 'Secondary'],
            ['accent_color', 'Accent'],
            ['background_color', 'Background'],
            ['text_color', 'Text'],
            ['header_color', 'Header'],
            ['sidebar_color', 'Sidebar'],
            ['button_color', 'Button'],
            ['link_color', 'Link'],
            ['border_color', 'Border'],
            ['success_color', 'Success'],
            ['warning_color', 'Warning'],
            ['error_color', 'Error'],
            ['info_color', 'Info'],
            ['danger_color', 'Danger'],
            ['primary_dark', 'Primary Dark'],
            ['secondary_dark', 'Secondary Dark'],
            ['success_dark', 'Success Dark'],
            ['warning_dark', 'Warning Dark'],
            ['error_dark', 'Error Dark'],
            ['info_dark', 'Info Dark'],
            ['danger_dark', 'Danger Dark'],
        ];

        $pdfColorGroups = [
            ['pdf_accent_color', 'Accent'],
            ['pdf_heading_color', 'Heading'],
            ['pdf_body_text_color', 'Body Text'],
            ['pdf_muted_text_color', 'Muted Text'],
            ['pdf_border_color', 'Border'],
            ['pdf_panel_background', 'Panel Background'],
            ['pdf_table_header_background', 'Table Header Background'],
            ['pdf_table_header_text_color', 'Table Header Text'],
            ['pdf_table_row_alt_background', 'Alternate Row'],
            ['pdf_brand_mark_background', 'Brand Mark Background'],
            ['pdf_brand_mark_text_color', 'Brand Mark Text'],
        ];

        $logoSetting = $settings->get('site_logo');
        $faviconSetting = $settings->get('site_favicon');

        $logoUrl = $logoSetting?->setting_value
            ? storage_public_url($logoSetting->setting_value) . '?v=' . ($logoSetting->updated_at?->timestamp ?? time())
            : null;

        $faviconUrl = $faviconSetting?->setting_value
            ? storage_public_url($faviconSetting->setting_value) . '?v=' . ($faviconSetting->updated_at?->timestamp ?? time())
            : null;

        $popupImageSetting = $settings->get('welcome_popup_image');
        $popupUploadedUrl = $popupImageSetting?->setting_value
            ? storage_public_url($popupImageSetting->setting_value) . '?v=' . ($popupImageSetting->updated_at?->timestamp ?? time())
            : null;

        $bannerImageSetting = $settings->get('welcome_banner_image');
        $bannerUploadedUrl = $bannerImageSetting?->setting_value
            ? storage_public_url($bannerImageSetting->setting_value) . '?v=' . ($bannerImageSetting->updated_at?->timestamp ?? time())
            : null;

        $bannerMobileImageSetting = $settings->get('welcome_banner_mobile_image');
        $bannerMobileUploadedUrl = $bannerMobileImageSetting?->setting_value
            ? storage_public_url($bannerMobileImageSetting->setting_value) . '?v=' . ($bannerMobileImageSetting->updated_at?->timestamp ?? time())
            : null;

        $publicHeaderRegisterEnabledValue = old(
            'public_header_register_enabled',
            $settings->get('public_header_register_enabled')?->setting_value
                ?? $settings->get('welcome_primary_enabled')?->setting_value
                ?? \App\Models\CustomizationSetting::defaultValue('public_header_register_enabled', '1')
        );
        $publicHeaderRegisterLabelValue = old(
            'public_header_register_label',
            $settings->get('public_header_register_label')?->setting_value
                ?? $settings->get('welcome_primary_label')?->setting_value
                ?? \App\Models\CustomizationSetting::defaultValue('public_header_register_label', 'Secure Your Spot')
        );
        $publicHeaderRegisterLinkValue = old(
            'public_header_register_link',
            $settings->get('public_header_register_link')?->setting_value
                ?? $settings->get('welcome_primary_link')?->setting_value
                ?? \App\Models\CustomizationSetting::defaultValue('public_header_register_link', '/register')
        );

        $footerBrandingLogoSetting = $settings->get('footer_branding_logo');
        $footerBrandingLogoUploadedUrl = $footerBrandingLogoSetting?->setting_value
            ? storage_public_url($footerBrandingLogoSetting->setting_value) . '?v=' . ($footerBrandingLogoSetting->updated_at?->timestamp ?? time())
            : null;

        $emailHeaderBgSetting = $settings->get('email_header_bg_image');
        $emailHeaderBgUploadedUrl = $emailHeaderBgSetting?->setting_value
            ? storage_public_url($emailHeaderBgSetting->setting_value) . '?v=' . ($emailHeaderBgSetting->updated_at?->timestamp ?? time())
            : null;

        $pdfLogoSetting = $settings->get('pdf_logo');
        $pdfLogoUploadedUrl = $pdfLogoSetting?->setting_value
            ? storage_public_url($pdfLogoSetting->setting_value) . '?v=' . ($pdfLogoSetting->updated_at?->timestamp ?? time())
            : null;

        $parseFooterRows = function (string $raw, bool $withIcon = false): array {
            $rows = collect(preg_split('/\r\n|\r|\n/', $raw))
                ->map(fn ($line) => trim((string) $line))
                ->filter()
                ->map(function (string $line) use ($withIcon) {
                    $parts = array_map('trim', explode('|', $line));

                    if (!$withIcon) {
                        return [
                            'label' => $parts[0] ?? '',
                            'url' => $parts[1] ?? '',
                        ];
                    }

                    return [
                        'label' => $parts[0] ?? '',
                        'url' => $parts[1] ?? '',
                        'icon' => $parts[2] ?? 'fas fa-link',
                    ];
                })
                ->values()
                ->all();

            if (empty($rows)) {
                return [$withIcon
                    ? ['label' => '', 'url' => '', 'icon' => 'fas fa-link']
                    : ['label' => '', 'url' => '']
                ];
            }

            return $rows;
        };

        $policyRows = $parseFooterRows((string) old('footer_policy_links', $settingValue('footer_policy_links')), false);
        $socialRows = $parseFooterRows((string) old('footer_social_links', $settingValue('footer_social_links')), true);
        $announcementRows = collect(preg_split('/\r\n|\r|\n/', (string) old('welcome_announcement_text', $settingValue('welcome_announcement_text'))))
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->values()
            ->all();

        if (empty($announcementRows)) {
            $announcementRows = [''];
        }

        $socialIconOptions = [
            'fab fa-facebook-f' => 'Facebook',
            'fab fa-instagram' => 'Instagram',
            'fab fa-linkedin-in' => 'LinkedIn',
            'fab fa-x-twitter' => 'X (Twitter)',
            'fab fa-youtube' => 'YouTube',
            'fab fa-tiktok' => 'TikTok',
            'fab fa-whatsapp' => 'WhatsApp',
            'fas fa-globe' => 'Website',
            'fas fa-link' => 'Generic Link',
        ];
    @endphp

    <div class="space-y-6">
        @if($errors->any())
            <div class="alert alert-danger">
                <div class="font-semibold">Please fix the validation errors below.</div>
                <ul class="mb-0 mt-2 list-disc ps-4">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div>

            <form id="customization-section-form" action="{{ route('admin.customization.section.update', ['section' => $activeSection]) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')
                <input type="hidden" name="section" value="{{ $activeSection }}">

                <section class="rounded-[2rem] border border-[#e7dcc5] bg-white/95 p-6 shadow-[0_24px_60px_rgba(17,17,17,0.06)] sm:p-8">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-[#9b7431]">Customization</p>
                    <h2 class="theme-display mt-2 text-3xl text-stone-950">{{ $sections[$activeSection]['label'] ?? 'Section' }}</h2>
                    <p class="mt-2 text-sm text-stone-600">{{ $sections[$activeSection]['description'] ?? '' }}</p>

                    @if($activeSection === 'identity')
                        <div class="mt-6 grid gap-4 md:grid-cols-2">
                            <div>
                                <label for="site_name" class="form-label">Site Name</label>
                                <input type="text" class="form-control" id="site_name" name="site_name" value="{{ old('site_name', $settingValue('site_name')) }}">
                            </div>
                            <div>
                                <label for="site_tagline" class="form-label">Brand Tagline</label>
                                <input type="text" class="form-control" id="site_tagline" name="site_tagline" value="{{ old('site_tagline', $settingValue('site_tagline')) }}">
                            </div>
                            <div class="md:col-span-2">
                                <label for="auth_panel_caption" class="form-label">Auth Caption</label>
                                <input type="text" class="form-control" id="auth_panel_caption" name="auth_panel_caption" value="{{ old('auth_panel_caption', $settingValue('auth_panel_caption')) }}">
                            </div>
                        </div>

                        <div class="mt-6 grid gap-4 md:grid-cols-2">
                            <div class="rounded-[1.4rem] border border-[#ece3d2] bg-[#fffdfa] p-4">
                                <label for="site_logo" class="form-label">Site Logo</label>
                                <input type="file" class="form-control mt-2" id="site_logo" name="site_logo" accept="image/*">
                                <div class="mt-4 flex h-24 items-center justify-center rounded-2xl border border-dashed border-[#d8c6a1] bg-white p-3">
                                    <img id="logo-preview-image" src="{{ $logoUrl }}" alt="Logo preview" class="{{ $logoUrl ? '' : 'hidden ' }}max-h-full w-auto object-contain">
                                    <span id="logo-preview-placeholder" class="{{ $logoUrl ? 'hidden ' : '' }}text-[11px] font-semibold uppercase tracking-[0.24em] text-stone-400">No Logo</span>
                                </div>
                            </div>
                            <div class="rounded-[1.4rem] border border-[#ece3d2] bg-[#fffdfa] p-4">
                                <label for="site_favicon" class="form-label">Favicon</label>
                                <input type="file" class="form-control mt-2" id="site_favicon" name="site_favicon" accept="image/*">
                                <div class="mt-4 flex h-24 items-center justify-center rounded-2xl border border-dashed border-[#d8c6a1] bg-white p-3">
                                    <img id="favicon-preview-image" src="{{ $faviconUrl }}" alt="Favicon preview" class="{{ $faviconUrl ? '' : 'hidden ' }}max-h-full w-auto object-contain">
                                    <span id="favicon-preview-placeholder" class="{{ $faviconUrl ? 'hidden ' : '' }}text-[11px] font-semibold uppercase tracking-[0.24em] text-stone-400">Icon</span>
                                </div>
                            </div>
                        </div>
                    @elseif($activeSection === 'footer')
                        <div class="mt-6 grid gap-4">
                            <div>
                                <label for="footer_text" class="form-label">Copyright Text</label>
                                <textarea class="form-control" id="footer_text" name="footer_text" rows="2">{{ old('footer_text', $settingValue('footer_text')) }}</textarea>
                            </div>

                            <div class="rounded-[1.2rem] border border-[#ece3d2] bg-[#fffdfa] p-4">
                                <div class="mb-3">
                                    <h3 class="mb-1 text-base font-semibold text-stone-900">Powered By Branding</h3>
                                    <p class="mb-0 text-sm text-stone-600">Controls the right side of the footer copyright row.</p>
                                </div>
                                <div class="grid gap-4 md:grid-cols-2">
                                    <div>
                                        <label for="footer_branding_enabled" class="form-label">Branding Visibility</label>
                                        <select class="form-select" id="footer_branding_enabled" name="footer_branding_enabled">
                                            <option value="1" @selected(old('footer_branding_enabled', $settingValue('footer_branding_enabled')) === '1')>Show branding</option>
                                            <option value="0" @selected(old('footer_branding_enabled', $settingValue('footer_branding_enabled')) === '0')>Hide branding</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="footer_branding_show_logo" class="form-label">Logo Visibility</label>
                                        <select class="form-select" id="footer_branding_show_logo" name="footer_branding_show_logo">
                                            <option value="1" @selected(old('footer_branding_show_logo', $settingValue('footer_branding_show_logo')) === '1')>Show logo</option>
                                            <option value="0" @selected(old('footer_branding_show_logo', $settingValue('footer_branding_show_logo')) === '0')>Hide logo</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="footer_branding_prefix" class="form-label">Prefix Text</label>
                                        <input type="text" class="form-control" id="footer_branding_prefix" name="footer_branding_prefix" value="{{ old('footer_branding_prefix', $settingValue('footer_branding_prefix')) }}" placeholder="Powered by">
                                    </div>
                                    <div>
                                        <label for="footer_branding_name" class="form-label">Brand Name</label>
                                        <input type="text" class="form-control" id="footer_branding_name" name="footer_branding_name" value="{{ old('footer_branding_name', $settingValue('footer_branding_name')) }}" placeholder="Shopispeed">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label for="footer_branding_link" class="form-label">Brand Link</label>
                                        <input type="text" class="form-control" id="footer_branding_link" name="footer_branding_link" value="{{ old('footer_branding_link', $settingValue('footer_branding_link')) }}" placeholder="https://example.com">
                                    </div>
                                    <div>
                                        <label for="footer_branding_logo_url" class="form-label">Logo URL</label>
                                        <input type="text" class="form-control" id="footer_branding_logo_url" name="footer_branding_logo_url" value="{{ old('footer_branding_logo_url', $settingValue('footer_branding_logo_url')) }}" placeholder="https://example.com/logo.png">
                                        <p class="mb-0 mt-2 text-xs text-stone-500">Used when no uploaded footer logo is present.</p>
                                    </div>
                                    <div class="rounded-[1.25rem] border border-[#e7dcc5] bg-white p-4">
                                        <label for="footer_branding_logo" class="form-label">Upload Logo</label>
                                        <input type="file" class="form-control mt-2" id="footer_branding_logo" name="footer_branding_logo" accept="image/*">
                                        <div class="mt-4 flex h-24 items-center justify-center rounded-2xl border border-dashed border-[#d8c6a1] bg-[#fffdfa] p-3">
                                            <img id="footer-branding-logo-preview-image" src="{{ $footerBrandingLogoUploadedUrl ?: old('footer_branding_logo_url', $settingValue('footer_branding_logo_url')) }}" alt="Footer branding logo preview" class="{{ ($footerBrandingLogoUploadedUrl || old('footer_branding_logo_url', $settingValue('footer_branding_logo_url'))) ? '' : 'hidden ' }}max-h-full w-auto object-contain">
                                            <span id="footer-branding-logo-preview-placeholder" class="{{ ($footerBrandingLogoUploadedUrl || old('footer_branding_logo_url', $settingValue('footer_branding_logo_url'))) ? 'hidden ' : '' }}text-[11px] font-semibold uppercase tracking-[0.24em] text-stone-400">No Footer Logo</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-[1.2rem] border border-[#ece3d2] bg-[#fffdfa] p-4">
                                <div class="mb-3 flex items-center justify-between gap-3">
                                    <label class="form-label mb-0">Policy Links (Row 1)</label>
                                    <button type="button" class="btn btn-border-black btn-sm" id="add-policy-link">
                                        <i class="fas fa-plus me-1"></i>Add More
                                    </button>
                                </div>
                                <input type="hidden" id="footer_policy_links" name="footer_policy_links" value="{{ old('footer_policy_links', $settingValue('footer_policy_links')) }}">
                                <div id="policy-links-builder" class="space-y-3">
                                    @foreach($policyRows as $index => $row)
                                        <div class="grid gap-2 rounded-xl border border-[#e7dcc5] bg-white p-3 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto] footer-policy-item">
                                            <input type="text" class="form-control footer-policy-label" value="{{ $row['label'] }}" placeholder="Label (e.g., Privacy Policy)">
                                            <input type="text" class="form-control footer-policy-url" value="{{ $row['url'] }}" placeholder="URL (e.g., /privacy-policy)">
                                            <button type="button" class="btn btn-outline-danger btn-sm footer-remove-row" @if($index === 0 && count($policyRows) === 1) disabled @endif>
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="rounded-[1.2rem] border border-[#ece3d2] bg-[#fffdfa] p-4">
                                <div class="mb-3 flex items-center justify-between gap-3">
                                    <label class="form-label mb-0">Social Links (Row 2)</label>
                                    <button type="button" class="btn btn-border-black btn-sm" id="add-social-link">
                                        <i class="fas fa-plus me-1"></i>Add More
                                    </button>
                                </div>
                                <input type="hidden" id="footer_social_links" name="footer_social_links" value="{{ old('footer_social_links', $settingValue('footer_social_links')) }}">
                                <div id="social-links-builder" class="space-y-3">
                                    @foreach($socialRows as $index => $row)
                                        <div class="grid gap-2 rounded-xl border border-[#e7dcc5] bg-white p-3 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(0,1fr)_auto] footer-social-item">
                                            <input type="text" class="form-control footer-social-label" value="{{ $row['label'] }}" placeholder="Label (internal)">
                                            <input type="text" class="form-control footer-social-url" value="{{ $row['url'] }}" placeholder="URL">
                                            <select class="form-select footer-social-icon">
                                                @foreach($socialIconOptions as $iconClass => $iconLabel)
                                                    <option value="{{ $iconClass }}" @selected(($row['icon'] ?? 'fas fa-link') === $iconClass)>{{ $iconLabel }} ({{ $iconClass }})</option>
                                                @endforeach
                                            </select>
                                            <button type="button" class="btn btn-outline-danger btn-sm footer-remove-row" @if($index === 0 && count($socialRows) === 1) disabled @endif>
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @elseif($activeSection === 'theme')
                        <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                            @foreach($colorGroups as [$key, $label])
                                <div class="rounded-[1.3rem] border border-[#ece3d2] bg-[#fffdfa] p-4">
                                    <label for="{{ $key }}" class="form-label">{{ $label }}</label>
                                    <div class="mt-2 flex items-center gap-3">
                                        <input type="color" class="h-12 w-16 cursor-pointer rounded-2xl border border-[#d8c6a1] bg-white p-1" id="{{ $key }}" name="{{ $key }}" value="{{ old($key, $settingValue($key)) }}" data-color-control="{{ $key }}">
                                        <input type="text" class="form-control" value="{{ old($key, $settingValue($key)) }}" data-color-text="{{ $key }}">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @elseif($activeSection === 'layout')
                        <div class="mt-6 grid gap-4 md:grid-cols-2">
                            <div>
                                <label for="font_family" class="form-label">Body Font</label>
                                <select class="form-select" id="font_family" name="font_family">
                                    @foreach($bodyFontOptions as $font)
                                        <option value="{{ $font }}" @selected(old('font_family', $settingValue('font_family')) === $font)>{{ $font }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="display_font" class="form-label">Heading Font</label>
                                <select class="form-select" id="display_font" name="display_font">
                                    @foreach($displayFontOptions as $font)
                                        <option value="{{ $font }}" @selected(old('display_font', $settingValue('display_font')) === $font)>{{ $font }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="font_size" class="form-label">Font Size</label>
                                <select class="form-select" id="font_size" name="font_size">
                                    @foreach(['14px' => '14px', '15px' => '15px', '16px' => '16px', '18px' => '18px'] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('font_size', $settingValue('font_size')) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="border_radius" class="form-label">Border Radius</label>
                                <select class="form-select" id="border_radius" name="border_radius">
                                    @foreach(['0px' => '0px', '12px' => '12px', '20px' => '20px', '28px' => '28px'] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('border_radius', $settingValue('border_radius')) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="animation_speed" class="form-label">Animation Speed</label>
                                <select class="form-select" id="animation_speed" name="animation_speed">
                                    @foreach(['0.15s' => '0.15s', '0.3s' => '0.3s', '0.45s' => '0.45s'] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('animation_speed', $settingValue('animation_speed')) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="box_shadow" class="form-label">Box Shadow</label>
                                <input type="text" class="form-control" id="box_shadow" name="box_shadow" value="{{ old('box_shadow', $settingValue('box_shadow')) }}">
                            </div>
                            <div>
                                <label for="sidebar_width" class="form-label">Sidebar Width</label>
                                <select class="form-select" id="sidebar_width" name="sidebar_width">
                                    @foreach(['16rem' => '16rem', '18rem' => '18rem', '20rem' => '20rem'] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('sidebar_width', $settingValue('sidebar_width')) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="header_height" class="form-label">Header Height</label>
                                <select class="form-select" id="header_height" name="header_height">
                                    @foreach(['68px' => '68px', '76px' => '76px', '84px' => '84px'] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('header_height', $settingValue('header_height')) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @elseif($activeSection === 'welcome')
                        <div class="mt-6 rounded-[1.3rem] border border-[#ece3d2] bg-[#fffdfa] p-4">
                            <div class="mb-4">
                                <h3 class="mb-1 text-base font-semibold text-stone-900">Top Hero Banner</h3>
                                <p class="mb-0 text-sm text-stone-600">Shown under the public header and above the countdown. Use image only, or image with centered text.</p>
                            </div>
                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label for="welcome_banner_enabled" class="form-label">Banner Status</label>
                                    <select class="form-select" id="welcome_banner_enabled" name="welcome_banner_enabled">
                                        <option value="1" @selected(old('welcome_banner_enabled', $settingValue('welcome_banner_enabled')) === '1')>Enabled</option>
                                        <option value="0" @selected(old('welcome_banner_enabled', $settingValue('welcome_banner_enabled')) === '0')>Disabled</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="welcome_banner_mode" class="form-label">Banner Mode</label>
                                    <select class="form-select" id="welcome_banner_mode" name="welcome_banner_mode">
                                        <option value="image_text" @selected(old('welcome_banner_mode', $settingValue('welcome_banner_mode')) === 'image_text')>Image With Text</option>
                                        <option value="image_only" @selected(old('welcome_banner_mode', $settingValue('welcome_banner_mode')) === 'image_only')>Image Only</option>
                                    </select>
                                </div>
                                <div class="rounded-[1.2rem] border border-[#ece3d2] bg-white p-3">
                                    <label for="welcome_banner_image" class="form-label">Desktop Banner Image</label>
                                    <input type="file" class="form-control mt-2" id="welcome_banner_image" name="welcome_banner_image" accept="image/*">
                                    <p class="mt-2 text-xs text-stone-500">Used first on desktop and tablet. If no upload exists, the image URL below is used.</p>
                                    <div class="mt-3 flex h-32 items-center justify-center rounded-2xl border border-dashed border-[#d8c6a1] bg-[#fffdfa] p-3">
                                        <img id="banner-preview-image" src="{{ $bannerUploadedUrl ?: old('welcome_banner_image_url', $settingValue('welcome_banner_image_url')) }}" alt="Banner image preview" class="{{ ($bannerUploadedUrl || old('welcome_banner_image_url', $settingValue('welcome_banner_image_url'))) ? '' : 'hidden ' }}max-h-full w-auto object-contain">
                                        <span id="banner-preview-placeholder" class="{{ ($bannerUploadedUrl || old('welcome_banner_image_url', $settingValue('welcome_banner_image_url'))) ? 'hidden ' : '' }}text-[11px] font-semibold uppercase tracking-[0.24em] text-stone-400">No Desktop Banner</span>
                                    </div>
                                </div>

                                <div class="rounded-[1.2rem] border border-[#ece3d2] bg-white p-3">
                                    <label for="welcome_banner_mobile_image" class="form-label">Mobile Banner Image</label>
                                    <input type="file" class="form-control mt-2" id="welcome_banner_mobile_image" name="welcome_banner_mobile_image" accept="image/*">
                                    <p class="mt-2 text-xs text-stone-500">Used first on screens below 768px. If empty, the desktop banner is reused automatically.</p>
                                    <div class="mt-3 flex h-32 items-center justify-center rounded-2xl border border-dashed border-[#d8c6a1] bg-[#fffdfa] p-3">
                                        <img id="banner-mobile-preview-image" src="{{ $bannerMobileUploadedUrl ?: old('welcome_banner_mobile_image_url', $settingValue('welcome_banner_mobile_image_url')) }}" alt="Mobile banner image preview" class="{{ ($bannerMobileUploadedUrl || old('welcome_banner_mobile_image_url', $settingValue('welcome_banner_mobile_image_url'))) ? '' : 'hidden ' }}max-h-full w-auto object-contain">
                                        <span id="banner-mobile-preview-placeholder" class="{{ ($bannerMobileUploadedUrl || old('welcome_banner_mobile_image_url', $settingValue('welcome_banner_mobile_image_url'))) ? 'hidden ' : '' }}text-[11px] font-semibold uppercase tracking-[0.24em] text-stone-400">No Mobile Banner</span>
                                    </div>
                                </div>
                                <div>
                                    <label for="welcome_banner_image_url" class="form-label">Desktop Banner Image URL (Optional)</label>
                                    <input type="text" class="form-control" id="welcome_banner_image_url" name="welcome_banner_image_url" value="{{ old('welcome_banner_image_url', $settingValue('welcome_banner_image_url')) }}">
                                </div>
                                <div>
                                    <label for="welcome_banner_mobile_image_url" class="form-label">Mobile Banner Image URL (Optional)</label>
                                    <input type="text" class="form-control" id="welcome_banner_mobile_image_url" name="welcome_banner_mobile_image_url" value="{{ old('welcome_banner_mobile_image_url', $settingValue('welcome_banner_mobile_image_url')) }}">
                                </div>
                                <div class="md:col-span-2">
                                    <p class="mb-0 text-xs text-stone-500">When Banner Mode is set to Image With Text, the Hero Badge, Hero Title, Hero Subtitle, and the enabled hero buttons below will be used in the center overlay.</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 grid gap-4 md:grid-cols-2">
                            <div>
                                <label for="welcome_badge" class="form-label">Hero Badge</label>
                                <input type="text" class="form-control" id="welcome_badge" name="welcome_badge" value="{{ old('welcome_badge', $settingValue('welcome_badge')) }}">
                            </div>
                            <div>
                                <label for="welcome_side_title" class="form-label">Side Panel Title</label>
                                <input type="text" class="form-control" id="welcome_side_title" name="welcome_side_title" value="{{ old('welcome_side_title', $settingValue('welcome_side_title')) }}">
                            </div>
                            <div class="md:col-span-2">
                                <label for="welcome_title" class="form-label">Hero Title</label>
                                <input type="text" class="form-control" id="welcome_title" name="welcome_title" value="{{ old('welcome_title', $settingValue('welcome_title')) }}">
                            </div>
                            <div class="md:col-span-2">
                                <label for="welcome_subtitle" class="form-label">Hero Subtitle</label>
                                <textarea class="form-control" id="welcome_subtitle" name="welcome_subtitle" rows="3">{{ old('welcome_subtitle', $settingValue('welcome_subtitle')) }}</textarea>
                            </div>
                            <div class="md:col-span-2">
                                <label for="welcome_points" class="form-label">Bullet Points</label>
                                <textarea class="form-control" id="welcome_points" name="welcome_points" rows="6">{{ old('welcome_points', $settingValue('welcome_points')) }}</textarea>
                            </div>
                            <div>
                                <label for="welcome_primary_enabled" class="form-label">Primary Button Status</label>
                                <select class="form-select" id="welcome_primary_enabled" name="welcome_primary_enabled">
                                    <option value="1" @selected(old('welcome_primary_enabled', $settingValue('welcome_primary_enabled')) === '1')>Show</option>
                                    <option value="0" @selected(old('welcome_primary_enabled', $settingValue('welcome_primary_enabled')) === '0')>Hide</option>
                                </select>
                            </div>
                            <div>
                                <label for="welcome_primary_label" class="form-label">Primary Button Label</label>
                                <input type="text" class="form-control" id="welcome_primary_label" name="welcome_primary_label" value="{{ old('welcome_primary_label', $settingValue('welcome_primary_label')) }}">
                            </div>
                            <div>
                                <label for="welcome_primary_link" class="form-label">Primary Button Link</label>
                                <input type="text" class="form-control" id="welcome_primary_link" name="welcome_primary_link" value="{{ old('welcome_primary_link', $settingValue('welcome_primary_link')) }}">
                            </div>
                            <div>
                                <label for="welcome_secondary_enabled" class="form-label">Secondary Button Status</label>
                                <select class="form-select" id="welcome_secondary_enabled" name="welcome_secondary_enabled">
                                    <option value="1" @selected(old('welcome_secondary_enabled', $settingValue('welcome_secondary_enabled')) === '1')>Show</option>
                                    <option value="0" @selected(old('welcome_secondary_enabled', $settingValue('welcome_secondary_enabled')) === '0')>Hide</option>
                                </select>
                            </div>
                            <div>
                                <label for="welcome_secondary_label" class="form-label">Secondary Button Label</label>
                                <input type="text" class="form-control" id="welcome_secondary_label" name="welcome_secondary_label" value="{{ old('welcome_secondary_label', $settingValue('welcome_secondary_label')) }}">
                            </div>
                            <div>
                                <label for="welcome_secondary_link" class="form-label">Secondary Button Link</label>
                                <input type="text" class="form-control" id="welcome_secondary_link" name="welcome_secondary_link" value="{{ old('welcome_secondary_link', $settingValue('welcome_secondary_link')) }}">
                            </div>
                            <div>
                                <label for="welcome_services_button_label" class="form-label">Services Button Label</label>
                                <input type="text" class="form-control" id="welcome_services_button_label" name="welcome_services_button_label" value="{{ old('welcome_services_button_label', $settingValue('welcome_services_button_label')) }}">
                            </div>
                            <div>
                                <label for="welcome_services_button_link" class="form-label">Services Button Link</label>
                                <input type="text" class="form-control" id="welcome_services_button_link" name="welcome_services_button_link" value="{{ old('welcome_services_button_link', $settingValue('welcome_services_button_link')) }}">
                            </div>
                        </div>

                        <div class="mt-6 rounded-[1.3rem] border border-[#ece3d2] bg-[#fffdfa] p-4 sm:p-5">
                            <div class="text-xs font-semibold uppercase tracking-[0.22em] text-stone-500">Public Header Register Button</div>
                            <p class="mt-2 text-sm text-stone-600">Use this to control the register CTA in the public site header separately from the welcome hero buttons.</p>

                            <div class="mt-4 grid gap-4 md:grid-cols-3">
                                <div>
                                    <label for="public_header_register_enabled" class="form-label">Button Status</label>
                                    <select class="form-select" id="public_header_register_enabled" name="public_header_register_enabled">
                                        <option value="1" @selected($publicHeaderRegisterEnabledValue === '1')>Show</option>
                                        <option value="0" @selected($publicHeaderRegisterEnabledValue === '0')>Hide</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="public_header_register_label" class="form-label">Button Label</label>
                                    <input type="text" class="form-control" id="public_header_register_label" name="public_header_register_label" value="{{ $publicHeaderRegisterLabelValue }}">
                                </div>
                                <div>
                                    <label for="public_header_register_link" class="form-label">Button Link</label>
                                    <input type="text" class="form-control" id="public_header_register_link" name="public_header_register_link" value="{{ $publicHeaderRegisterLinkValue }}">
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 grid gap-4 md:grid-cols-3">
                            @foreach([
                                ['welcome_timeline_label', 'welcome_timeline', 'Panel Card 1'],
                                ['welcome_availability_label', 'welcome_availability', 'Panel Card 2'],
                                ['welcome_quality_label', 'welcome_quality_text', 'Panel Card 3'],
                            ] as [$labelKey, $copyKey, $title])
                                <div class="rounded-[1.3rem] border border-[#ece3d2] bg-[#fffdfa] p-4">
                                    <div class="text-xs font-semibold uppercase tracking-[0.22em] text-stone-500">{{ $title }}</div>
                                    <label for="{{ $labelKey }}" class="form-label mt-3">Label</label>
                                    <input type="text" class="form-control" id="{{ $labelKey }}" name="{{ $labelKey }}" value="{{ old($labelKey, $settingValue($labelKey)) }}">
                                    <label for="{{ $copyKey }}" class="form-label mt-3">Copy</label>
                                    <textarea class="form-control" id="{{ $copyKey }}" name="{{ $copyKey }}" rows="4">{{ old($copyKey, $settingValue($copyKey)) }}</textarea>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6 grid gap-4 md:grid-cols-3">
                            @foreach([
                                ['welcome_stat_one_value', 'welcome_stat_one_label', 'Metric 1'],
                                ['welcome_stat_two_value', 'welcome_stat_two_label', 'Metric 2'],
                                ['welcome_stat_three_value', 'welcome_stat_three_label', 'Metric 3'],
                            ] as [$valueKey, $labelKey, $title])
                                <div class="rounded-[1.3rem] border border-[#ece3d2] bg-[#fffdfa] p-4">
                                    <div class="text-xs font-semibold uppercase tracking-[0.22em] text-stone-500">{{ $title }}</div>
                                    <label for="{{ $valueKey }}" class="form-label mt-3">Value</label>
                                    <input type="text" class="form-control" id="{{ $valueKey }}" name="{{ $valueKey }}" value="{{ old($valueKey, $settingValue($valueKey)) }}">
                                    <label for="{{ $labelKey }}" class="form-label mt-3">Label</label>
                                    <input type="text" class="form-control" id="{{ $labelKey }}" name="{{ $labelKey }}" value="{{ old($labelKey, $settingValue($labelKey)) }}">
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6 rounded-[1.3rem] border border-[#ece3d2] bg-[#fffdfa] p-4">
                            <div class="mb-4">
                                <h3 class="mb-1 text-base font-semibold text-stone-900">Services And Reviews Text Section</h3>
                                <p class="mb-0 text-sm text-stone-600">Shown between the services section and the client reviews. Rich text supports HTML formatting such as headings, bold text, line breaks, and links.</p>
                            </div>
                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label for="welcome_text_section_enabled" class="form-label">Section Status</label>
                                    <select class="form-select" id="welcome_text_section_enabled" name="welcome_text_section_enabled">
                                        <option value="1" @selected(old('welcome_text_section_enabled', $settingValue('welcome_text_section_enabled')) === '1')>Enabled</option>
                                        <option value="0" @selected(old('welcome_text_section_enabled', $settingValue('welcome_text_section_enabled')) === '0')>Disabled</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="welcome_text_section_button_label" class="form-label">Button Label</label>
                                    <input type="text" class="form-control" id="welcome_text_section_button_label" name="welcome_text_section_button_label" value="{{ old('welcome_text_section_button_label', $settingValue('welcome_text_section_button_label')) }}">
                                </div>
                                <div class="md:col-span-2">
                                    <label for="welcome_text_section_rich_text" class="form-label">Rich Text</label>
                                    <textarea class="form-control font-monospace" id="welcome_text_section_rich_text" name="welcome_text_section_rich_text" rows="8">{{ old('welcome_text_section_rich_text', $settingValue('welcome_text_section_rich_text')) }}</textarea>
                                </div>
                                <div class="md:col-span-2">
                                    <label for="welcome_text_section_subtext" class="form-label">Sub Text</label>
                                    <textarea class="form-control" id="welcome_text_section_subtext" name="welcome_text_section_subtext" rows="4">{{ old('welcome_text_section_subtext', $settingValue('welcome_text_section_subtext')) }}</textarea>
                                </div>
                                <div class="md:col-span-2">
                                    <label for="welcome_text_section_button_link" class="form-label">Button Link</label>
                                    <input type="text" class="form-control" id="welcome_text_section_button_link" name="welcome_text_section_button_link" value="{{ old('welcome_text_section_button_link', $settingValue('welcome_text_section_button_link')) }}">
                                </div>
                            </div>
                        </div>
                    @elseif($activeSection === 'announcement')
                        <div class="mt-6 rounded-[1.3rem] border border-[#ece3d2] bg-[#fffdfa] p-4">
                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label for="welcome_announcement_enabled" class="form-label">Status</label>
                                    <select class="form-select" id="welcome_announcement_enabled" name="welcome_announcement_enabled">
                                        <option value="1" @selected(old('welcome_announcement_enabled', $settingValue('welcome_announcement_enabled')) === '1')>Enabled</option>
                                        <option value="0" @selected(old('welcome_announcement_enabled', $settingValue('welcome_announcement_enabled')) === '0')>Disabled</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="welcome_announcement_speed" class="form-label">Scroll Speed</label>
                                    <select class="form-select" id="welcome_announcement_speed" name="welcome_announcement_speed">
                                        @foreach(['10' => '10s (Fast)', '14' => '14s', '18' => '18s', '20' => '20s', '24' => '24s', '30' => '30s (Slow)'] as $value => $label)
                                            <option value="{{ $value }}" @selected((string) old('welcome_announcement_speed', $settingValue('welcome_announcement_speed')) === (string) $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="md:col-span-2 rounded-[1.2rem] border border-[#ece3d2] bg-white p-4">
                                    <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                                        <div>
                                            <label class="form-label mb-1">Announcement Items</label>
                                            <p class="mb-0 text-xs text-stone-500">Add one or more short messages. When there are multiple items, the public bar separates them with a circle bullet automatically.</p>
                                        </div>
                                        <button type="button" class="btn btn-border-black btn-sm" id="add-announcement-item">
                                            <i class="fas fa-plus me-2"></i>Add More
                                        </button>
                                    </div>
                                    <div id="announcement-builder" class="grid gap-3">
                                        @foreach($announcementRows as $announcementIndex => $announcementRow)
                                            <div class="announcement-item flex items-start gap-3 rounded-2xl border border-[#ece3d2] bg-[#fffdfa] p-3">
                                                <span class="mt-2 inline-flex min-w-[2rem] justify-center rounded-full bg-[#f7ecd7] px-2 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-[#9b7431]">{{ $announcementIndex + 1 }}</span>
                                                <div class="flex-1">
                                                    <input
                                                        type="text"
                                                        class="form-control announcement-item-input"
                                                        value="{{ $announcementRow }}"
                                                        placeholder="Announcement item {{ $announcementIndex + 1 }}"
                                                    >
                                                </div>
                                                <button type="button" class="btn btn-white btn-sm announcement-remove-row">
                                                    Remove
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                    <textarea class="hidden" id="welcome_announcement_text" name="welcome_announcement_text">{{ old('welcome_announcement_text', $settingValue('welcome_announcement_text')) }}</textarea>
                                </div>
                                <div class="md:col-span-2">
                                    <label for="welcome_announcement_link" class="form-label">Announcement Link (Optional)</label>
                                    <input type="text" class="form-control" id="welcome_announcement_link" name="welcome_announcement_link" value="{{ old('welcome_announcement_link', $settingValue('welcome_announcement_link')) }}">
                                </div>
                                <div>
                                    <label for="welcome_announcement_bg_color" class="form-label">Bar Background</label>
                                    <input type="color" class="h-12 w-full cursor-pointer rounded-2xl border border-[#d8c6a1] bg-white p-1" id="welcome_announcement_bg_color" name="welcome_announcement_bg_color" value="{{ old('welcome_announcement_bg_color', $settingValue('welcome_announcement_bg_color')) }}">
                                </div>
                                <div>
                                    <label for="welcome_announcement_text_color" class="form-label">Text Color</label>
                                    <input type="color" class="h-12 w-full cursor-pointer rounded-2xl border border-[#d8c6a1] bg-white p-1" id="welcome_announcement_text_color" name="welcome_announcement_text_color" value="{{ old('welcome_announcement_text_color', $settingValue('welcome_announcement_text_color')) }}">
                                </div>
                            </div>
                        </div>
                    @elseif($activeSection === 'popup')
                        <div class="mt-6 rounded-[1.3rem] border border-[#ece3d2] bg-[#fffdfa] p-4">
                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label for="welcome_popup_enabled" class="form-label">Status</label>
                                    <select class="form-select" id="welcome_popup_enabled" name="welcome_popup_enabled">
                                        <option value="1" @selected(old('welcome_popup_enabled', $settingValue('welcome_popup_enabled')) === '1')>Enabled</option>
                                        <option value="0" @selected(old('welcome_popup_enabled', $settingValue('welcome_popup_enabled')) === '0')>Disabled</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="welcome_popup_delay" class="form-label">Show Delay</label>
                                    <select class="form-select" id="welcome_popup_delay" name="welcome_popup_delay">
                                        @foreach(['0' => 'Instant', '1' => '1s', '2' => '2s', '3' => '3s', '5' => '5s', '8' => '8s'] as $value => $label)
                                            <option value="{{ $value }}" @selected((string) old('welcome_popup_delay', $settingValue('welcome_popup_delay')) === (string) $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="welcome_popup_badge" class="form-label">Badge Text</label>
                                    <input type="text" class="form-control" id="welcome_popup_badge" name="welcome_popup_badge" value="{{ old('welcome_popup_badge', $settingValue('welcome_popup_badge')) }}">
                                </div>
                                <div>
                                    <label for="welcome_popup_price" class="form-label">Price Text</label>
                                    <input type="text" class="form-control" id="welcome_popup_price" name="welcome_popup_price" value="{{ old('welcome_popup_price', $settingValue('welcome_popup_price')) }}">
                                </div>
                                <div class="md:col-span-2">
                                    <label for="welcome_popup_title" class="form-label">Popup Title</label>
                                    <input type="text" class="form-control" id="welcome_popup_title" name="welcome_popup_title" value="{{ old('welcome_popup_title', $settingValue('welcome_popup_title')) }}">
                                </div>
                                <div class="md:col-span-2">
                                    <label for="welcome_popup_message" class="form-label">Popup Message</label>
                                    <textarea class="form-control" id="welcome_popup_message" name="welcome_popup_message" rows="3">{{ old('welcome_popup_message', $settingValue('welcome_popup_message')) }}</textarea>
                                </div>
                                <div>
                                    <label for="welcome_popup_button_label" class="form-label">Button Label</label>
                                    <input type="text" class="form-control" id="welcome_popup_button_label" name="welcome_popup_button_label" value="{{ old('welcome_popup_button_label', $settingValue('welcome_popup_button_label')) }}">
                                </div>
                                <div>
                                    <label for="welcome_popup_button_link" class="form-label">Button Link</label>
                                    <input type="text" class="form-control" id="welcome_popup_button_link" name="welcome_popup_button_link" value="{{ old('welcome_popup_button_link', $settingValue('welcome_popup_button_link')) }}">
                                </div>
                                <div class="md:col-span-2 rounded-[1.2rem] border border-[#ece3d2] bg-white p-3">
                                    <label for="welcome_popup_image" class="form-label">Upload Popup Image</label>
                                    <input type="file" class="form-control mt-2" id="welcome_popup_image" name="welcome_popup_image" accept="image/*">
                                    <p class="mt-2 text-xs text-stone-500">Uploaded image is used first. If none is uploaded, the image URL below is used.</p>
                                    <div class="mt-3 flex h-28 items-center justify-center rounded-2xl border border-dashed border-[#d8c6a1] bg-[#fffdfa] p-3">
                                        <img id="popup-preview-image" src="{{ $popupUploadedUrl }}" alt="Popup image preview" class="{{ $popupUploadedUrl ? '' : 'hidden ' }}max-h-full w-auto object-contain">
                                        <span id="popup-preview-placeholder" class="{{ $popupUploadedUrl ? 'hidden ' : '' }}text-[11px] font-semibold uppercase tracking-[0.24em] text-stone-400">No Uploaded Image</span>
                                    </div>
                                </div>
                                <div class="md:col-span-2">
                                    <label for="welcome_popup_image_url" class="form-label">Image URL (Optional)</label>
                                    <input type="text" class="form-control" id="welcome_popup_image_url" name="welcome_popup_image_url" value="{{ old('welcome_popup_image_url', $settingValue('welcome_popup_image_url')) }}">
                                </div>
                                <div>
                                    <label for="welcome_popup_bg_color" class="form-label">Popup Background</label>
                                    <input type="color" class="h-12 w-full cursor-pointer rounded-2xl border border-[#d8c6a1] bg-white p-1" id="welcome_popup_bg_color" name="welcome_popup_bg_color" value="{{ old('welcome_popup_bg_color', $settingValue('welcome_popup_bg_color')) }}">
                                </div>
                                <div>
                                    <label for="welcome_popup_text_color" class="form-label">Popup Text</label>
                                    <input type="color" class="h-12 w-full cursor-pointer rounded-2xl border border-[#d8c6a1] bg-white p-1" id="welcome_popup_text_color" name="welcome_popup_text_color" value="{{ old('welcome_popup_text_color', $settingValue('welcome_popup_text_color')) }}">
                                </div>
                                <div class="md:col-span-2">
                                    <label for="welcome_popup_accent_color" class="form-label">Accent Color</label>
                                    <input type="color" class="h-12 w-full cursor-pointer rounded-2xl border border-[#d8c6a1] bg-white p-1" id="welcome_popup_accent_color" name="welcome_popup_accent_color" value="{{ old('welcome_popup_accent_color', $settingValue('welcome_popup_accent_color')) }}">
                                </div>
                            </div>
                        </div>
                    @elseif($activeSection === 'email')
                        <div class="mt-6 rounded-[1.3rem] border border-[#ece3d2] bg-[#fffdfa] p-4">
                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="md:col-span-2">
                                    <label for="email_header_logo_url" class="form-label">Email Header Logo URL (Optional Override)</label>
                                    <input type="text" class="form-control" id="email_header_logo_url" name="email_header_logo_url" value="{{ old('email_header_logo_url', $settingValue('email_header_logo_url')) }}">
                                    <p class="mt-2 text-xs text-stone-500">If provided, this URL is used for email header logo. Otherwise the Site Logo from Identity is used.</p>
                                </div>
                                <div class="md:col-span-2 rounded-[1.2rem] border border-[#ece3d2] bg-white p-3">
                                    <label for="email_header_bg_image" class="form-label">Email Header Background Image</label>
                                    <input type="file" class="form-control mt-2" id="email_header_bg_image" name="email_header_bg_image" accept="image/*">
                                    <p class="mt-2 text-xs text-stone-500">Uploaded image is used first for the black header background in all email templates.</p>
                                    <div class="mt-3 flex h-24 items-center justify-center rounded-2xl border border-dashed border-[#d8c6a1] bg-[#fffdfa] p-3">
                                        <img id="email-bg-preview-image" src="{{ $emailHeaderBgUploadedUrl }}" alt="Email header background preview" class="{{ $emailHeaderBgUploadedUrl ? '' : 'hidden ' }}max-h-full w-auto object-contain">
                                        <span id="email-bg-preview-placeholder" class="{{ $emailHeaderBgUploadedUrl ? 'hidden ' : '' }}text-[11px] font-semibold uppercase tracking-[0.24em] text-stone-400">No Uploaded Background</span>
                                    </div>
                                </div>
                                <div class="md:col-span-2">
                                    <label for="email_header_bg_image_url" class="form-label">Email Header Background URL (Optional)</label>
                                    <input type="text" class="form-control" id="email_header_bg_image_url" name="email_header_bg_image_url" value="{{ old('email_header_bg_image_url', $settingValue('email_header_bg_image_url')) }}">
                                    <p class="mt-2 text-xs text-stone-500">Used only when no uploaded background image exists.</p>
                                </div>
                                <div class="md:col-span-2 rounded-2xl border border-[#ece3d2] bg-white p-4 text-sm text-stone-600">
                                    <div class="font-semibold text-stone-900">Global Email Style</div>
                                    <p class="mt-2 mb-0">Email header logo uses this Logo URL if set, otherwise Site Logo from Identity. Buttons are rendered in black with white text. Highlight/emphasis text automatically uses your current Accent color from Theme Colors.</p>
                                </div>
                            </div>
                        </div>
                    @elseif($activeSection === 'onboarding')
                        <div class="mt-6 grid gap-6 lg:grid-cols-2">
                            <div>
                                <label for="onboarding_instructions" class="form-label">Onboarding Instructions</label>
                                <textarea class="form-control" id="onboarding_instructions" name="onboarding_instructions" rows="12">{{ old('onboarding_instructions', $settingValue('onboarding_instructions')) }}</textarea>
                            </div>
                            <div>
                                <label for="onboarding_guide_file" class="form-label">Guide File</label>
                                <input type="file" class="form-control" id="onboarding_guide_file" name="onboarding_guide_file" accept=".pdf,.doc,.docx">
                                @if($settings->get('onboarding_guide_file')?->setting_value)
                                    <a href="{{ storage_public_url($settings->get('onboarding_guide_file')->setting_value) }}" class="btn btn-border-black mt-3" target="_blank">Download current guide</a>
                                @endif
                            </div>
                        </div>
                    @elseif($activeSection === 'pdf')
                        <div class="mt-6 space-y-6">
                            <div class="grid gap-6 xl:grid-cols-[minmax(0,1.2fr)_minmax(320px,0.8fr)]">
                                <div class="rounded-[1.35rem] border border-[#ece3d2] bg-[#fffdfa] p-4">
                                    <div class="mb-4">
                                        <div class="text-sm font-semibold text-stone-900">Global PDF Branding</div>
                                        <p class="mt-2 mb-0 text-sm text-stone-600">These settings apply across onboarding PDFs and all work update PDFs for admin, agent, and client exports.</p>
                                    </div>
                                    <div class="grid gap-4 md:grid-cols-2">
                                        <div>
                                            <label for="pdf_brand_name" class="form-label">PDF Brand Name</label>
                                            <input type="text" class="form-control" id="pdf_brand_name" name="pdf_brand_name" value="{{ old('pdf_brand_name', $settingValue('pdf_brand_name')) }}" placeholder="{{ $settingValue('site_name') }}">
                                            <p class="mb-0 mt-2 text-xs text-stone-500">Leave blank to use the site name automatically.</p>
                                        </div>
                                        <div>
                                            <label for="pdf_logo_url" class="form-label">Logo URL Fallback</label>
                                            <input type="text" class="form-control" id="pdf_logo_url" name="pdf_logo_url" value="{{ old('pdf_logo_url', $settingValue('pdf_logo_url')) }}" placeholder="https://example.com/logo.png">
                                            <p class="mb-0 mt-2 text-xs text-stone-500">Used only when no PDF logo is uploaded.</p>
                                        </div>
                                        <div class="md:col-span-2">
                                            <label for="pdf_footer_note" class="form-label">Footer Note</label>
                                            <textarea class="form-control" id="pdf_footer_note" name="pdf_footer_note" rows="3">{{ old('pdf_footer_note', $settingValue('pdf_footer_note')) }}</textarea>
                                        </div>
                                        <div>
                                            <label for="pdf_generated_label" class="form-label">Generated Label</label>
                                            <input type="text" class="form-control" id="pdf_generated_label" name="pdf_generated_label" value="{{ old('pdf_generated_label', $settingValue('pdf_generated_label')) }}" placeholder="Generated">
                                        </div>
                                        <div>
                                            <label for="pdf_not_available_text" class="form-label">Not Available Text</label>
                                            <input type="text" class="form-control" id="pdf_not_available_text" name="pdf_not_available_text" value="{{ old('pdf_not_available_text', $settingValue('pdf_not_available_text')) }}" placeholder="N/A">
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-[1.35rem] border border-[#ece3d2] bg-[#fffdfa] p-4">
                                    <label for="pdf_logo" class="form-label">Upload PDF Logo</label>
                                    <input type="file" class="form-control mt-2" id="pdf_logo" name="pdf_logo" accept="image/*">
                                    <div class="mt-4 flex h-28 items-center justify-center rounded-2xl border border-dashed border-[#d8c6a1] bg-white p-3">
                                        <img id="pdf-logo-preview-image" src="{{ $pdfLogoUploadedUrl ?: old('pdf_logo_url', $settingValue('pdf_logo_url')) }}" alt="PDF logo preview" class="{{ ($pdfLogoUploadedUrl || old('pdf_logo_url', $settingValue('pdf_logo_url'))) ? '' : 'hidden ' }}max-h-full w-auto object-contain">
                                        <span id="pdf-logo-preview-placeholder" class="{{ ($pdfLogoUploadedUrl || old('pdf_logo_url', $settingValue('pdf_logo_url'))) ? 'hidden ' : '' }}text-[11px] font-semibold uppercase tracking-[0.24em] text-stone-400">No PDF Logo</span>
                                    </div>
                                    <div class="mt-4 rounded-2xl border border-[#e7dcc5] bg-white p-4 text-sm text-stone-600">
                                        <div class="font-semibold text-stone-900">Applies to all PDF exports</div>
                                        <p class="mt-2 mb-0">This customizer controls the shared header, footer, cards, tables, and text styling used in onboarding and work update PDF files.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-[1.35rem] border border-[#ece3d2] bg-[#fffdfa] p-4">
                                <div class="mb-4">
                                    <div class="text-sm font-semibold text-stone-900">PDF Colors</div>
                                    <p class="mt-2 mb-0 text-sm text-stone-600">Keep the palette clean and readable. These colors are used globally by the shared PDF theme partial.</p>
                                </div>
                                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                                    @foreach($pdfColorGroups as [$key, $label])
                                        <div class="rounded-[1.2rem] border border-[#e7dcc5] bg-white p-4">
                                            <label for="{{ $key }}" class="form-label">{{ $label }}</label>
                                            <div class="mt-2 flex items-center gap-3">
                                                <input type="color" class="h-12 w-16 cursor-pointer rounded-2xl border border-[#d8c6a1] bg-white p-1" id="{{ $key }}" name="{{ $key }}" value="{{ old($key, $settingValue($key)) }}" data-color-control="{{ $key }}">
                                                <input type="text" class="form-control" value="{{ old($key, $settingValue($key)) }}" data-color-text="{{ $key }}">
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="rounded-[1.35rem] border border-[#ece3d2] bg-[#fffdfa] p-4">
                                <div class="mb-4">
                                    <div class="text-sm font-semibold text-stone-900">PDF Template Editors</div>
                                    <p class="mt-2 mb-0 text-sm text-stone-600">Edit each PDF template on its own page, similar to the email template workflow. Template pages control titles, table headers, intro copy, empty states, and footer text for each export separately.</p>
                                </div>

                                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                                    @foreach($pdfTemplates as $template)
                                        <div class="rounded-[1.2rem] border border-[#e7dcc5] bg-white p-4">
                                            <div class="text-[11px] font-semibold uppercase tracking-[0.22em] text-[#9b7431]">{{ $template['key'] }}</div>
                                            <div class="mt-2 text-base font-semibold text-stone-900">{{ $template['name'] }}</div>
                                            <p class="mt-2 text-sm leading-6 text-stone-600">{{ $template['description'] }}</p>
                                            <p class="mt-3 mb-0 text-xs text-stone-500">{{ count($template['fields']) }} editable fields</p>
                                            <a href="{{ route('admin.pdf-templates.edit', ['template' => $template['key']]) }}" class="btn btn-black btn-sm mt-4 w-100">
                                                <i class="fas fa-pen me-2"></i>Edit Template
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @elseif($activeSection === 'client-guide')
                        <div class="mt-6 space-y-6">
                            <div class="rounded-[1.35rem] border border-[#ece3d2] bg-[#fffdfa] p-4">
                                <div class="grid gap-4 md:grid-cols-2">
                                    <div>
                                        <label for="client_guide_badge" class="form-label">Page Badge</label>
                                        <input type="text" class="form-control" id="client_guide_badge" name="client_guide_badge" value="{{ old('client_guide_badge', $settingValue('client_guide_badge')) }}">
                                    </div>
                                    <div>
                                        <label for="client_guide_title" class="form-label">Page Title</label>
                                        <input type="text" class="form-control" id="client_guide_title" name="client_guide_title" value="{{ old('client_guide_title', $settingValue('client_guide_title')) }}">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label for="client_guide_subtitle" class="form-label">Page Subtitle</label>
                                        <textarea class="form-control" id="client_guide_subtitle" name="client_guide_subtitle" rows="4">{{ old('client_guide_subtitle', $settingValue('client_guide_subtitle')) }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="grid gap-6 xl:grid-cols-2">
                                <div class="rounded-[1.35rem] border border-[#ece3d2] bg-[#fffdfa] p-4">
                                    <div class="mb-4 text-sm font-semibold text-stone-900">Intro Panel</div>
                                    <div class="grid gap-4">
                                        <div>
                                            <label for="client_guide_intro_title" class="form-label">Intro Title</label>
                                            <input type="text" class="form-control" id="client_guide_intro_title" name="client_guide_intro_title" value="{{ old('client_guide_intro_title', $settingValue('client_guide_intro_title')) }}">
                                        </div>
                                        <div>
                                            <label for="client_guide_intro_text" class="form-label">Intro Text</label>
                                            <textarea class="form-control" id="client_guide_intro_text" name="client_guide_intro_text" rows="7">{{ old('client_guide_intro_text', $settingValue('client_guide_intro_text')) }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-[1.35rem] border border-[#ece3d2] bg-[#fffdfa] p-4">
                                    <div class="mb-4 text-sm font-semibold text-stone-900">Support Panel and Buttons</div>
                                    <div class="grid gap-4">
                                        <div>
                                            <label for="client_guide_support_title" class="form-label">Support Title</label>
                                            <input type="text" class="form-control" id="client_guide_support_title" name="client_guide_support_title" value="{{ old('client_guide_support_title', $settingValue('client_guide_support_title')) }}">
                                        </div>
                                        <div>
                                            <label for="client_guide_support_text" class="form-label">Support Text</label>
                                            <textarea class="form-control" id="client_guide_support_text" name="client_guide_support_text" rows="7">{{ old('client_guide_support_text', $settingValue('client_guide_support_text')) }}</textarea>
                                        </div>
                                        <div class="grid gap-4 md:grid-cols-2">
                                            <div>
                                                <label for="client_guide_primary_label" class="form-label">Primary Button Label</label>
                                                <input type="text" class="form-control" id="client_guide_primary_label" name="client_guide_primary_label" value="{{ old('client_guide_primary_label', $settingValue('client_guide_primary_label')) }}">
                                            </div>
                                            <div>
                                                <label for="client_guide_primary_link" class="form-label">Primary Button Link</label>
                                                <input type="text" class="form-control" id="client_guide_primary_link" name="client_guide_primary_link" value="{{ old('client_guide_primary_link', $settingValue('client_guide_primary_link')) }}">
                                            </div>
                                            <div>
                                                <label for="client_guide_secondary_label" class="form-label">Secondary Button Label</label>
                                                <input type="text" class="form-control" id="client_guide_secondary_label" name="client_guide_secondary_label" value="{{ old('client_guide_secondary_label', $settingValue('client_guide_secondary_label')) }}">
                                            </div>
                                            <div>
                                                <label for="client_guide_secondary_link" class="form-label">Secondary Button Link</label>
                                                <input type="text" class="form-control" id="client_guide_secondary_link" name="client_guide_secondary_link" value="{{ old('client_guide_secondary_link', $settingValue('client_guide_secondary_link')) }}">
                                            </div>
                                        </div>
                                        <div class="rounded-2xl border border-[#e7dcc5] bg-white p-4 text-sm text-stone-600">
                                            <div class="font-semibold text-stone-900">Public URL</div>
                                            <p class="mt-2 mb-0">{{ route('guide.page') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-[1.35rem] border border-[#ece3d2] bg-[#fffdfa] p-4">
                                <div class="mb-4 text-sm font-semibold text-stone-900">Step-by-Step Content</div>
                                <div class="grid gap-4 xl:grid-cols-2">
                                    @foreach(range(1, 6) as $stepNumber)
                                        <div class="rounded-[1.2rem] border border-[#e7dcc5] bg-white p-4">
                                            <div class="mb-3 text-[11px] font-semibold uppercase tracking-[0.24em] text-[#9b7431]">Step {{ $stepNumber }}</div>
                                            <div class="grid gap-4">
                                                <div>
                                                    <label for="client_guide_step_{{ $stepNumber }}_eyebrow" class="form-label">Step {{ $stepNumber }} Eyebrow</label>
                                                    <input type="text" class="form-control" id="client_guide_step_{{ $stepNumber }}_eyebrow" name="client_guide_step_{{ $stepNumber }}_eyebrow" value="{{ old("client_guide_step_{$stepNumber}_eyebrow", $settingValue("client_guide_step_{$stepNumber}_eyebrow")) }}">
                                                </div>
                                                <div>
                                                    <label for="client_guide_step_{{ $stepNumber }}_title" class="form-label">Step {{ $stepNumber }} Title</label>
                                                    <input type="text" class="form-control" id="client_guide_step_{{ $stepNumber }}_title" name="client_guide_step_{{ $stepNumber }}_title" value="{{ old("client_guide_step_{$stepNumber}_title", $settingValue("client_guide_step_{$stepNumber}_title")) }}">
                                                </div>
                                                <div>
                                                    <label for="client_guide_step_{{ $stepNumber }}_body" class="form-label">Step {{ $stepNumber }} Description</label>
                                                    <textarea class="form-control" id="client_guide_step_{{ $stepNumber }}_body" name="client_guide_step_{{ $stepNumber }}_body" rows="6">{{ old("client_guide_step_{$stepNumber}_body", $settingValue("client_guide_step_{$stepNumber}_body")) }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @elseif($activeSection === 'policies')
                        <div class="mt-6 space-y-6">
                            <div class="grid gap-4 xl:grid-cols-4">
                                <div class="rounded-[1.35rem] border border-[#ece3d2] bg-[#fffdfa] p-4">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[#9b7431]">Privacy Policy URL</p>
                                    <p class="mt-3 mb-0 break-all text-sm font-semibold text-stone-900">{{ route('privacy-policy.page') }}</p>
                                </div>
                                <div class="rounded-[1.35rem] border border-[#ece3d2] bg-[#fffdfa] p-4">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[#9b7431]">Terms URL</p>
                                    <p class="mt-3 mb-0 break-all text-sm font-semibold text-stone-900">{{ route('terms-of-service.page') }}</p>
                                </div>
                                <div class="rounded-[1.35rem] border border-[#ece3d2] bg-[#fffdfa] p-4">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[#9b7431]">Booking URL</p>
                                    <p class="mt-3 mb-0 break-all text-sm font-semibold text-stone-900">{{ route('booking-policy.page') }}</p>
                                </div>
                                <div class="rounded-[1.35rem] border border-[#ece3d2] bg-[#fffdfa] p-4">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[#9b7431]">Refund URL</p>
                                    <p class="mt-3 mb-0 break-all text-sm font-semibold text-stone-900">{{ route('refund-policy.page') }}</p>
                                </div>
                            </div>

                            <div class="rounded-[1.35rem] border border-[#ece3d2] bg-[#fffdfa] p-4">
                                <div class="mb-4">
                                    <div class="text-sm font-semibold text-stone-900">Privacy Policy</div>
                                    <p class="mt-2 mb-0 text-sm text-stone-600">Use the fields below to control the top card and the rich text editor to control the main privacy policy content.</p>
                                </div>
                                <div class="mb-4 grid gap-4 md:grid-cols-2">
                                    <div class="md:col-span-2">
                                        <label for="privacy_policy_title" class="form-label">Top Card Title</label>
                                        <input type="text" class="form-control" id="privacy_policy_title" name="privacy_policy_title" value="{{ old('privacy_policy_title', $settingValue('privacy_policy_title')) }}">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label for="privacy_policy_subtitle" class="form-label">Top Card Subtitle</label>
                                        <textarea class="form-control" id="privacy_policy_subtitle" name="privacy_policy_subtitle" rows="3">{{ old('privacy_policy_subtitle', $settingValue('privacy_policy_subtitle')) }}</textarea>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label for="privacy_policy_meta_text" class="form-label">Top Card Meta Text</label>
                                        <input type="text" class="form-control" id="privacy_policy_meta_text" name="privacy_policy_meta_text" value="{{ old('privacy_policy_meta_text', $settingValue('privacy_policy_meta_text')) }}">
                                        <p class="mt-2 mb-0 text-xs text-stone-500">Use <code>{date}</code> to show the latest updated date automatically.</p>
                                    </div>
                                </div>
                                <textarea class="hidden" id="privacy_policy_content" name="privacy_policy_content">{{ old('privacy_policy_content', $settingValue('privacy_policy_content')) }}</textarea>
                                <div id="privacy_policy_content_editor" class="policy-editor-shell"></div>
                            </div>

                            <div class="rounded-[1.35rem] border border-[#ece3d2] bg-[#fffdfa] p-4">
                                <div class="mb-4">
                                    <div class="text-sm font-semibold text-stone-900">Terms of Service</div>
                                    <p class="mt-2 mb-0 text-sm text-stone-600">Edit the top card content here and keep the main policy body in the rich text editor below.</p>
                                </div>
                                <div class="mb-4 grid gap-4 md:grid-cols-2">
                                    <div class="md:col-span-2">
                                        <label for="terms_of_service_title" class="form-label">Top Card Title</label>
                                        <input type="text" class="form-control" id="terms_of_service_title" name="terms_of_service_title" value="{{ old('terms_of_service_title', $settingValue('terms_of_service_title')) }}">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label for="terms_of_service_subtitle" class="form-label">Top Card Subtitle</label>
                                        <textarea class="form-control" id="terms_of_service_subtitle" name="terms_of_service_subtitle" rows="3">{{ old('terms_of_service_subtitle', $settingValue('terms_of_service_subtitle')) }}</textarea>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label for="terms_of_service_meta_text" class="form-label">Top Card Meta Text</label>
                                        <input type="text" class="form-control" id="terms_of_service_meta_text" name="terms_of_service_meta_text" value="{{ old('terms_of_service_meta_text', $settingValue('terms_of_service_meta_text')) }}">
                                        <p class="mt-2 mb-0 text-xs text-stone-500">Use <code>{date}</code> to show the latest updated date automatically.</p>
                                    </div>
                                </div>
                                <textarea class="hidden" id="terms_of_service_content" name="terms_of_service_content">{{ old('terms_of_service_content', $settingValue('terms_of_service_content')) }}</textarea>
                                <div id="terms_of_service_content_editor" class="policy-editor-shell"></div>
                            </div>

                            <div class="rounded-[1.35rem] border border-[#ece3d2] bg-[#fffdfa] p-4">
                                <div class="mb-4">
                                    <div class="text-sm font-semibold text-stone-900">Booking Policy</div>
                                    <p class="mt-2 mb-0 text-sm text-stone-600">Manage the booking policy top card here and keep the full booking terms in the rich text editor below.</p>
                                </div>
                                <div class="mb-4 grid gap-4 md:grid-cols-2">
                                    <div class="md:col-span-2">
                                        <label for="booking_policy_title" class="form-label">Top Card Title</label>
                                        <input type="text" class="form-control" id="booking_policy_title" name="booking_policy_title" value="{{ old('booking_policy_title', $settingValue('booking_policy_title')) }}">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label for="booking_policy_subtitle" class="form-label">Top Card Subtitle</label>
                                        <textarea class="form-control" id="booking_policy_subtitle" name="booking_policy_subtitle" rows="3">{{ old('booking_policy_subtitle', $settingValue('booking_policy_subtitle')) }}</textarea>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label for="booking_policy_meta_text" class="form-label">Top Card Meta Text</label>
                                        <input type="text" class="form-control" id="booking_policy_meta_text" name="booking_policy_meta_text" value="{{ old('booking_policy_meta_text', $settingValue('booking_policy_meta_text')) }}">
                                        <p class="mt-2 mb-0 text-xs text-stone-500">Use <code>{date}</code> to show the latest updated date automatically.</p>
                                    </div>
                                </div>
                                <textarea class="hidden" id="booking_policy_content" name="booking_policy_content">{{ old('booking_policy_content', $settingValue('booking_policy_content')) }}</textarea>
                                <div id="booking_policy_content_editor" class="policy-editor-shell"></div>
                            </div>

                            <div class="rounded-[1.35rem] border border-[#ece3d2] bg-[#fffdfa] p-4">
                                <div class="mb-4">
                                    <div class="text-sm font-semibold text-stone-900">Refund Policy</div>
                                    <p class="mt-2 mb-0 text-sm text-stone-600">You can manage the refund page top card here and keep the full refund policy content below.</p>
                                </div>
                                <div class="mb-4 grid gap-4 md:grid-cols-2">
                                    <div class="md:col-span-2">
                                        <label for="refund_policy_title" class="form-label">Top Card Title</label>
                                        <input type="text" class="form-control" id="refund_policy_title" name="refund_policy_title" value="{{ old('refund_policy_title', $settingValue('refund_policy_title')) }}">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label for="refund_policy_subtitle" class="form-label">Top Card Subtitle</label>
                                        <textarea class="form-control" id="refund_policy_subtitle" name="refund_policy_subtitle" rows="3">{{ old('refund_policy_subtitle', $settingValue('refund_policy_subtitle')) }}</textarea>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label for="refund_policy_meta_text" class="form-label">Top Card Meta Text</label>
                                        <input type="text" class="form-control" id="refund_policy_meta_text" name="refund_policy_meta_text" value="{{ old('refund_policy_meta_text', $settingValue('refund_policy_meta_text')) }}">
                                        <p class="mt-2 mb-0 text-xs text-stone-500">Use <code>{date}</code> to show the latest updated date automatically.</p>
                                    </div>
                                </div>
                                <textarea class="hidden" id="refund_policy_content" name="refund_policy_content">{{ old('refund_policy_content', $settingValue('refund_policy_content')) }}</textarea>
                                <div id="refund_policy_content_editor" class="policy-editor-shell"></div>
                            </div>
                        </div>
                    @elseif($activeSection === 'code')
                        <div class="mt-6 grid gap-6 lg:grid-cols-2">
                            <div>
                                <label for="custom_css" class="form-label">Custom CSS</label>
                                <textarea class="form-control font-monospace" id="custom_css" name="custom_css" rows="12">{{ old('custom_css', $settings->get('custom_css')?->setting_value ?? '') }}</textarea>
                            </div>
                            <div>
                                <label for="custom_js" class="form-label">Custom JavaScript</label>
                                <textarea class="form-control font-monospace" id="custom_js" name="custom_js" rows="12">{{ old('custom_js', $settings->get('custom_js')?->setting_value ?? '') }}</textarea>
                            </div>
                        </div>
                    @endif
                </section>

                <section class="rounded-[1.8rem] border border-[#e7dcc5] bg-white/95 p-5 shadow-[0_24px_60px_rgba(17,17,17,0.06)]">
                    <button type="submit" class="btn btn-black w-100">
                        <i class="fas fa-save me-2"></i>Save {{ $sections[$activeSection]['label'] ?? 'Section' }}
                    </button>
                </section>
            </form>

            <form action="{{ route('admin.customization.reset') }}" method="POST" onsubmit="return confirm('Reset all customization settings to the default theme?');" class="mt-4">
                @csrf
                <input type="hidden" name="section" value="{{ $activeSection }}">
                <button type="submit" class="btn btn-outline-danger w-100">
                    <i class="fas fa-rotate-left me-2"></i>Reset to Default
                </button>
            </form>
        </div>
    </div>

    @if($activeSection === 'policies')
        @push('styles')
            <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
            <style>
                .policy-editor-shell .ql-toolbar.ql-snow {
                    border: 1px solid #e7dcc5;
                    border-radius: 1rem 1rem 0 0;
                    background: #fffdfa;
                }

                .policy-editor-shell .ql-container.ql-snow {
                    border: 1px solid #e7dcc5;
                    border-top: 0;
                    border-radius: 0 0 1rem 1rem;
                    background: #ffffff;
                    min-height: 22rem;
                }

                .policy-editor-shell .ql-editor {
                    min-height: 22rem;
                    font-size: 0.98rem;
                    line-height: 1.8;
                    color: #1c1917;
                }
            </style>
        @endpush

        @push('scripts')
            <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
        @endpush
    @endif

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('[data-color-control]').forEach(function (colorInput) {
                    const key = colorInput.getAttribute('data-color-control');
                    const textInput = document.querySelector('[data-color-text="' + key + '"]');

                    if (!textInput) return;

                    const syncFromColor = function () {
                        textInput.value = colorInput.value.toUpperCase();
                    };

                    const syncFromText = function () {
                        if (/^#[0-9A-F]{6}$/i.test(textInput.value)) {
                            colorInput.value = textInput.value;
                        }
                    };

                    colorInput.addEventListener('input', syncFromColor);
                    textInput.addEventListener('input', syncFromText);
                    textInput.addEventListener('blur', syncFromColor);
                });

                const bindFilePreview = function (inputId, imageId, placeholderId) {
                    const input = document.getElementById(inputId);
                    const image = document.getElementById(imageId);
                    const placeholder = document.getElementById(placeholderId);

                    if (!input || !image || !placeholder) return;

                    input.addEventListener('change', function () {
                        const file = input.files && input.files[0];
                        if (!file) return;

                        const reader = new FileReader();
                        reader.onload = function (event) {
                            image.src = event.target.result;
                            image.classList.remove('hidden');
                            placeholder.classList.add('hidden');
                        };
                        reader.readAsDataURL(file);
                    });
                };

                bindFilePreview('site_logo', 'logo-preview-image', 'logo-preview-placeholder');
                bindFilePreview('site_favicon', 'favicon-preview-image', 'favicon-preview-placeholder');
                bindFilePreview('footer_branding_logo', 'footer-branding-logo-preview-image', 'footer-branding-logo-preview-placeholder');
                bindFilePreview('welcome_banner_image', 'banner-preview-image', 'banner-preview-placeholder');
                bindFilePreview('welcome_banner_mobile_image', 'banner-mobile-preview-image', 'banner-mobile-preview-placeholder');
                bindFilePreview('welcome_popup_image', 'popup-preview-image', 'popup-preview-placeholder');
                bindFilePreview('email_header_bg_image', 'email-bg-preview-image', 'email-bg-preview-placeholder');
                bindFilePreview('pdf_logo', 'pdf-logo-preview-image', 'pdf-logo-preview-placeholder');

                const form = document.getElementById('customization-section-form');
                const policyBuilder = document.getElementById('policy-links-builder');
                const socialBuilder = document.getElementById('social-links-builder');
                const policyHiddenInput = document.getElementById('footer_policy_links');
                const socialHiddenInput = document.getElementById('footer_social_links');
                const addPolicyBtn = document.getElementById('add-policy-link');
                const addSocialBtn = document.getElementById('add-social-link');
                const announcementBuilder = document.getElementById('announcement-builder');
                const announcementHiddenInput = document.getElementById('welcome_announcement_text');
                const addAnnouncementBtn = document.getElementById('add-announcement-item');
                const socialIconOptions = @json($socialIconOptions);

                const refreshRemoveButtons = function (container) {
                    if (!container) return;
                    const rows = Array.from(container.querySelectorAll('.footer-policy-item, .footer-social-item'));
                    rows.forEach((row) => {
                        const removeBtn = row.querySelector('.footer-remove-row');
                        if (!removeBtn) return;
                        removeBtn.disabled = rows.length <= 1;
                    });
                };

                const serializePolicyRows = function () {
                    if (!policyBuilder || !policyHiddenInput) return;
                    const lines = Array.from(policyBuilder.querySelectorAll('.footer-policy-item'))
                        .map((row) => {
                            const label = row.querySelector('.footer-policy-label')?.value?.trim() || '';
                            const url = row.querySelector('.footer-policy-url')?.value?.trim() || '';
                            return (label && url) ? `${label}|${url}` : '';
                        })
                        .filter(Boolean);

                    policyHiddenInput.value = lines.join('\n');
                };

                const serializeSocialRows = function () {
                    if (!socialBuilder || !socialHiddenInput) return;
                    const lines = Array.from(socialBuilder.querySelectorAll('.footer-social-item'))
                        .map((row) => {
                            const label = row.querySelector('.footer-social-label')?.value?.trim() || '';
                            const url = row.querySelector('.footer-social-url')?.value?.trim() || '';
                            const icon = row.querySelector('.footer-social-icon')?.value?.trim() || 'fas fa-link';
                            return (label && url) ? `${label}|${url}|${icon}` : '';
                        })
                        .filter(Boolean);

                    socialHiddenInput.value = lines.join('\n');
                };

                const serializeFooterRows = function () {
                    serializePolicyRows();
                    serializeSocialRows();
                };

                const refreshAnnouncementRows = function () {
                    if (!announcementBuilder) return;

                    const rows = Array.from(announcementBuilder.querySelectorAll('.announcement-item'));

                    rows.forEach((row, index) => {
                        const badge = row.querySelector('[data-announcement-index]');
                        const input = row.querySelector('.announcement-item-input');
                        const removeBtn = row.querySelector('.announcement-remove-row');

                        if (badge) {
                            badge.textContent = String(index + 1);
                        }

                        if (input) {
                            input.placeholder = `Announcement item ${index + 1}`;
                        }

                        if (removeBtn) {
                            removeBtn.disabled = rows.length <= 1;
                        }
                    });
                };

                const serializeAnnouncementRows = function () {
                    if (!announcementBuilder || !announcementHiddenInput) return;

                    const lines = Array.from(announcementBuilder.querySelectorAll('.announcement-item-input'))
                        .map((input) => input.value.trim())
                        .filter(Boolean);

                    announcementHiddenInput.value = lines.join('\n');
                };

                const createPolicyRow = function () {
                    const row = document.createElement('div');
                    row.className = 'grid gap-2 rounded-xl border border-[#e7dcc5] bg-white p-3 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto] footer-policy-item';
                    row.innerHTML = `
                        <input type="text" class="form-control footer-policy-label" placeholder="Label (e.g., Privacy Policy)">
                        <input type="text" class="form-control footer-policy-url" placeholder="URL (e.g., /privacy-policy)">
                        <button type="button" class="btn btn-outline-danger btn-sm footer-remove-row">
                            <i class="fas fa-trash"></i>
                        </button>
                    `;
                    return row;
                };

                const createAnnouncementRow = function (value = '') {
                    const row = document.createElement('div');
                    row.className = 'announcement-item flex items-start gap-3 rounded-2xl border border-[#ece3d2] bg-[#fffdfa] p-3';

                    const badge = document.createElement('span');
                    badge.className = 'mt-2 inline-flex min-w-[2rem] justify-center rounded-full bg-[#f7ecd7] px-2 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-[#9b7431]';
                    badge.setAttribute('data-announcement-index', 'true');
                    badge.textContent = '1';

                    const inputWrap = document.createElement('div');
                    inputWrap.className = 'flex-1';

                    const input = document.createElement('input');
                    input.type = 'text';
                    input.className = 'form-control announcement-item-input';
                    input.value = value;
                    input.placeholder = 'Announcement item';

                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'btn btn-white btn-sm announcement-remove-row';
                    removeBtn.textContent = 'Remove';

                    inputWrap.appendChild(input);
                    row.appendChild(badge);
                    row.appendChild(inputWrap);
                    row.appendChild(removeBtn);

                    return row;
                };

                const createSocialRow = function () {
                    const iconOptionsHtml = Object.entries(socialIconOptions)
                        .map(([value, label]) => `<option value="${value}">${label} (${value})</option>`)
                        .join('');

                    const row = document.createElement('div');
                    row.className = 'grid gap-2 rounded-xl border border-[#e7dcc5] bg-white p-3 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(0,1fr)_auto] footer-social-item';
                    row.innerHTML = `
                        <input type="text" class="form-control footer-social-label" placeholder="Label (internal)">
                        <input type="text" class="form-control footer-social-url" placeholder="URL">
                        <select class="form-select footer-social-icon">${iconOptionsHtml}</select>
                        <button type="button" class="btn btn-outline-danger btn-sm footer-remove-row">
                            <i class="fas fa-trash"></i>
                        </button>
                    `;
                    return row;
                };

                addPolicyBtn?.addEventListener('click', function () {
                    if (!policyBuilder) return;
                    policyBuilder.appendChild(createPolicyRow());
                    refreshRemoveButtons(policyBuilder);
                });

                addSocialBtn?.addEventListener('click', function () {
                    if (!socialBuilder) return;
                    socialBuilder.appendChild(createSocialRow());
                    refreshRemoveButtons(socialBuilder);
                });

                addAnnouncementBtn?.addEventListener('click', function () {
                    if (!announcementBuilder) return;
                    announcementBuilder.appendChild(createAnnouncementRow());
                    refreshAnnouncementRows();
                    serializeAnnouncementRows();
                });

                policyBuilder?.addEventListener('click', function (event) {
                    const removeBtn = event.target.closest('.footer-remove-row');
                    if (!removeBtn || removeBtn.disabled) return;
                    removeBtn.closest('.footer-policy-item')?.remove();
                    refreshRemoveButtons(policyBuilder);
                    serializePolicyRows();
                });

                socialBuilder?.addEventListener('click', function (event) {
                    const removeBtn = event.target.closest('.footer-remove-row');
                    if (!removeBtn || removeBtn.disabled) return;
                    removeBtn.closest('.footer-social-item')?.remove();
                    refreshRemoveButtons(socialBuilder);
                    serializeSocialRows();
                });

                announcementBuilder?.addEventListener('input', function (event) {
                    if (!(event.target instanceof HTMLInputElement) || !event.target.classList.contains('announcement-item-input')) {
                        return;
                    }

                    serializeAnnouncementRows();
                });

                announcementBuilder?.addEventListener('click', function (event) {
                    const removeBtn = event.target.closest('.announcement-remove-row');
                    if (!removeBtn || removeBtn.disabled) return;

                    removeBtn.closest('.announcement-item')?.remove();
                    refreshAnnouncementRows();
                    serializeAnnouncementRows();
                });

                policyBuilder?.addEventListener('input', serializePolicyRows);
                socialBuilder?.addEventListener('input', serializeSocialRows);
                socialBuilder?.addEventListener('change', serializeSocialRows);

                form?.addEventListener('submit', function () {
                    serializeFooterRows();
                    serializeAnnouncementRows();
                });

                refreshAnnouncementRows();
                serializeAnnouncementRows();

                if (window.Quill) {
                    const policyToolbar = [
                        [{ header: [1, 2, 3, false] }],
                        ['bold', 'italic', 'underline'],
                        [{ list: 'ordered' }, { list: 'bullet' }],
                        [{ align: [] }],
                        ['link', 'blockquote'],
                        ['clean']
                    ];

                    const policyEditors = [
                        'privacy_policy_content',
                        'terms_of_service_content',
                        'booking_policy_content',
                        'refund_policy_content'
                    ];

                    policyEditors.forEach(function (fieldId) {
                        const textarea = document.getElementById(fieldId);
                        const editorElement = document.getElementById(fieldId + '_editor');

                        if (!textarea || !editorElement) {
                            return;
                        }

                        const quill = new Quill('#' + fieldId + '_editor', {
                            theme: 'snow',
                            modules: { toolbar: policyToolbar },
                            placeholder: 'Write the public policy content here...'
                        });

                        quill.root.innerHTML = textarea.value || '';

                        quill.on('text-change', function () {
                            textarea.value = quill.root.innerHTML;
                        });

                        form?.addEventListener('submit', function () {
                            textarea.value = quill.root.innerHTML;
                        });
                    });
                }

                refreshRemoveButtons(policyBuilder);
                refreshRemoveButtons(socialBuilder);
                serializeFooterRows();
            });
        </script>
    @endpush
</x-app-layout>
