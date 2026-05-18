<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class CustomizationSetting extends Model
{
    protected static $cachedActiveSettings = null;

    protected static array $resolvedValues = [];

    protected static array $assetUrls = [];

    protected $fillable = [
        'setting_key',
        'setting_value',
        'setting_type',
        'category',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get a setting value by key.
     */
    public static function getValue(string $key, $default = null)
    {
        if (!array_key_exists($key, static::$resolvedValues)) {
            static::$resolvedValues[$key] = static::getAllActive()->get($key)?->setting_value;
        }

        $value = static::$resolvedValues[$key];

        return ($value !== null && $value !== '')
            ? $value
            : ($default ?? static::defaultValue($key));
    }

    /**
     * Get the stored setting value without converting blank strings to defaults.
     */
    public static function getStoredValue(string $key, $default = null)
    {
        if (!array_key_exists($key, static::$resolvedValues)) {
            static::$resolvedValues[$key] = static::getAllActive()->get($key)?->setting_value;
        }

        $value = static::$resolvedValues[$key];

        return $value !== null ? $value : $default;
    }

    /**
     * Set a setting value.
     */
    public static function setValue(string $key, $value, string $type = 'text', string $category = 'general', string $description = null)
    {
        $setting = static::updateOrCreate(
            ['setting_key' => $key],
            [
                'setting_value' => $value,
                'setting_type' => $type,
                'category' => $category,
                'description' => $description,
                'is_active' => true,
            ]
        );

        static::clearCache();

        return $setting;
    }

    /**
     * Get all settings by category.
     */
    public static function getByCategory(string $category)
    {
        return static::where('category', $category)->where('is_active', true)->get();
    }

    /**
     * Get all active settings.
     */
    public static function getAllActive()
    {
        if (static::$cachedActiveSettings !== null) {
            return static::$cachedActiveSettings;
        }

        return static::$cachedActiveSettings = Cache::remember('all_customization_settings', 3600, function () {
            return static::where('is_active', true)->get()->keyBy('setting_key');
        });
    }

    /**
     * Build a cache-friendly asset URL for a stored customization file.
     */
    public static function getAssetUrl(string $key, ?string $default = null): ?string
    {
        if (!array_key_exists($key, static::$assetUrls)) {
            $setting = static::getAllActive()->get($key);
            $path = ltrim(trim((string) ($setting?->setting_value ?? '')), '/');

            if ($path === '') {
                static::$assetUrls[$key] = null;
            } else {
                $url = storage_public_url($path);
                $version = $setting?->updated_at?->timestamp;

                static::$assetUrls[$key] = $version ? "{$url}?v={$version}" : $url;
            }
        }

        return static::$assetUrls[$key] ?? $default;
    }

    /**
     * Create any missing default settings.
     */
    public static function syncDefaults(): void
    {
        foreach (static::defaultDefinitions() as $key => $definition) {
            static::firstOrCreate(
                ['setting_key' => $key],
                array_merge($definition, ['setting_key' => $key])
            );
        }

        static::clearCache();
    }

    /**
     * Reset all settings back to the modern default theme.
     */
    public static function resetToDefaults(): void
    {
        static::query()->update(['is_active' => false]);

        foreach (static::defaultDefinitions() as $key => $definition) {
            static::updateOrCreate(
                ['setting_key' => $key],
                array_merge($definition, ['setting_key' => $key, 'is_active' => true])
            );
        }

        static::clearCache();
    }

    /**
     * Get a default value for a setting key.
     */
    public static function defaultValue(string $key, $fallback = null)
    {
        return static::defaultDefinitions()[$key]['setting_value'] ?? $fallback;
    }

    /**
     * Google font catalog used by the customization UI and dynamic styles.
     */
    public static function fontCatalog(): array
    {
        return [
            'Inter' => 'Inter:wght@400;500;600;700;800',
            'Poppins' => 'Poppins:wght@400;500;600;700;800',
            'Manrope' => 'Manrope:wght@400;500;600;700;800',
            'Open Sans' => 'Open+Sans:wght@400;500;600;700;800',
            'Montserrat' => 'Montserrat:wght@400;500;600;700;800',
            'Lato' => 'Lato:wght@400;700;900',
            'Playfair Display' => 'Playfair+Display:wght@600;700;800',
            'DM Serif Display' => 'DM+Serif+Display',
        ];
    }

    /**
     * Allowed body fonts.
     */
    public static function bodyFontOptions(): array
    {
        return [
            'Inter',
            'Manrope',
            'Open Sans',
            'Lato',
            'Montserrat',
        ];
    }

    /**
     * Allowed heading/display fonts.
     */
    public static function displayFontOptions(): array
    {
        return [
            'Poppins',
            'Montserrat',
            'Playfair Display',
            'DM Serif Display',
        ];
    }

    /**
     * Build a Google Fonts stylesheet URL for the selected font families.
     */
    public static function googleFontHref(array $families): ?string
    {
        $catalog = static::fontCatalog();
        $queries = [];

        foreach (array_unique(array_filter($families)) as $family) {
            if (!isset($catalog[$family])) {
                continue;
            }

            $queries[] = 'family=' . $catalog[$family];
        }

        if ($queries === []) {
            return null;
        }

        return 'https://fonts.googleapis.com/css2?' . implode('&', $queries) . '&display=swap';
    }

    /**
     * Clear all cache.
     */
    public static function clearCache()
    {
        Cache::forget('all_customization_settings');
        static::$cachedActiveSettings = null;
        static::$resolvedValues = [];
        static::$assetUrls = [];

        static::query()->pluck('setting_key')->each(function ($settingKey) {
            Cache::forget("customization_setting_{$settingKey}");
        });
    }

    /**
     * Default settings that match the current frontend theme.
     */
    public static function defaultDefinitions(): array
    {
        return [
            'site_name' => [
                'setting_value' => 'W Automation',
                'setting_type' => 'text',
                'category' => 'branding',
                'description' => 'Site name',
                'is_active' => true,
            ],
            'site_tagline' => [
                'setting_value' => 'Modern Classic Resume Studio',
                'setting_type' => 'text',
                'category' => 'branding',
                'description' => 'Brand tagline',
                'is_active' => true,
            ],
            'site_logo' => [
                'setting_value' => null,
                'setting_type' => 'image',
                'category' => 'branding',
                'description' => 'Site logo',
                'is_active' => true,
            ],
            'site_favicon' => [
                'setting_value' => null,
                'setting_type' => 'image',
                'category' => 'branding',
                'description' => 'Site favicon',
                'is_active' => true,
            ],
            'email_header_logo' => [
                'setting_value' => null,
                'setting_type' => 'image',
                'category' => 'email',
                'description' => 'Email header logo',
                'is_active' => true,
            ],
            'email_header_logo_url' => [
                'setting_value' => '',
                'setting_type' => 'text',
                'category' => 'email',
                'description' => 'Email header logo URL',
                'is_active' => true,
            ],
            'email_header_bg_image' => [
                'setting_value' => null,
                'setting_type' => 'image',
                'category' => 'email',
                'description' => 'Email header background image',
                'is_active' => true,
            ],
            'email_header_bg_image_url' => [
                'setting_value' => '',
                'setting_type' => 'text',
                'category' => 'email',
                'description' => 'Email header background image URL',
                'is_active' => true,
            ],
            'email_footer_note' => [
                'setting_value' => 'This is an automated email.',
                'setting_type' => 'text',
                'category' => 'email',
                'description' => 'Email footer note',
                'is_active' => true,
            ],
            'pdf_brand_name' => [
                'setting_value' => '',
                'setting_type' => 'text',
                'category' => 'pdf',
                'description' => 'PDF brand name override',
                'is_active' => true,
            ],
            'pdf_footer_note' => [
                'setting_value' => 'Confidential workspace export.',
                'setting_type' => 'text',
                'category' => 'pdf',
                'description' => 'PDF footer note',
                'is_active' => true,
            ],
            'pdf_logo' => [
                'setting_value' => null,
                'setting_type' => 'image',
                'category' => 'pdf',
                'description' => 'PDF logo override',
                'is_active' => true,
            ],
            'pdf_logo_url' => [
                'setting_value' => '',
                'setting_type' => 'text',
                'category' => 'pdf',
                'description' => 'PDF logo URL fallback',
                'is_active' => true,
            ],
            'pdf_accent_color' => [
                'setting_value' => '#9A7B3F',
                'setting_type' => 'color',
                'category' => 'pdf',
                'description' => 'PDF accent color',
                'is_active' => true,
            ],
            'pdf_heading_color' => [
                'setting_value' => '#111827',
                'setting_type' => 'color',
                'category' => 'pdf',
                'description' => 'PDF heading color',
                'is_active' => true,
            ],
            'pdf_body_text_color' => [
                'setting_value' => '#374151',
                'setting_type' => 'color',
                'category' => 'pdf',
                'description' => 'PDF body text color',
                'is_active' => true,
            ],
            'pdf_muted_text_color' => [
                'setting_value' => '#6B7280',
                'setting_type' => 'color',
                'category' => 'pdf',
                'description' => 'PDF muted text color',
                'is_active' => true,
            ],
            'pdf_border_color' => [
                'setting_value' => '#DDE4EE',
                'setting_type' => 'color',
                'category' => 'pdf',
                'description' => 'PDF border color',
                'is_active' => true,
            ],
            'pdf_panel_background' => [
                'setting_value' => '#F8FAFC',
                'setting_type' => 'color',
                'category' => 'pdf',
                'description' => 'PDF panel background',
                'is_active' => true,
            ],
            'pdf_table_header_background' => [
                'setting_value' => '#111827',
                'setting_type' => 'color',
                'category' => 'pdf',
                'description' => 'PDF table header background',
                'is_active' => true,
            ],
            'pdf_table_header_text_color' => [
                'setting_value' => '#FFFFFF',
                'setting_type' => 'color',
                'category' => 'pdf',
                'description' => 'PDF table header text color',
                'is_active' => true,
            ],
            'pdf_table_row_alt_background' => [
                'setting_value' => '#FBFCFE',
                'setting_type' => 'color',
                'category' => 'pdf',
                'description' => 'PDF table alternate row background',
                'is_active' => true,
            ],
            'pdf_brand_mark_background' => [
                'setting_value' => '#111827',
                'setting_type' => 'color',
                'category' => 'pdf',
                'description' => 'PDF brand mark background',
                'is_active' => true,
            ],
            'pdf_brand_mark_text_color' => [
                'setting_value' => '#FFFFFF',
                'setting_type' => 'color',
                'category' => 'pdf',
                'description' => 'PDF brand mark text color',
                'is_active' => true,
            ],
            'pdf_generated_label' => [
                'setting_value' => 'Generated',
                'setting_type' => 'text',
                'category' => 'pdf',
                'description' => 'PDF generated label',
                'is_active' => true,
            ],
            'pdf_not_available_text' => [
                'setting_value' => 'N/A',
                'setting_type' => 'text',
                'category' => 'pdf',
                'description' => 'PDF not available text',
                'is_active' => true,
            ],
            'pdf_admin_work_updates_tag' => [
                'setting_value' => 'Workspace Report',
                'setting_type' => 'text',
                'category' => 'pdf',
                'description' => 'Admin work updates PDF tag',
                'is_active' => true,
            ],
            'pdf_admin_work_updates_title' => [
                'setting_value' => 'Admin Work Updates Report',
                'setting_type' => 'text',
                'category' => 'pdf',
                'description' => 'Admin work updates PDF title',
                'is_active' => true,
            ],
            'pdf_admin_work_updates_subtitle' => [
                'setting_value' => 'Filtered application activity across agents and clients.',
                'setting_type' => 'text',
                'category' => 'pdf',
                'description' => 'Admin work updates PDF subtitle',
                'is_active' => true,
            ],
            'pdf_admin_work_updates_intro_title' => [
                'setting_value' => 'Operational Overview',
                'setting_type' => 'text',
                'category' => 'pdf',
                'description' => 'Admin work updates PDF intro title',
                'is_active' => true,
            ],
            'pdf_admin_work_updates_intro_text' => [
                'setting_value' => 'This export captures the filtered work updates visible to administrators, including assignment ownership, application progress, supporting links, and recorded notes.',
                'setting_type' => 'text',
                'category' => 'pdf',
                'description' => 'Admin work updates PDF intro text',
                'is_active' => true,
            ],
            'pdf_admin_work_updates_register_title' => [
                'setting_value' => 'Work Update Register',
                'setting_type' => 'text',
                'category' => 'pdf',
                'description' => 'Admin work updates PDF register title',
                'is_active' => true,
            ],
            'pdf_admin_work_updates_empty_title' => [
                'setting_value' => 'No work updates found',
                'setting_type' => 'text',
                'category' => 'pdf',
                'description' => 'Admin work updates PDF empty title',
                'is_active' => true,
            ],
            'pdf_admin_work_updates_empty_text' => [
                'setting_value' => 'No filtered work updates were available when this PDF was generated.',
                'setting_type' => 'text',
                'category' => 'pdf',
                'description' => 'Admin work updates PDF empty text',
                'is_active' => true,
            ],
            'pdf_admin_work_updates_footer_note' => [
                'setting_value' => 'Admin work updates export for {record_count} records.',
                'setting_type' => 'text',
                'category' => 'pdf',
                'description' => 'Admin work updates PDF footer note',
                'is_active' => true,
            ],
            'pdf_agent_work_updates_tag' => [
                'setting_value' => 'Agent Export',
                'setting_type' => 'text',
                'category' => 'pdf',
                'description' => 'Agent work updates PDF tag',
                'is_active' => true,
            ],
            'pdf_agent_work_updates_title' => [
                'setting_value' => 'Agent Work Updates Report',
                'setting_type' => 'text',
                'category' => 'pdf',
                'description' => 'Agent work updates PDF title',
                'is_active' => true,
            ],
            'pdf_agent_work_updates_subtitle' => [
                'setting_value' => 'Activity report for {agent_name}.',
                'setting_type' => 'text',
                'category' => 'pdf',
                'description' => 'Agent work updates PDF subtitle',
                'is_active' => true,
            ],
            'pdf_agent_work_updates_intro_title' => [
                'setting_value' => 'Report Summary',
                'setting_type' => 'text',
                'category' => 'pdf',
                'description' => 'Agent work updates PDF intro title',
                'is_active' => true,
            ],
            'pdf_agent_work_updates_intro_text' => [
                'setting_value' => 'This report lists the filtered work updates submitted by {agent_name}, including client ownership, application outcome, and any saved links or notes.',
                'setting_type' => 'text',
                'category' => 'pdf',
                'description' => 'Agent work updates PDF intro text',
                'is_active' => true,
            ],
            'pdf_agent_work_updates_register_title' => [
                'setting_value' => 'Submission Register',
                'setting_type' => 'text',
                'category' => 'pdf',
                'description' => 'Agent work updates PDF register title',
                'is_active' => true,
            ],
            'pdf_agent_work_updates_empty_title' => [
                'setting_value' => 'No work updates found',
                'setting_type' => 'text',
                'category' => 'pdf',
                'description' => 'Agent work updates PDF empty title',
                'is_active' => true,
            ],
            'pdf_agent_work_updates_empty_text' => [
                'setting_value' => 'No filtered work updates were available for this agent when the PDF was generated.',
                'setting_type' => 'text',
                'category' => 'pdf',
                'description' => 'Agent work updates PDF empty text',
                'is_active' => true,
            ],
            'pdf_agent_work_updates_footer_note' => [
                'setting_value' => 'Agent report for {agent_name}.',
                'setting_type' => 'text',
                'category' => 'pdf',
                'description' => 'Agent work updates PDF footer note',
                'is_active' => true,
            ],
            'pdf_client_work_updates_tag' => [
                'setting_value' => 'Client Export',
                'setting_type' => 'text',
                'category' => 'pdf',
                'description' => 'Client work updates PDF tag',
                'is_active' => true,
            ],
            'pdf_client_work_updates_title' => [
                'setting_value' => 'Client Work Updates Report',
                'setting_type' => 'text',
                'category' => 'pdf',
                'description' => 'Client work updates PDF title',
                'is_active' => true,
            ],
            'pdf_client_work_updates_subtitle' => [
                'setting_value' => 'Approved work updates for {client_name}.',
                'setting_type' => 'text',
                'category' => 'pdf',
                'description' => 'Client work updates PDF subtitle',
                'is_active' => true,
            ],
            'pdf_client_work_updates_intro_title' => [
                'setting_value' => 'Client Summary',
                'setting_type' => 'text',
                'category' => 'pdf',
                'description' => 'Client work updates PDF intro title',
                'is_active' => true,
            ],
            'pdf_client_work_updates_intro_text' => [
                'setting_value' => 'This report contains the approved work updates currently available in the client portal, including job details, handling agent, application progress, and any saved references.',
                'setting_type' => 'text',
                'category' => 'pdf',
                'description' => 'Client work updates PDF intro text',
                'is_active' => true,
            ],
            'pdf_client_work_updates_register_title' => [
                'setting_value' => 'Approved Work Updates',
                'setting_type' => 'text',
                'category' => 'pdf',
                'description' => 'Client work updates PDF register title',
                'is_active' => true,
            ],
            'pdf_client_work_updates_empty_title' => [
                'setting_value' => 'No work updates found',
                'setting_type' => 'text',
                'category' => 'pdf',
                'description' => 'Client work updates PDF empty title',
                'is_active' => true,
            ],
            'pdf_client_work_updates_empty_text' => [
                'setting_value' => 'No approved work updates were available for this client when the PDF was generated.',
                'setting_type' => 'text',
                'category' => 'pdf',
                'description' => 'Client work updates PDF empty text',
                'is_active' => true,
            ],
            'pdf_client_work_updates_footer_note' => [
                'setting_value' => 'Client report for {client_name}.',
                'setting_type' => 'text',
                'category' => 'pdf',
                'description' => 'Client work updates PDF footer note',
                'is_active' => true,
            ],
            'pdf_onboarding_tag' => [
                'setting_value' => 'Onboarding Record',
                'setting_type' => 'text',
                'category' => 'pdf',
                'description' => 'Onboarding PDF tag',
                'is_active' => true,
            ],
            'pdf_onboarding_title' => [
                'setting_value' => 'Client Onboarding Submission',
                'setting_type' => 'text',
                'category' => 'pdf',
                'description' => 'Onboarding PDF title',
                'is_active' => true,
            ],
            'pdf_onboarding_subtitle' => [
                'setting_value' => 'Structured intake report for {client_name}.',
                'setting_type' => 'text',
                'category' => 'pdf',
                'description' => 'Onboarding PDF subtitle',
                'is_active' => true,
            ],
            'pdf_onboarding_intro_title' => [
                'setting_value' => 'Submission Overview',
                'setting_type' => 'text',
                'category' => 'pdf',
                'description' => 'Onboarding PDF intro title',
                'is_active' => true,
            ],
            'pdf_onboarding_intro_text' => [
                'setting_value' => 'This report captures the onboarding details submitted by the client in a structured format, matching the internal review style used across the workspace export reports.',
                'setting_type' => 'text',
                'category' => 'pdf',
                'description' => 'Onboarding PDF intro text',
                'is_active' => true,
            ],
            'pdf_onboarding_register_title' => [
                'setting_value' => 'Onboarding Register',
                'setting_type' => 'text',
                'category' => 'pdf',
                'description' => 'Onboarding PDF register title',
                'is_active' => true,
            ],
            'pdf_onboarding_client_note_title' => [
                'setting_value' => 'Client Note',
                'setting_type' => 'text',
                'category' => 'pdf',
                'description' => 'Onboarding PDF client note title',
                'is_active' => true,
            ],
            'pdf_onboarding_empty_title' => [
                'setting_value' => 'No onboarding sections found',
                'setting_type' => 'text',
                'category' => 'pdf',
                'description' => 'Onboarding PDF empty title',
                'is_active' => true,
            ],
            'pdf_onboarding_empty_text' => [
                'setting_value' => 'This submission did not include any structured onboarding sections.',
                'setting_type' => 'text',
                'category' => 'pdf',
                'description' => 'Onboarding PDF empty text',
                'is_active' => true,
            ],
            'pdf_onboarding_footer_note' => [
                'setting_value' => 'Onboarding submission for {client_name}.',
                'setting_type' => 'text',
                'category' => 'pdf',
                'description' => 'Onboarding PDF footer note',
                'is_active' => true,
            ],
            'pdf_onboarding_fallback_section_title' => [
                'setting_value' => 'Additional Details',
                'setting_type' => 'text',
                'category' => 'pdf',
                'description' => 'Onboarding PDF fallback section title',
                'is_active' => true,
            ],
            'pdf_onboarding_default_package_text' => [
                'setting_value' => 'Not provided',
                'setting_type' => 'text',
                'category' => 'pdf',
                'description' => 'Onboarding PDF default package text',
                'is_active' => true,
            ],
            'footer_text' => [
                'setting_value' => '© ' . date('Y') . ' W Automation. All rights reserved.',
                'setting_type' => 'text',
                'category' => 'branding',
                'description' => 'Footer text',
                'is_active' => true,
            ],
            'footer_branding_enabled' => [
                'setting_value' => '1',
                'setting_type' => 'text',
                'category' => 'branding',
                'description' => 'Footer branding enabled',
                'is_active' => true,
            ],
            'footer_branding_show_logo' => [
                'setting_value' => '1',
                'setting_type' => 'text',
                'category' => 'branding',
                'description' => 'Footer branding show logo',
                'is_active' => true,
            ],
            'footer_branding_prefix' => [
                'setting_value' => 'Powered by',
                'setting_type' => 'text',
                'category' => 'branding',
                'description' => 'Footer branding prefix',
                'is_active' => true,
            ],
            'footer_branding_name' => [
                'setting_value' => 'Shopispeed',
                'setting_type' => 'text',
                'category' => 'branding',
                'description' => 'Footer branding name',
                'is_active' => true,
            ],
            'footer_branding_link' => [
                'setting_value' => '',
                'setting_type' => 'text',
                'category' => 'branding',
                'description' => 'Footer branding link',
                'is_active' => true,
            ],
            'footer_branding_logo_url' => [
                'setting_value' => '',
                'setting_type' => 'text',
                'category' => 'branding',
                'description' => 'Footer branding logo URL',
                'is_active' => true,
            ],
            'footer_branding_logo' => [
                'setting_value' => null,
                'setting_type' => 'image',
                'category' => 'branding',
                'description' => 'Footer branding logo',
                'is_active' => true,
            ],
            'footer_policy_links' => [
                'setting_value' => "Privacy Policy|#\nTerms of Service|#\nCookie Policy|#",
                'setting_type' => 'text',
                'category' => 'branding',
                'description' => 'Footer policy links',
                'is_active' => true,
            ],
            'footer_social_links' => [
                'setting_value' => "Facebook|#|fab fa-facebook-f\nInstagram|#|fab fa-instagram\nLinkedIn|#|fab fa-linkedin-in",
                'setting_type' => 'text',
                'category' => 'branding',
                'description' => 'Footer social links',
                'is_active' => true,
            ],
            'auth_panel_caption' => [
                'setting_value' => 'Professional Client Portal',
                'setting_type' => 'text',
                'category' => 'auth',
                'description' => 'Auth panel caption',
                'is_active' => true,
            ],
            'primary_color' => [
                'setting_value' => '#111111',
                'setting_type' => 'color',
                'category' => 'colors',
                'description' => 'Primary color',
                'is_active' => true,
            ],
            'secondary_color' => [
                'setting_value' => '#F7F2E8',
                'setting_type' => 'color',
                'category' => 'colors',
                'description' => 'Secondary color',
                'is_active' => true,
            ],
            'accent_color' => [
                'setting_value' => '#C8A45D',
                'setting_type' => 'color',
                'category' => 'colors',
                'description' => 'Accent color',
                'is_active' => true,
            ],
            'background_color' => [
                'setting_value' => '#FBFAF7',
                'setting_type' => 'color',
                'category' => 'colors',
                'description' => 'Background color',
                'is_active' => true,
            ],
            'text_color' => [
                'setting_value' => '#111111',
                'setting_type' => 'color',
                'category' => 'colors',
                'description' => 'Text color',
                'is_active' => true,
            ],
            'header_color' => [
                'setting_value' => '#FFFFFF',
                'setting_type' => 'color',
                'category' => 'colors',
                'description' => 'Header color',
                'is_active' => true,
            ],
            'sidebar_color' => [
                'setting_value' => '#111111',
                'setting_type' => 'color',
                'category' => 'colors',
                'description' => 'Sidebar color',
                'is_active' => true,
            ],
            'button_color' => [
                'setting_value' => '#111111',
                'setting_type' => 'color',
                'category' => 'colors',
                'description' => 'Button color',
                'is_active' => true,
            ],
            'link_color' => [
                'setting_value' => '#9B7431',
                'setting_type' => 'color',
                'category' => 'colors',
                'description' => 'Link color',
                'is_active' => true,
            ],
            'border_color' => [
                'setting_value' => '#E7DCC5',
                'setting_type' => 'color',
                'category' => 'colors',
                'description' => 'Border color',
                'is_active' => true,
            ],
            'success_color' => [
                'setting_value' => '#2E7D5B',
                'setting_type' => 'color',
                'category' => 'colors',
                'description' => 'Success color',
                'is_active' => true,
            ],
            'warning_color' => [
                'setting_value' => '#C8A45D',
                'setting_type' => 'color',
                'category' => 'colors',
                'description' => 'Warning color',
                'is_active' => true,
            ],
            'error_color' => [
                'setting_value' => '#9D3E32',
                'setting_type' => 'color',
                'category' => 'colors',
                'description' => 'Error color',
                'is_active' => true,
            ],
            'info_color' => [
                'setting_value' => '#8D6A2E',
                'setting_type' => 'color',
                'category' => 'colors',
                'description' => 'Info color',
                'is_active' => true,
            ],
            'danger_color' => [
                'setting_value' => '#9D3E32',
                'setting_type' => 'color',
                'category' => 'colors',
                'description' => 'Danger color',
                'is_active' => true,
            ],
            'primary_dark' => [
                'setting_value' => '#000000',
                'setting_type' => 'color',
                'category' => 'colors',
                'description' => 'Primary dark color',
                'is_active' => true,
            ],
            'secondary_dark' => [
                'setting_value' => '#EEE3CD',
                'setting_type' => 'color',
                'category' => 'colors',
                'description' => 'Secondary dark color',
                'is_active' => true,
            ],
            'success_dark' => [
                'setting_value' => '#215C42',
                'setting_type' => 'color',
                'category' => 'colors',
                'description' => 'Success dark color',
                'is_active' => true,
            ],
            'warning_dark' => [
                'setting_value' => '#A67F34',
                'setting_type' => 'color',
                'category' => 'colors',
                'description' => 'Warning dark color',
                'is_active' => true,
            ],
            'error_dark' => [
                'setting_value' => '#7D2C22',
                'setting_type' => 'color',
                'category' => 'colors',
                'description' => 'Error dark color',
                'is_active' => true,
            ],
            'info_dark' => [
                'setting_value' => '#6D5127',
                'setting_type' => 'color',
                'category' => 'colors',
                'description' => 'Info dark color',
                'is_active' => true,
            ],
            'danger_dark' => [
                'setting_value' => '#7D2C22',
                'setting_type' => 'color',
                'category' => 'colors',
                'description' => 'Danger dark color',
                'is_active' => true,
            ],
            'font_family' => [
                'setting_value' => 'Inter',
                'setting_type' => 'text',
                'category' => 'layout',
                'description' => 'Body font family',
                'is_active' => true,
            ],
            'display_font' => [
                'setting_value' => 'Poppins',
                'setting_type' => 'text',
                'category' => 'layout',
                'description' => 'Display font family',
                'is_active' => true,
            ],
            'font_size' => [
                'setting_value' => '14px',
                'setting_type' => 'text',
                'category' => 'layout',
                'description' => 'Body font size',
                'is_active' => true,
            ],
            'border_radius' => [
                'setting_value' => '20px',
                'setting_type' => 'text',
                'category' => 'layout',
                'description' => 'Border radius',
                'is_active' => true,
            ],
            'box_shadow' => [
                'setting_value' => '0 18px 38px rgba(17, 17, 17, 0.06)',
                'setting_type' => 'text',
                'category' => 'layout',
                'description' => 'Box shadow',
                'is_active' => true,
            ],
            'animation_speed' => [
                'setting_value' => '0.3s',
                'setting_type' => 'text',
                'category' => 'layout',
                'description' => 'Animation speed',
                'is_active' => true,
            ],
            'sidebar_width' => [
                'setting_value' => '18rem',
                'setting_type' => 'text',
                'category' => 'layout',
                'description' => 'Sidebar width',
                'is_active' => true,
            ],
            'header_height' => [
                'setting_value' => '76px',
                'setting_type' => 'text',
                'category' => 'layout',
                'description' => 'Header height',
                'is_active' => true,
            ],
            'onboarding_instructions' => [
                'setting_value' => '',
                'setting_type' => 'text',
                'category' => 'onboarding',
                'description' => 'Onboarding instructions',
                'is_active' => true,
            ],
            'onboarding_guide_file' => [
                'setting_value' => null,
                'setting_type' => 'file',
                'category' => 'onboarding',
                'description' => 'Onboarding guide file',
                'is_active' => true,
            ],
            'client_guide_badge' => [
                'setting_value' => 'Client Guide',
                'setting_type' => 'text',
                'category' => 'client-guide',
                'description' => 'Client guide badge',
                'is_active' => true,
            ],
            'client_guide_title' => [
                'setting_value' => 'A clear step-by-step guide for using your client portal.',
                'setting_type' => 'text',
                'category' => 'client-guide',
                'description' => 'Client guide title',
                'is_active' => true,
            ],
            'client_guide_subtitle' => [
                'setting_value' => 'From registration to daily updates, this page shows clients exactly how to move through onboarding, communication, approvals, payments, and support without confusion.',
                'setting_type' => 'text',
                'category' => 'client-guide',
                'description' => 'Client guide subtitle',
                'is_active' => true,
            ],
            'client_guide_intro_title' => [
                'setting_value' => 'What this page covers',
                'setting_type' => 'text',
                'category' => 'client-guide',
                'description' => 'Client guide intro title',
                'is_active' => true,
            ],
            'client_guide_intro_text' => [
                'setting_value' => 'Your account gives you one place to complete onboarding, follow work progress, respond when your agent requests something, and keep every important update organized in one dashboard.',
                'setting_type' => 'text',
                'category' => 'client-guide',
                'description' => 'Client guide intro text',
                'is_active' => true,
            ],
            'client_guide_support_title' => [
                'setting_value' => 'Best practice for clients',
                'setting_type' => 'text',
                'category' => 'client-guide',
                'description' => 'Client guide support title',
                'is_active' => true,
            ],
            'client_guide_support_text' => [
                'setting_value' => 'Keep your profile details accurate, check the dashboard regularly, and reply quickly to agent requests so your service moves forward without avoidable delays.',
                'setting_type' => 'text',
                'category' => 'client-guide',
                'description' => 'Client guide support text',
                'is_active' => true,
            ],
            'client_guide_primary_label' => [
                'setting_value' => 'Create Client Account',
                'setting_type' => 'text',
                'category' => 'client-guide',
                'description' => 'Client guide primary button label',
                'is_active' => true,
            ],
            'client_guide_primary_link' => [
                'setting_value' => '/register',
                'setting_type' => 'text',
                'category' => 'client-guide',
                'description' => 'Client guide primary button link',
                'is_active' => true,
            ],
            'client_guide_secondary_label' => [
                'setting_value' => 'Contact Us',
                'setting_type' => 'text',
                'category' => 'client-guide',
                'description' => 'Client guide secondary button label',
                'is_active' => true,
            ],
            'client_guide_secondary_link' => [
                'setting_value' => '/contact',
                'setting_type' => 'text',
                'category' => 'client-guide',
                'description' => 'Client guide secondary button link',
                'is_active' => true,
            ],
            'client_guide_step_1_eyebrow' => [
                'setting_value' => 'Start here',
                'setting_type' => 'text',
                'category' => 'client-guide',
                'description' => 'Client guide step 1 eyebrow',
                'is_active' => true,
            ],
            'client_guide_step_1_title' => [
                'setting_value' => 'Create your account and access the client area',
                'setting_type' => 'text',
                'category' => 'client-guide',
                'description' => 'Client guide step 1 title',
                'is_active' => true,
            ],
            'client_guide_step_1_body' => [
                'setting_value' => 'Register with your correct email and contact details. Once your account is active, log in to reach your dashboard, where all client actions, updates, and notices will be centralized.',
                'setting_type' => 'text',
                'category' => 'client-guide',
                'description' => 'Client guide step 1 body',
                'is_active' => true,
            ],
            'client_guide_step_2_eyebrow' => [
                'setting_value' => 'Complete setup',
                'setting_type' => 'text',
                'category' => 'client-guide',
                'description' => 'Client guide step 2 eyebrow',
                'is_active' => true,
            ],
            'client_guide_step_2_title' => [
                'setting_value' => 'Submit onboarding details carefully',
                'setting_type' => 'text',
                'category' => 'client-guide',
                'description' => 'Client guide step 2 title',
                'is_active' => true,
            ],
            'client_guide_step_2_body' => [
                'setting_value' => 'Fill in the onboarding form with accurate personal information, work preferences, resume details, and any files or instructions your team needs before work begins.',
                'setting_type' => 'text',
                'category' => 'client-guide',
                'description' => 'Client guide step 2 body',
                'is_active' => true,
            ],
            'client_guide_step_3_eyebrow' => [
                'setting_value' => 'Stay informed',
                'setting_type' => 'text',
                'category' => 'client-guide',
                'description' => 'Client guide step 3 eyebrow',
                'is_active' => true,
            ],
            'client_guide_step_3_title' => [
                'setting_value' => 'Track work updates and review drafts from your dashboard',
                'setting_type' => 'text',
                'category' => 'client-guide',
                'description' => 'Client guide step 3 title',
                'is_active' => true,
            ],
            'client_guide_step_3_body' => [
                'setting_value' => 'Your assigned agent can share work updates, notes, and draft material inside the system. Review them regularly so you do not miss approvals, edits, or important next actions.',
                'setting_type' => 'text',
                'category' => 'client-guide',
                'description' => 'Client guide step 3 body',
                'is_active' => true,
            ],
            'client_guide_step_4_eyebrow' => [
                'setting_value' => 'When requested',
                'setting_type' => 'text',
                'category' => 'client-guide',
                'description' => 'Client guide step 4 eyebrow',
                'is_active' => true,
            ],
            'client_guide_step_4_title' => [
                'setting_value' => 'Respond to OTP requests, notices, and action items quickly',
                'setting_type' => 'text',
                'category' => 'client-guide',
                'description' => 'Client guide step 4 title',
                'is_active' => true,
            ],
            'client_guide_step_4_body' => [
                'setting_value' => 'If your workflow requires OTP submission, confirmation, or another time-sensitive task, complete it through the dashboard as soon as possible. Fast responses help your service move without interruption.',
                'setting_type' => 'text',
                'category' => 'client-guide',
                'description' => 'Client guide step 4 body',
                'is_active' => true,
            ],
            'client_guide_step_5_eyebrow' => [
                'setting_value' => 'Billing and help',
                'setting_type' => 'text',
                'category' => 'client-guide',
                'description' => 'Client guide step 5 eyebrow',
                'is_active' => true,
            ],
            'client_guide_step_5_title' => [
                'setting_value' => 'Use payment requests and support tickets from the portal',
                'setting_type' => 'text',
                'category' => 'client-guide',
                'description' => 'Client guide step 5 title',
                'is_active' => true,
            ],
            'client_guide_step_5_body' => [
                'setting_value' => 'Whenever a payment is due or you need clarification, use the website features provided for payment handling and support communication so your records stay organized and visible to the team.',
                'setting_type' => 'text',
                'category' => 'client-guide',
                'description' => 'Client guide step 5 body',
                'is_active' => true,
            ],
            'client_guide_step_6_eyebrow' => [
                'setting_value' => 'Keep momentum',
                'setting_type' => 'text',
                'category' => 'client-guide',
                'description' => 'Client guide step 6 eyebrow',
                'is_active' => true,
            ],
            'client_guide_step_6_title' => [
                'setting_value' => 'Check your portal regularly until your service is complete',
                'setting_type' => 'text',
                'category' => 'client-guide',
                'description' => 'Client guide step 6 title',
                'is_active' => true,
            ],
            'client_guide_step_6_body' => [
                'setting_value' => 'The best results come from steady communication. Revisit the client area for notices, progress, pending items, and final deliverables so nothing stalls near the finish line.',
                'setting_type' => 'text',
                'category' => 'client-guide',
                'description' => 'Client guide step 6 body',
                'is_active' => true,
            ],
            'privacy_policy_content' => [
                'setting_value' => <<<'HTML'
<h2>1. Information We Collect</h2>
<p>When you create an account, complete onboarding, contact support, submit OTP details, respond to payment requests, or use the client dashboard, we may collect personal information such as your name, email address, phone number, resume details, work history, target job preferences, onboarding answers, uploaded documents, and communication history.</p>
<p>We also collect service-related records generated through the portal, including work updates, notices, support ticket messages, payment request activity, and account actions required to manage your service.</p>
<h2>2. How We Use Your Information</h2>
<p>We use your information to deliver the client service you signed up for, manage your account, prepare or organize onboarding materials, communicate service updates, process support requests, and keep a record of tasks completed through the website.</p>
<p>Your information may also be used to improve our workflow, keep internal records accurate, prevent misuse of the platform, and comply with legal or operational requirements.</p>
<h2>3. Account and Portal Data</h2>
<p>The client portal is designed to centralize your service activity. Information you provide through onboarding forms, dashboard tools, OTP submissions, support tickets, notices, and payment areas may be stored so our team can continue your workflow efficiently and keep a reliable service record.</p>
<h2>4. Sharing of Information</h2>
<p>We do not sell your personal information. We only share information when it is reasonably necessary for operating the website, supporting your service workflow, processing platform functions, or complying with applicable law.</p>
<h2>5. Data Security</h2>
<p>We take reasonable steps to protect account information and service records, but no online platform can promise absolute security. Clients should use accurate information, protect their login credentials, and notify us if they believe their account has been accessed improperly.</p>
<h2>6. Data Accuracy</h2>
<p>Clients are responsible for providing accurate, current, and complete information during registration, onboarding, and ongoing communication. Inaccurate or incomplete details may affect the quality, speed, or outcome of the service.</p>
<h2>7. Contact</h2>
<p>If you have questions about how your information is handled, please contact us through the website contact page or the support tools available in your client dashboard.</p>
HTML,
                'setting_type' => 'html',
                'category' => 'policies',
                'description' => 'Privacy policy page content',
                'is_active' => true,
            ],
            'privacy_policy_title' => [
                'setting_value' => 'Privacy Policy',
                'setting_type' => 'text',
                'category' => 'policies',
                'description' => 'Privacy policy page title',
                'is_active' => true,
            ],
            'privacy_policy_subtitle' => [
                'setting_value' => 'How we collect, use, store, and protect the information shared through your client portal and service workflow.',
                'setting_type' => 'text',
                'category' => 'policies',
                'description' => 'Privacy policy page subtitle',
                'is_active' => true,
            ],
            'privacy_policy_meta_text' => [
                'setting_value' => 'Last updated {date}',
                'setting_type' => 'text',
                'category' => 'policies',
                'description' => 'Privacy policy page meta text',
                'is_active' => true,
            ],
            'terms_of_service_content' => [
                'setting_value' => <<<'HTML'
<h2>1. Acceptance of Terms</h2>
<p>By accessing this website, creating an account, submitting onboarding information, or using any client dashboard features, you agree to these terms of service and to any additional policies published on the website.</p>
<h2>2. Nature of the Service</h2>
<p>Our website supports a remote-career and client-service workflow that may include onboarding, dashboard communication, work updates, support tickets, OTP handling, notices, and payment-related actions. The service is intended to organize and manage client support through a single portal.</p>
<h2>3. Client Responsibilities</h2>
<p>You agree to provide accurate information, maintain the confidentiality of your account credentials, respond promptly when your assigned team requests time-sensitive information, and use the website lawfully and respectfully.</p>
<p>Delays caused by missing onboarding details, incomplete documents, late OTP submissions, or inaccurate information may affect service progress.</p>
<h2>4. No Guaranteed Job Outcome</h2>
<p>Our team works on your behalf for the time or service period you selected, but no part of the website or service should be understood as a guarantee of a job offer, interview, hire, or employment result.</p>
<h2>5. Dashboard Records</h2>
<p>Work updates, notices, support messages, payment request activity, and related portal records are maintained for operational and communication purposes. You agree that these records may be used to manage and verify service activity inside the platform.</p>
<h2>6. Payments</h2>
<p>Any fees, requests for payment, or billing notices shown in the client area are part of the service workflow. Clients are responsible for reviewing payment information carefully and using the dashboard tools provided to respond or confirm payment status.</p>
<h2>7. Platform Availability</h2>
<p>We may update, improve, suspend, or modify website features at any time when reasonably necessary for maintenance, security, operational improvements, or service delivery.</p>
<h2>8. Limitation of Use</h2>
<p>You may not misuse the website, attempt unauthorized access, interfere with the platform, upload harmful content, or use the portal in a way that disrupts service operations for other users.</p>
<h2>9. Contact and Support</h2>
<p>Questions about your service should be directed through the contact page or your support ticket area so communication remains attached to your account and easier to track.</p>
HTML,
                'setting_type' => 'html',
                'category' => 'policies',
                'description' => 'Terms of service page content',
                'is_active' => true,
            ],
            'terms_of_service_title' => [
                'setting_value' => 'Terms of Service',
                'setting_type' => 'text',
                'category' => 'policies',
                'description' => 'Terms of service page title',
                'is_active' => true,
            ],
            'terms_of_service_subtitle' => [
                'setting_value' => 'The service terms that apply when you use this website, create an account, or purchase client support from our team.',
                'setting_type' => 'text',
                'category' => 'policies',
                'description' => 'Terms of service page subtitle',
                'is_active' => true,
            ],
            'terms_of_service_meta_text' => [
                'setting_value' => 'Last updated {date}',
                'setting_type' => 'text',
                'category' => 'policies',
                'description' => 'Terms of service page meta text',
                'is_active' => true,
            ],
            'booking_policy_content' => [
                'setting_value' => <<<'HTML'
<p>This Booking Policy outlines how services are reserved, scheduled, activated, and delivered across our platforms. By purchasing any service, you agree to this policy, along with our Terms of Service and Refund Policy.</p>
<h2>1. Ownership &amp; Platform Structure</h2>
<p>All services are owned and operated by Cali and her team.</p>
<p><strong>Booking &amp; payment platform:</strong> <a href="https://www.atswfhresumes.com" target="_blank" rel="noopener noreferrer">https://www.atswfhresumes.com</a><br><strong>Service platform:</strong> <a href="https://crresumes.com" target="_blank" rel="noopener noreferrer">https://crresumes.com</a></p>
<p>Both platforms operate under the same ownership and function as a unified system. Your booking, onboarding, and service delivery are all part of one continuous service process.</p>
<h2>2. Booking &amp; Reservation of Service Slot</h2>
<p>When you purchase a service, you are reserving a limited service slot, not purchasing immediate service delivery.</p>
<p>CR Resumes operates on a restricted-capacity model, meaning:</p>
<ul>
<li>Only a limited number of clients are actively serviced at any given time</li>
<li>New clients are placed into a service queue upon booking</li>
</ul>
<p><strong>Important:</strong> Booking does not mean your service starts immediately.</p>
<h2>3. Required Account Setup &amp; Onboarding</h2>
<p>To begin your service, you must:</p>
<ul>
<li>Create an account on <a href="https://crresumes.com" target="_blank" rel="noopener noreferrer">https://crresumes.com</a></li>
<li>Complete the onboarding form within your account</li>
<li>Upload your current resume</li>
</ul>
<p>This allows Cali and her team to prepare your materials, optimize your resume, and build your job application strategy.</p>
<p><strong>Work will not begin until all onboarding steps are fully completed.</strong></p>
<h2>4. Service Start Date (Critical)</h2>
<p>Your official service start date is:</p>
<p><strong>The date when active work begins on your resume, cover letter, or job application preparation.</strong></p>
<p>It is not:</p>
<ul>
<li>The date of purchase</li>
<li>The date you enter the queue</li>
<li>The date onboarding is submitted</li>
</ul>
<p>You will be notified when your service officially starts.</p>
<h2>5. Preparation Phase (Pre-Activation)</h2>
<p>Before your active service begins, Cali and her team will complete a preparation phase, which may include:</p>
<ul>
<li>Resume review and optimization</li>
<li>Cover letter creation</li>
<li>Job targeting and strategy setup</li>
<li>Internal system and account preparation</li>
</ul>
<p>This phase is necessary to ensure quality service delivery.</p>
<p><strong>This preparation phase is part of the service process and does not count toward your purchased service duration.</strong></p>
<h2>6. Service Duration &amp; Activation</h2>
<p>For plans with defined durations (2 weeks, 3 weeks, 4 weeks, or 6 weeks):</p>
<ul>
<li>Your service duration begins only when you start receiving daily job updates and application activity</li>
<li>This marks the start of your active job hunting phase</li>
</ul>
<p><strong>Important:</strong></p>
<ul>
<li>You will be notified when this phase begins</li>
<li>The countdown of your selected plan starts only from that date</li>
<li>Any delay in onboarding or communication may delay your activation date</li>
</ul>
<h2>7. Turnaround &amp; Timeline</h2>
<p>Due to the queue-based system:</p>
<ul>
<li>Services do not follow fixed timelines from the date of purchase</li>
<li>Timing depends on queue position, onboarding completion, and demand</li>
</ul>
<p>Delays caused by incomplete onboarding, missing information, or slow client responses will extend your timeline and are not the responsibility of CR Resumes.</p>
<h2>8. Client Responsibilities</h2>
<p>By booking, you agree to:</p>
<ul>
<li>Provide accurate, complete, and truthful information</li>
<li>Complete onboarding promptly</li>
<li>Respond to communication requests when needed</li>
<li>Provide OTPs or verification codes if required for job applications</li>
</ul>
<p>Failure to meet these responsibilities may delay or affect your service.</p>
<h2>9. Nature of Service</h2>
<p>CR Resumes provides:</p>
<ul>
<li>Resume writing and optimization</li>
<li>Cover letter creation</li>
<li>Job application assistance</li>
</ul>
<p>All services are performed on a best-effort basis.</p>
<h2>10. No Guarantee of Results</h2>
<p>By booking, you acknowledge:</p>
<ul>
<li>No job placement is guaranteed</li>
<li>No interviews are guaranteed</li>
<li>No employment outcomes are guaranteed</li>
</ul>
<p>Results depend on external factors beyond the control of Cali and her team.</p>
<h2>11. Cancellations</h2>
<p>All bookings are final. Cancellation is not permitted once a service slot has been reserved.</p>
<h2>12. Refund Policy (Strict)</h2>
<p>All purchases are non-refundable.</p>
<p>The only exception is if your service does not begin within forty-five (45) business days from the date of booking.</p>
<p><strong>Service start</strong> means active work has begun. Being in the queue does not count as service start.</p>
<p>All refund decisions are subject to review and remain at our sole discretion.</p>
<p>For full details: <a href="https://crresumes.com/refund-policy" target="_blank" rel="noopener noreferrer">https://crresumes.com/refund-policy</a></p>
<h2>13. Work in Progress</h2>
<p>Once any work has begun, including:</p>
<ul>
<li>Resume review or editing</li>
<li>Cover letter preparation</li>
<li>Account setup or internal preparation</li>
<li>Job application activity</li>
</ul>
<p>The service is considered in progress and non-reversible, and no refunds will be issued.</p>
<h2>14. Communication &amp; Support</h2>
<p>All communication must be conducted through official channels:</p>
<ul>
<li>Submit questions and requests via the support ticket system in your account on <a href="https://crresumes.com" target="_blank" rel="noopener noreferrer">https://crresumes.com</a></li>
<li>Email support: <a href="mailto:caliwfh@outlook.com">caliwfh@outlook.com</a></li>
</ul>
<p>Clients are responsible for checking their account regularly and responding to messages promptly. Failure to communicate through official channels may result in delays.</p>
<h2>15. Payment Disputes &amp; Chargebacks</h2>
<p>By completing a purchase, you agree:</p>
<ul>
<li>Not to initiate chargebacks or disputes without contacting us first</li>
<li>That this policy, along with our Terms and Refund Policy, may be used as evidence in dispute resolution</li>
</ul>
<p>We reserve the right to suspend or terminate your service and restrict future access.</p>
<h2>16. Limitation of Liability</h2>
<p>To the fullest extent permitted by law, Cali and her team are not liable for:</p>
<ul>
<li>Job rejections</li>
<li>Missed opportunities</li>
<li>Employer decisions</li>
<li>Any indirect or consequential damages</li>
</ul>
<h2>17. Agreement to Policies</h2>
<p>By booking through <a href="https://www.atswfhresumes.com" target="_blank" rel="noopener noreferrer">https://www.atswfhresumes.com</a>, you confirm that you have read and agreed to:</p>
<ul>
<li><a href="https://crresumes.com/terms-of-service" target="_blank" rel="noopener noreferrer">Terms of Service</a></li>
<li><a href="https://crresumes.com/refund-policy" target="_blank" rel="noopener noreferrer">Refund Policy</a></li>
<li>This Booking Policy</li>
</ul>
<h2>18. Contact Information</h2>
<p>For all inquiries and support: <a href="mailto:caliwfh@outlook.com">caliwfh@outlook.com</a></p>
<h2>19. Policy Updates &amp; Modifications</h2>
<p>Cali and her team reserve the right to update, modify, or change this Booking Policy at any time without prior notice.</p>
<p>Any updates will be reflected by the "Last Updated" displayed at the top of this page.</p>
<p>By continuing to use our services after any changes are made, you agree to be bound by the updated policy.</p>
HTML,
                'setting_type' => 'html',
                'category' => 'policies',
                'description' => 'Booking policy page content',
                'is_active' => true,
            ],
            'booking_policy_title' => [
                'setting_value' => 'Booking Policy',
                'setting_type' => 'text',
                'category' => 'policies',
                'description' => 'Booking policy page title',
                'is_active' => true,
            ],
            'booking_policy_subtitle' => [
                'setting_value' => 'This Booking Policy outlines how services are reserved, scheduled, activated, and delivered across our platforms. By purchasing any service, you agree to this policy, along with our Terms of Service and Refund Policy.',
                'setting_type' => 'text',
                'category' => 'policies',
                'description' => 'Booking policy page subtitle',
                'is_active' => true,
            ],
            'booking_policy_meta_text' => [
                'setting_value' => 'Last updated December 30, 2025',
                'setting_type' => 'text',
                'category' => 'policies',
                'description' => 'Booking policy page meta text',
                'is_active' => true,
            ],
            'refund_policy_content' => [
                'setting_value' => <<<'HTML'
<h2>1. General Refund Position</h2>
<p>Our service is provided on a work-performed and time-allotted basis. Once your service has started, the fees paid for that service are generally non-refundable.</p>
<h2>2. Non-Refundable Service Notice</h2>
<p><strong>THIS IS A NON-REFUNDABLE SERVICE. OUR TEAM WILL WORK ON YOUR BEHALF FOR THE ALLOTTED TIME YOU SIGNED UP FOR TO THE BEST OF OUR ABILITY. THIS IS NOT A GUARANTEED JOB POSITION.</strong></p>
<h2>3. Why Refunds Are Limited</h2>
<p>Our team begins work through onboarding review, account setup, planning, communication, dashboard management, and other service activities soon after the process starts. Because this work is time-based and operational in nature, it cannot usually be reversed or recovered once delivered.</p>
<h2>4. Client Responsibility</h2>
<p>Clients are responsible for reviewing service details before purchasing, providing accurate information, and using the dashboard and communication tools responsibly. Failure to respond to requests, delays in providing required details, or changes in personal circumstances do not create an automatic refund obligation.</p>
<h2>5. Exceptional Review</h2>
<p>If you believe a billing issue occurred in error, you may contact support through the portal or contact page. Any review of the matter will be handled case by case, but no statement on this page should be interpreted as a promise of a refund.</p>
<h2>6. Policy Updates</h2>
<p>We may revise this refund policy when needed to reflect operational, legal, or service changes. The current version published on this website will apply going forward.</p>
HTML,
                'setting_type' => 'html',
                'category' => 'policies',
                'description' => 'Refund policy page content',
                'is_active' => true,
            ],
            'refund_policy_title' => [
                'setting_value' => 'Refund Policy',
                'setting_type' => 'text',
                'category' => 'policies',
                'description' => 'Refund policy page title',
                'is_active' => true,
            ],
            'refund_policy_subtitle' => [
                'setting_value' => 'Clear payment and refund terms for clients using our portal, onboarding process, and remote-career support service.',
                'setting_type' => 'text',
                'category' => 'policies',
                'description' => 'Refund policy page subtitle',
                'is_active' => true,
            ],
            'refund_policy_meta_text' => [
                'setting_value' => 'Last updated {date}',
                'setting_type' => 'text',
                'category' => 'policies',
                'description' => 'Refund policy page meta text',
                'is_active' => true,
            ],
            'welcome_badge' => [
                'setting_value' => 'Tailored Remote Career Support',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Welcome page badge',
                'is_active' => true,
            ],
            'welcome_title' => [
                'setting_value' => 'Get hired faster with a sharper remote-work application package.',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Welcome title',
                'is_active' => true,
            ],
            'welcome_subtitle' => [
                'setting_value' => 'Landing a legitimate work-from-home role is competitive. We refine your resume, positioning, and preparation so your application reads as polished, credible, and ready for serious employers.',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Welcome subtitle',
                'is_active' => true,
            ],
            'welcome_points' => [
                'setting_value' => implode("\n", [
                    'Professionally written, ATS-optimized resume',
                    'Custom cover letter tailored to remote positions',
                    'Job-specific keyword optimization',
                    'Interview prep cheat sheets and guidance',
                    '1-on-1 expert support with limited client intake',
                    'Proven strategies used by thousands of remote job seekers',
                ]),
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Welcome points',
                'is_active' => true,
            ],
            'welcome_primary_label' => [
                'setting_value' => 'Secure Your Spot',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Welcome primary button label',
                'is_active' => true,
            ],
            'welcome_primary_enabled' => [
                'setting_value' => '1',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Welcome primary button enabled',
                'is_active' => true,
            ],
            'welcome_primary_link' => [
                'setting_value' => '/register',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Welcome primary button link',
                'is_active' => true,
            ],
            'public_header_register_label' => [
                'setting_value' => 'Secure Your Spot',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Public header register button label',
                'is_active' => true,
            ],
            'public_header_register_enabled' => [
                'setting_value' => '1',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Public header register button enabled',
                'is_active' => true,
            ],
            'public_header_register_link' => [
                'setting_value' => '/register',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Public header register button link',
                'is_active' => true,
            ],
            'welcome_secondary_label' => [
                'setting_value' => 'Login',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Welcome secondary button label',
                'is_active' => true,
            ],
            'welcome_secondary_enabled' => [
                'setting_value' => '1',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Welcome secondary button enabled',
                'is_active' => true,
            ],
            'welcome_secondary_link' => [
                'setting_value' => '/login',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Welcome secondary button link',
                'is_active' => true,
            ],
            'welcome_services_button_label' => [
                'setting_value' => 'View All Services',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Welcome services button label',
                'is_active' => true,
            ],
            'welcome_services_button_link' => [
                'setting_value' => '#',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Welcome services button link',
                'is_active' => true,
            ],
            'welcome_banner_enabled' => [
                'setting_value' => '1',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Welcome banner enabled',
                'is_active' => true,
            ],
            'welcome_banner_mode' => [
                'setting_value' => 'image_text',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Welcome banner mode',
                'is_active' => true,
            ],
            'welcome_banner_image_url' => [
                'setting_value' => '',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Welcome banner image URL',
                'is_active' => true,
            ],
            'welcome_banner_mobile_image_url' => [
                'setting_value' => '',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Welcome mobile banner image URL',
                'is_active' => true,
            ],
            'welcome_banner_image' => [
                'setting_value' => null,
                'setting_type' => 'image',
                'category' => 'landing',
                'description' => 'Welcome banner image',
                'is_active' => true,
            ],
            'welcome_banner_mobile_image' => [
                'setting_value' => null,
                'setting_type' => 'image',
                'category' => 'landing',
                'description' => 'Welcome mobile banner image',
                'is_active' => true,
            ],
            'welcome_text_section_enabled' => [
                'setting_value' => '1',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Welcome text section enabled',
                'is_active' => true,
            ],
            'welcome_text_section_rich_text' => [
                'setting_value' => '<strong>Professional support for serious remote job seekers.</strong>',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Welcome text section rich text',
                'is_active' => true,
            ],
            'welcome_text_section_subtext' => [
                'setting_value' => 'Use this section to highlight a clear promise, a short brand message, or an important callout between your services and client reviews.',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Welcome text section subtext',
                'is_active' => true,
            ],
            'welcome_text_section_button_label' => [
                'setting_value' => 'Get Started',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Welcome text section button label',
                'is_active' => true,
            ],
            'welcome_text_section_button_link' => [
                'setting_value' => '/register',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Welcome text section button link',
                'is_active' => true,
            ],
            'welcome_announcement_enabled' => [
                'setting_value' => '1',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Welcome announcement enabled',
                'is_active' => true,
            ],
            'welcome_announcement_text' => [
                'setting_value' => 'Now booking new clients this week. Limited availability.',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Welcome announcement text',
                'is_active' => true,
            ],
            'welcome_announcement_link' => [
                'setting_value' => '/register',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Welcome announcement link',
                'is_active' => true,
            ],
            'welcome_announcement_bg_color' => [
                'setting_value' => '#111111',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Welcome announcement background color',
                'is_active' => true,
            ],
            'welcome_announcement_text_color' => [
                'setting_value' => '#F7F2E8',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Welcome announcement text color',
                'is_active' => true,
            ],
            'welcome_announcement_speed' => [
                'setting_value' => '20',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Welcome announcement speed in seconds',
                'is_active' => true,
            ],
            'welcome_popup_enabled' => [
                'setting_value' => '1',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Welcome sales popup enabled',
                'is_active' => true,
            ],
            'welcome_popup_delay' => [
                'setting_value' => '1',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Welcome sales popup delay in seconds',
                'is_active' => true,
            ],
            'welcome_popup_badge' => [
                'setting_value' => 'Limited Time Offer',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Welcome sales popup badge',
                'is_active' => true,
            ],
            'welcome_popup_title' => [
                'setting_value' => 'Get hired faster with our WFH full service package',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Welcome sales popup title',
                'is_active' => true,
            ],
            'welcome_popup_message' => [
                'setting_value' => 'Lock your slot now and get a premium resume, tailored support, and faster interview momentum.',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Welcome sales popup message',
                'is_active' => true,
            ],
            'welcome_popup_price' => [
                'setting_value' => 'From $275',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Welcome sales popup price text',
                'is_active' => true,
            ],
            'welcome_popup_button_label' => [
                'setting_value' => 'Book Now',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Welcome sales popup button label',
                'is_active' => true,
            ],
            'welcome_popup_button_link' => [
                'setting_value' => '#',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Welcome sales popup button link',
                'is_active' => true,
            ],
            'welcome_popup_image_url' => [
                'setting_value' => '',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Welcome sales popup image URL',
                'is_active' => true,
            ],
            'welcome_popup_image' => [
                'setting_value' => null,
                'setting_type' => 'image',
                'category' => 'landing',
                'description' => 'Welcome sales popup uploaded image',
                'is_active' => true,
            ],
            'welcome_popup_bg_color' => [
                'setting_value' => '#111111',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Welcome sales popup background color',
                'is_active' => true,
            ],
            'welcome_popup_text_color' => [
                'setting_value' => '#FFFFFF',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Welcome sales popup text color',
                'is_active' => true,
            ],
            'welcome_popup_accent_color' => [
                'setting_value' => '#C8A45D',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Welcome sales popup accent color',
                'is_active' => true,
            ],
            'welcome_side_title' => [
                'setting_value' => 'Why Clients Choose Us',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Welcome side panel title',
                'is_active' => true,
            ],
            'welcome_timeline_label' => [
                'setting_value' => 'Guided timeline',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Welcome timeline label',
                'is_active' => true,
            ],
            'welcome_timeline' => [
                'setting_value' => 'Choose from structured 2-week, 3-week, or 6-week packages designed to improve interview volume and conversion.',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Welcome timeline note',
                'is_active' => true,
            ],
            'welcome_availability_label' => [
                'setting_value' => 'Selective intake',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Welcome availability label',
                'is_active' => true,
            ],
            'welcome_availability' => [
                'setting_value' => 'Limited availability: we only onboard a small number of clients at a time to keep the work personal and precise.',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Welcome availability note',
                'is_active' => true,
            ],
            'welcome_quality_label' => [
                'setting_value' => 'Professional polish',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Welcome quality label',
                'is_active' => true,
            ],
            'welcome_quality_text' => [
                'setting_value' => 'Every deliverable is built to feel credible, concise, and ready for serious hiring teams.',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Welcome quality note',
                'is_active' => true,
            ],
            'welcome_stat_one_value' => [
                'setting_value' => 'ATS',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Welcome stat one value',
                'is_active' => true,
            ],
            'welcome_stat_one_label' => [
                'setting_value' => 'Aligned',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Welcome stat one label',
                'is_active' => true,
            ],
            'welcome_stat_two_value' => [
                'setting_value' => '1:1',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Welcome stat two value',
                'is_active' => true,
            ],
            'welcome_stat_two_label' => [
                'setting_value' => 'Support',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Welcome stat two label',
                'is_active' => true,
            ],
            'welcome_stat_three_value' => [
                'setting_value' => 'WFH',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Welcome stat three value',
                'is_active' => true,
            ],
            'welcome_stat_three_label' => [
                'setting_value' => 'Focused',
                'setting_type' => 'text',
                'category' => 'landing',
                'description' => 'Welcome stat three label',
                'is_active' => true,
            ],
            'custom_css' => [
                'setting_value' => null,
                'setting_type' => 'text',
                'category' => 'custom-code',
                'description' => 'Custom CSS',
                'is_active' => true,
            ],
            'custom_js' => [
                'setting_value' => null,
                'setting_type' => 'text',
                'category' => 'custom-code',
                'description' => 'Custom JavaScript',
                'is_active' => true,
            ],
        ];
    }
}
