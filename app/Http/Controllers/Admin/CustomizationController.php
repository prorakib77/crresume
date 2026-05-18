<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomizationSetting;
use App\Support\PdfTemplateCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CustomizationController extends Controller
{
    /**
     * Available customization sections.
     */
    private function sectionMap(): array
    {
        return [
            'identity' => [
                'label' => 'Identity',
                'icon' => 'fas fa-fingerprint',
                'description' => 'Site name, branding text, and logo assets.',
            ],
            'footer' => [
                'label' => 'Footer',
                'icon' => 'fas fa-shoe-prints',
                'description' => 'Policy links, social icons, and copyright text.',
            ],
            'theme' => [
                'label' => 'Theme Colors',
                'icon' => 'fas fa-swatchbook',
                'description' => 'Core palette for all dashboard and public pages.',
            ],
            'layout' => [
                'label' => 'Layout & Fonts',
                'icon' => 'fas fa-text-height',
                'description' => 'Typography, sizing, spacing, and panel dimensions.',
            ],
            'welcome' => [
                'label' => 'Welcome Content',
                'icon' => 'fas fa-home',
                'description' => 'Hero copy, buttons, and metrics for landing page.',
            ],
            'announcement' => [
                'label' => 'Announcement Bar',
                'icon' => 'fas fa-bullhorn',
                'description' => 'Scrolling top bar message on the welcome page.',
            ],
            'popup' => [
                'label' => 'Popup',
                'icon' => 'fas fa-window-maximize',
                'description' => 'Load-time sales popup content, color, and CTA.',
            ],
            'email' => [
                'label' => 'Email',
                'icon' => 'fas fa-envelope-open-text',
                'description' => 'Global email branding header, logo, and accent styling.',
            ],
            'pdf' => [
                'label' => 'PDF Customizer',
                'icon' => 'fas fa-file-pdf',
                'description' => 'Global PDF branding, colors, and export footer content.',
            ],
            'onboarding' => [
                'label' => 'Onboarding',
                'icon' => 'fas fa-file-lines',
                'description' => 'Client onboarding instructions and guide file.',
            ],
            'client-guide' => [
                'label' => 'Client Guide',
                'icon' => 'fas fa-route',
                'description' => 'Public step-by-step guide content for clients.',
            ],
            'policies' => [
                'label' => 'Policies',
                'icon' => 'fas fa-scale-balanced',
                'description' => 'Rich text policy pages for privacy, terms, booking, and refunds.',
            ],
            'code' => [
                'label' => 'Custom Code',
                'icon' => 'fas fa-code',
                'description' => 'Custom CSS and JavaScript snippets.',
            ],
        ];
    }

    /**
     * Resolve section key and fallback to identity.
     */
    private function normalizeSection(?string $section): string
    {
        $key = strtolower(trim((string) $section));

        return array_key_exists($key, $this->sectionMap()) ? $key : 'identity';
    }

    /**
     * Display the customization settings page.
     */
    public function index()
    {
        return redirect()->route('admin.customization.section', ['section' => 'identity']);
    }

    /**
     * Display a single customization section.
     */
    public function section(string $section)
    {
        CustomizationSetting::syncDefaults();

        $settings = CustomizationSetting::getAllActive();
        $bodyFontOptions = CustomizationSetting::bodyFontOptions();
        $displayFontOptions = CustomizationSetting::displayFontOptions();
        $sections = $this->sectionMap();
        $activeSection = $this->normalizeSection($section);
        $pdfTemplates = PdfTemplateCatalog::ordered();

        return view('admin.customization.section', compact('settings', 'bodyFontOptions', 'displayFontOptions', 'sections', 'activeSection', 'pdfTemplates'));
    }

    /**
     * Update customization settings.
     */
    public function update(Request $request, ?string $section = null)
    {
        $colorRule = ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'];
        $sizeRule = ['nullable', 'regex:/^\\d+(\\.\\d+)?(px|rem|em)$/'];
        $widthRule = ['nullable', 'regex:/^\\d+(\\.\\d+)?(px|rem|%)$/'];
        $pdfTemplateValidation = [];

        foreach (PdfTemplateCatalog::ordered() as $editor) {
            foreach ($editor['fields'] as $field) {
                $pdfTemplateValidation[$field['key']] = 'nullable|string|max:' . ($field['max'] ?? 1200);
            }
        }

        $validationRules = [
            'site_name' => 'nullable|string|max:255',
            'site_tagline' => 'nullable|string|max:255',
            'auth_panel_caption' => 'nullable|string|max:255',
            'site_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'site_favicon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,ico,webp|max:1024',
            'footer_text' => 'nullable|string|max:500',
            'footer_branding_enabled' => 'nullable|in:0,1',
            'footer_branding_show_logo' => 'nullable|in:0,1',
            'footer_branding_prefix' => 'nullable|string|max:80',
            'footer_branding_name' => 'nullable|string|max:120',
            'footer_branding_link' => 'nullable|string|max:255',
            'footer_branding_logo_url' => 'nullable|string|max:255',
            'footer_branding_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'footer_policy_links' => 'nullable|string|max:3000',
            'footer_social_links' => 'nullable|string|max:3000',

            'primary_color' => $colorRule,
            'secondary_color' => $colorRule,
            'accent_color' => $colorRule,
            'background_color' => $colorRule,
            'text_color' => $colorRule,
            'header_color' => $colorRule,
            'sidebar_color' => $colorRule,
            'button_color' => $colorRule,
            'link_color' => $colorRule,
            'border_color' => $colorRule,
            'success_color' => $colorRule,
            'warning_color' => $colorRule,
            'error_color' => $colorRule,
            'info_color' => $colorRule,
            'danger_color' => $colorRule,
            'primary_dark' => $colorRule,
            'secondary_dark' => $colorRule,
            'success_dark' => $colorRule,
            'warning_dark' => $colorRule,
            'error_dark' => $colorRule,
            'info_dark' => $colorRule,
            'danger_dark' => $colorRule,

            'font_family' => ['nullable', 'string', 'max:255', Rule::in(CustomizationSetting::bodyFontOptions())],
            'display_font' => ['nullable', 'string', 'max:255', Rule::in(CustomizationSetting::displayFontOptions())],
            'font_size' => $sizeRule,
            'border_radius' => $sizeRule,
            'box_shadow' => 'nullable|string|max:120',
            'animation_speed' => ['nullable', 'regex:/^\\d+(\\.\\d+)?s$/'],
            'sidebar_width' => $widthRule,
            'header_height' => $widthRule,

            'onboarding_instructions' => 'nullable|string|max:8000',
            'onboarding_guide_file' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'pdf_brand_name' => 'nullable|string|max:255',
            'pdf_footer_note' => 'nullable|string|max:500',
            'pdf_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4096',
            'pdf_logo_url' => 'nullable|string|max:255',
            'pdf_accent_color' => $colorRule,
            'pdf_heading_color' => $colorRule,
            'pdf_body_text_color' => $colorRule,
            'pdf_muted_text_color' => $colorRule,
            'pdf_border_color' => $colorRule,
            'pdf_panel_background' => $colorRule,
            'pdf_table_header_background' => $colorRule,
            'pdf_table_header_text_color' => $colorRule,
            'pdf_table_row_alt_background' => $colorRule,
            'pdf_brand_mark_background' => $colorRule,
            'pdf_brand_mark_text_color' => $colorRule,
            'pdf_generated_label' => 'nullable|string|max:120',
            'pdf_not_available_text' => 'nullable|string|max:120',
            'client_guide_badge' => 'nullable|string|max:120',
            'client_guide_title' => 'nullable|string|max:255',
            'client_guide_subtitle' => 'nullable|string|max:1200',
            'client_guide_intro_title' => 'nullable|string|max:140',
            'client_guide_intro_text' => 'nullable|string|max:1400',
            'client_guide_support_title' => 'nullable|string|max:140',
            'client_guide_support_text' => 'nullable|string|max:1400',
            'client_guide_primary_label' => 'nullable|string|max:100',
            'client_guide_primary_link' => 'nullable|string|max:255',
            'client_guide_secondary_label' => 'nullable|string|max:100',
            'client_guide_secondary_link' => 'nullable|string|max:255',
            'client_guide_step_1_eyebrow' => 'nullable|string|max:100',
            'client_guide_step_1_title' => 'nullable|string|max:160',
            'client_guide_step_1_body' => 'nullable|string|max:1400',
            'client_guide_step_2_eyebrow' => 'nullable|string|max:100',
            'client_guide_step_2_title' => 'nullable|string|max:160',
            'client_guide_step_2_body' => 'nullable|string|max:1400',
            'client_guide_step_3_eyebrow' => 'nullable|string|max:100',
            'client_guide_step_3_title' => 'nullable|string|max:160',
            'client_guide_step_3_body' => 'nullable|string|max:1400',
            'client_guide_step_4_eyebrow' => 'nullable|string|max:100',
            'client_guide_step_4_title' => 'nullable|string|max:160',
            'client_guide_step_4_body' => 'nullable|string|max:1400',
            'client_guide_step_5_eyebrow' => 'nullable|string|max:100',
            'client_guide_step_5_title' => 'nullable|string|max:160',
            'client_guide_step_5_body' => 'nullable|string|max:1400',
            'client_guide_step_6_eyebrow' => 'nullable|string|max:100',
            'client_guide_step_6_title' => 'nullable|string|max:160',
            'client_guide_step_6_body' => 'nullable|string|max:1400',
            'privacy_policy_content' => 'nullable|string|max:20000',
            'terms_of_service_content' => 'nullable|string|max:20000',
            'booking_policy_content' => 'nullable|string|max:30000',
            'refund_policy_content' => 'nullable|string|max:20000',
            'privacy_policy_title' => 'nullable|string|max:255',
            'privacy_policy_subtitle' => 'nullable|string|max:1200',
            'privacy_policy_meta_text' => 'nullable|string|max:255',
            'terms_of_service_title' => 'nullable|string|max:255',
            'terms_of_service_subtitle' => 'nullable|string|max:1200',
            'terms_of_service_meta_text' => 'nullable|string|max:255',
            'booking_policy_title' => 'nullable|string|max:255',
            'booking_policy_subtitle' => 'nullable|string|max:2000',
            'booking_policy_meta_text' => 'nullable|string|max:255',
            'refund_policy_title' => 'nullable|string|max:255',
            'refund_policy_subtitle' => 'nullable|string|max:1200',
            'refund_policy_meta_text' => 'nullable|string|max:255',

            'welcome_badge' => 'nullable|string|max:120',
            'welcome_title' => 'nullable|string|max:255',
            'welcome_subtitle' => 'nullable|string|max:800',
            'welcome_points' => 'nullable|string|max:2500',
            'welcome_primary_label' => 'nullable|string|max:100',
            'welcome_primary_enabled' => 'nullable|in:0,1',
            'welcome_primary_link' => 'nullable|string|max:255',
            'public_header_register_label' => 'nullable|string|max:100',
            'public_header_register_enabled' => 'nullable|in:0,1',
            'public_header_register_link' => 'nullable|string|max:255',
            'welcome_secondary_label' => 'nullable|string|max:100',
            'welcome_secondary_enabled' => 'nullable|in:0,1',
            'welcome_secondary_link' => 'nullable|string|max:255',
            'welcome_services_button_label' => 'nullable|string|max:100',
            'welcome_services_button_link' => 'nullable|string|max:255',
            'welcome_banner_enabled' => 'nullable|in:0,1',
            'welcome_banner_mode' => ['nullable', Rule::in(['image_only', 'image_text'])],
            'welcome_banner_image_url' => 'nullable|string|max:255',
            'welcome_banner_mobile_image_url' => 'nullable|string|max:255',
            'welcome_banner_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4096',
            'welcome_banner_mobile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4096',
            'welcome_text_section_enabled' => 'nullable|in:0,1',
            'welcome_text_section_rich_text' => 'nullable|string|max:8000',
            'welcome_text_section_subtext' => 'nullable|string|max:2500',
            'welcome_text_section_button_label' => 'nullable|string|max:100',
            'welcome_text_section_button_link' => 'nullable|string|max:255',
            'welcome_announcement_enabled' => 'nullable|in:0,1',
            'welcome_announcement_text' => 'nullable|string|max:2500',
            'welcome_announcement_link' => 'nullable|string|max:255',
            'welcome_announcement_bg_color' => $colorRule,
            'welcome_announcement_text_color' => $colorRule,
            'welcome_announcement_speed' => 'nullable|integer|min:6|max:60',
            'welcome_popup_enabled' => 'nullable|in:0,1',
            'welcome_popup_delay' => 'nullable|integer|min:0|max:15',
            'welcome_popup_badge' => 'nullable|string|max:80',
            'welcome_popup_title' => 'nullable|string|max:220',
            'welcome_popup_message' => 'nullable|string|max:700',
            'welcome_popup_price' => 'nullable|string|max:40',
            'welcome_popup_button_label' => 'nullable|string|max:80',
            'welcome_popup_button_link' => 'nullable|string|max:255',
            'welcome_popup_image_url' => 'nullable|string|max:255',
            'welcome_popup_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4096',
            'welcome_popup_bg_color' => $colorRule,
            'welcome_popup_text_color' => $colorRule,
            'welcome_popup_accent_color' => $colorRule,
            'email_header_bg_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:6144',
            'email_header_logo_url' => 'nullable|string|max:255',
            'email_header_bg_image_url' => 'nullable|string|max:255',
            'welcome_side_title' => 'nullable|string|max:120',
            'welcome_timeline_label' => 'nullable|string|max:120',
            'welcome_timeline' => 'nullable|string|max:500',
            'welcome_availability_label' => 'nullable|string|max:120',
            'welcome_availability' => 'nullable|string|max:500',
            'welcome_quality_label' => 'nullable|string|max:120',
            'welcome_quality_text' => 'nullable|string|max:500',
            'welcome_stat_one_value' => 'nullable|string|max:20',
            'welcome_stat_one_label' => 'nullable|string|max:40',
            'welcome_stat_two_value' => 'nullable|string|max:20',
            'welcome_stat_two_label' => 'nullable|string|max:40',
            'welcome_stat_three_value' => 'nullable|string|max:20',
            'welcome_stat_three_label' => 'nullable|string|max:40',

            'custom_css' => 'nullable|string|max:15000',
            'custom_js' => 'nullable|string|max:15000',
        ];

        $request->validate(array_merge($validationRules, $pdfTemplateValidation));

        if ($request->hasFile('site_logo')) {
            $logoPath = $this->handleFileUpload($request->file('site_logo'), 'logos');
            CustomizationSetting::setValue('site_logo', $logoPath, 'image', 'branding', 'Site logo');
        }

        if ($request->hasFile('site_favicon')) {
            $faviconPath = $this->handleFileUpload($request->file('site_favicon'), 'favicons');
            CustomizationSetting::setValue('site_favicon', $faviconPath, 'image', 'branding', 'Site favicon');
        }

        if ($request->hasFile('footer_branding_logo')) {
            $footerBrandingLogoPath = $this->handleFileUpload($request->file('footer_branding_logo'), 'footer-branding');
            CustomizationSetting::setValue('footer_branding_logo', $footerBrandingLogoPath, 'image', 'branding', 'Footer branding logo');
        }

        if ($request->hasFile('onboarding_guide_file')) {
            $guidePath = $this->handleFileUpload($request->file('onboarding_guide_file'), 'onboarding-guides');
            CustomizationSetting::setValue('onboarding_guide_file', $guidePath, 'file', 'onboarding', 'Client onboarding guide file');
        }

        if ($request->hasFile('welcome_popup_image')) {
            $popupImagePath = $this->handleFileUpload($request->file('welcome_popup_image'), 'popup-images');
            CustomizationSetting::setValue('welcome_popup_image', $popupImagePath, 'image', 'landing', 'Welcome sales popup uploaded image');
        }

        if ($request->hasFile('welcome_banner_image')) {
            $bannerImagePath = $this->handleFileUpload($request->file('welcome_banner_image'), 'welcome-banners');
            CustomizationSetting::setValue('welcome_banner_image', $bannerImagePath, 'image', 'landing', 'Welcome top banner image');
        }

        if ($request->hasFile('welcome_banner_mobile_image')) {
            $bannerMobileImagePath = $this->handleFileUpload($request->file('welcome_banner_mobile_image'), 'welcome-banners');
            CustomizationSetting::setValue('welcome_banner_mobile_image', $bannerMobileImagePath, 'image', 'landing', 'Welcome mobile banner image');
        }

        if ($request->hasFile('email_header_bg_image')) {
            $emailBackgroundPath = $this->handleFileUpload($request->file('email_header_bg_image'), 'email-branding');
            CustomizationSetting::setValue('email_header_bg_image', $emailBackgroundPath, 'image', 'email', 'Email header background image');
        }

        if ($request->hasFile('pdf_logo')) {
            $pdfLogoPath = $this->handleFileUpload($request->file('pdf_logo'), 'pdf-branding');
            CustomizationSetting::setValue('pdf_logo', $pdfLogoPath, 'image', 'pdf', 'PDF logo override');
        }

        $brandingSettings = [
            'site_name' => 'Site name',
            'site_tagline' => 'Brand tagline',
            'footer_text' => 'Footer text',
            'footer_branding_enabled' => 'Footer branding enabled',
            'footer_branding_show_logo' => 'Footer branding show logo',
            'footer_branding_prefix' => 'Footer branding prefix',
            'footer_branding_name' => 'Footer branding name',
            'footer_branding_link' => 'Footer branding link',
            'footer_branding_logo_url' => 'Footer branding logo URL',
            'footer_policy_links' => 'Footer policy links',
            'footer_social_links' => 'Footer social links',
            'auth_panel_caption' => 'Auth panel caption',
        ];

        $landingSettings = [
            'welcome_badge' => 'Welcome page badge',
            'welcome_title' => 'Welcome title',
            'welcome_subtitle' => 'Welcome subtitle',
            'welcome_points' => 'Welcome points',
            'welcome_primary_label' => 'Welcome primary button label',
            'welcome_primary_enabled' => 'Welcome primary button enabled',
            'welcome_primary_link' => 'Welcome primary button link',
            'public_header_register_label' => 'Public header register button label',
            'public_header_register_enabled' => 'Public header register button enabled',
            'public_header_register_link' => 'Public header register button link',
            'welcome_secondary_label' => 'Welcome secondary button label',
            'welcome_secondary_enabled' => 'Welcome secondary button enabled',
            'welcome_secondary_link' => 'Welcome secondary button link',
            'welcome_services_button_label' => 'Welcome services button label',
            'welcome_services_button_link' => 'Welcome services button link',
            'welcome_banner_enabled' => 'Welcome banner enabled',
            'welcome_banner_mode' => 'Welcome banner mode',
            'welcome_banner_image_url' => 'Welcome banner image URL',
            'welcome_banner_mobile_image_url' => 'Welcome mobile banner image URL',
            'welcome_text_section_enabled' => 'Welcome text section enabled',
            'welcome_text_section_rich_text' => 'Welcome text section rich text',
            'welcome_text_section_subtext' => 'Welcome text section subtext',
            'welcome_text_section_button_label' => 'Welcome text section button label',
            'welcome_text_section_button_link' => 'Welcome text section button link',
            'welcome_announcement_enabled' => 'Welcome announcement enabled',
            'welcome_announcement_text' => 'Welcome announcement text',
            'welcome_announcement_link' => 'Welcome announcement link',
            'welcome_announcement_bg_color' => 'Welcome announcement background color',
            'welcome_announcement_text_color' => 'Welcome announcement text color',
            'welcome_announcement_speed' => 'Welcome announcement speed',
            'welcome_popup_enabled' => 'Welcome sales popup enabled',
            'welcome_popup_delay' => 'Welcome sales popup delay',
            'welcome_popup_badge' => 'Welcome sales popup badge',
            'welcome_popup_title' => 'Welcome sales popup title',
            'welcome_popup_message' => 'Welcome sales popup message',
            'welcome_popup_price' => 'Welcome sales popup price',
            'welcome_popup_button_label' => 'Welcome sales popup button label',
            'welcome_popup_button_link' => 'Welcome sales popup button link',
            'welcome_popup_image_url' => 'Welcome sales popup image URL',
            'welcome_popup_bg_color' => 'Welcome sales popup background color',
            'welcome_popup_text_color' => 'Welcome sales popup text color',
            'welcome_popup_accent_color' => 'Welcome sales popup accent color',
            'welcome_side_title' => 'Welcome side panel title',
            'welcome_timeline_label' => 'Welcome timeline label',
            'welcome_timeline' => 'Welcome timeline note',
            'welcome_availability_label' => 'Welcome availability label',
            'welcome_availability' => 'Welcome availability note',
            'welcome_quality_label' => 'Welcome quality label',
            'welcome_quality_text' => 'Welcome quality note',
            'welcome_stat_one_value' => 'Welcome stat one value',
            'welcome_stat_one_label' => 'Welcome stat one label',
            'welcome_stat_two_value' => 'Welcome stat two value',
            'welcome_stat_two_label' => 'Welcome stat two label',
            'welcome_stat_three_value' => 'Welcome stat three value',
            'welcome_stat_three_label' => 'Welcome stat three label',
        ];

        $colorSettings = [
            'primary_color' => 'Primary color',
            'secondary_color' => 'Secondary color',
            'accent_color' => 'Accent color',
            'background_color' => 'Background color',
            'text_color' => 'Text color',
            'header_color' => 'Header color',
            'sidebar_color' => 'Sidebar color',
            'button_color' => 'Button color',
            'link_color' => 'Link color',
            'border_color' => 'Border color',
            'success_color' => 'Success color',
            'warning_color' => 'Warning color',
            'error_color' => 'Error color',
            'info_color' => 'Info color',
            'danger_color' => 'Danger color',
            'primary_dark' => 'Primary dark color',
            'secondary_dark' => 'Secondary dark color',
            'success_dark' => 'Success dark color',
            'warning_dark' => 'Warning dark color',
            'error_dark' => 'Error dark color',
            'info_dark' => 'Info dark color',
            'danger_dark' => 'Danger dark color',
        ];

        $layoutSettings = [
            'font_family' => 'Body font family',
            'display_font' => 'Display font family',
            'font_size' => 'Body font size',
            'border_radius' => 'Border radius',
            'box_shadow' => 'Box shadow',
            'animation_speed' => 'Animation speed',
            'sidebar_width' => 'Sidebar width',
            'header_height' => 'Header height',
        ];

        $onboardingSettings = [
            'onboarding_instructions' => 'Onboarding instructions',
        ];

        $pdfSettings = [
            'pdf_brand_name' => 'PDF brand name override',
            'pdf_footer_note' => 'PDF footer note',
            'pdf_logo_url' => 'PDF logo URL fallback',
            'pdf_accent_color' => 'PDF accent color',
            'pdf_heading_color' => 'PDF heading color',
            'pdf_body_text_color' => 'PDF body text color',
            'pdf_muted_text_color' => 'PDF muted text color',
            'pdf_border_color' => 'PDF border color',
            'pdf_panel_background' => 'PDF panel background',
            'pdf_table_header_background' => 'PDF table header background',
            'pdf_table_header_text_color' => 'PDF table header text color',
            'pdf_table_row_alt_background' => 'PDF table alternate row background',
            'pdf_brand_mark_background' => 'PDF brand mark background',
            'pdf_brand_mark_text_color' => 'PDF brand mark text color',
            'pdf_generated_label' => 'PDF generated label',
            'pdf_not_available_text' => 'PDF not available text',
        ];

        $clientGuideSettings = [
            'client_guide_badge' => 'Client guide badge',
            'client_guide_title' => 'Client guide title',
            'client_guide_subtitle' => 'Client guide subtitle',
            'client_guide_intro_title' => 'Client guide intro title',
            'client_guide_intro_text' => 'Client guide intro text',
            'client_guide_support_title' => 'Client guide support title',
            'client_guide_support_text' => 'Client guide support text',
            'client_guide_primary_label' => 'Client guide primary button label',
            'client_guide_primary_link' => 'Client guide primary button link',
            'client_guide_secondary_label' => 'Client guide secondary button label',
            'client_guide_secondary_link' => 'Client guide secondary button link',
            'client_guide_step_1_eyebrow' => 'Client guide step 1 eyebrow',
            'client_guide_step_1_title' => 'Client guide step 1 title',
            'client_guide_step_1_body' => 'Client guide step 1 body',
            'client_guide_step_2_eyebrow' => 'Client guide step 2 eyebrow',
            'client_guide_step_2_title' => 'Client guide step 2 title',
            'client_guide_step_2_body' => 'Client guide step 2 body',
            'client_guide_step_3_eyebrow' => 'Client guide step 3 eyebrow',
            'client_guide_step_3_title' => 'Client guide step 3 title',
            'client_guide_step_3_body' => 'Client guide step 3 body',
            'client_guide_step_4_eyebrow' => 'Client guide step 4 eyebrow',
            'client_guide_step_4_title' => 'Client guide step 4 title',
            'client_guide_step_4_body' => 'Client guide step 4 body',
            'client_guide_step_5_eyebrow' => 'Client guide step 5 eyebrow',
            'client_guide_step_5_title' => 'Client guide step 5 title',
            'client_guide_step_5_body' => 'Client guide step 5 body',
            'client_guide_step_6_eyebrow' => 'Client guide step 6 eyebrow',
            'client_guide_step_6_title' => 'Client guide step 6 title',
            'client_guide_step_6_body' => 'Client guide step 6 body',
        ];

        $policySettings = [
            'privacy_policy_content' => 'Privacy policy content',
            'privacy_policy_title' => 'Privacy policy title',
            'privacy_policy_subtitle' => 'Privacy policy subtitle',
            'privacy_policy_meta_text' => 'Privacy policy meta text',
            'terms_of_service_content' => 'Terms of service content',
            'terms_of_service_title' => 'Terms of service title',
            'terms_of_service_subtitle' => 'Terms of service subtitle',
            'terms_of_service_meta_text' => 'Terms of service meta text',
            'booking_policy_content' => 'Booking policy content',
            'booking_policy_title' => 'Booking policy title',
            'booking_policy_subtitle' => 'Booking policy subtitle',
            'booking_policy_meta_text' => 'Booking policy meta text',
            'refund_policy_content' => 'Refund policy content',
            'refund_policy_title' => 'Refund policy title',
            'refund_policy_subtitle' => 'Refund policy subtitle',
            'refund_policy_meta_text' => 'Refund policy meta text',
        ];

        $emailSettings = [
            'email_header_logo_url' => 'Email header logo URL',
            'email_header_bg_image_url' => 'Email header background image URL',
        ];

        foreach ($brandingSettings as $key => $description) {
            $this->syncSettingFromRequest($request, $key, 'text', 'branding', $description);
        }

        foreach ($landingSettings as $key => $description) {
            $this->syncSettingFromRequest($request, $key, 'text', 'landing', $description);
        }

        foreach ($colorSettings as $key => $description) {
            $this->syncSettingFromRequest($request, $key, 'color', 'colors', $description);
        }

        foreach ($layoutSettings as $key => $description) {
            $this->syncSettingFromRequest($request, $key, 'text', 'layout', $description);
        }

        foreach ($onboardingSettings as $key => $description) {
            $this->syncSettingFromRequest($request, $key, 'text', 'onboarding', $description);
        }

        foreach ($pdfSettings as $key => $description) {
            $type = str_contains($key, 'color') || str_contains($key, 'background')
                ? 'color'
                : 'text';

            $this->syncSettingFromRequest($request, $key, $type, 'pdf', $description);
        }

        foreach (PdfTemplateCatalog::ordered() as $editor) {
            foreach ($editor['fields'] as $field) {
                $this->syncSettingFromRequest(
                    $request,
                    $field['key'],
                    'text',
                    'pdf',
                    $field['label'],
                    CustomizationSetting::defaultValue($field['key'])
                );
            }
        }

        foreach ($clientGuideSettings as $key => $description) {
            $this->syncSettingFromRequest($request, $key, 'text', 'client-guide', $description);
        }

        foreach ($policySettings as $key => $description) {
            $this->syncSettingFromRequest($request, $key, 'html', 'policies', $description);
        }

        foreach ($emailSettings as $key => $description) {
            $this->syncSettingFromRequest($request, $key, 'text', 'email', $description);
        }

        $this->syncSettingFromRequest($request, 'custom_css', 'text', 'custom-code', 'Custom CSS');
        $this->syncSettingFromRequest($request, 'custom_js', 'text', 'custom-code', 'Custom JavaScript');

        CustomizationSetting::clearCache();
        Artisan::call('view:clear');

        $targetSection = $this->normalizeSection($section ?? $request->input('section'));

        return redirect()
            ->route('admin.customization.section', ['section' => $targetSection])
            ->with('success', 'Customization settings updated successfully.');
    }

    /**
     * Update customization settings from a specific section route.
     */
    public function updateSection(Request $request, string $section)
    {
        return $this->update($request, $section);
    }

    /**
     * Reset all settings to the default theme.
     */
    public function reset(Request $request)
    {
        CustomizationSetting::resetToDefaults();
        Artisan::call('view:clear');

        $targetSection = $this->normalizeSection($request->input('section'));

        return redirect()
            ->route('admin.customization.section', ['section' => $targetSection])
            ->with('success', 'Customization settings were reset to the default theme.');
    }

    /**
     * Handle AJAX file upload for immediate preview.
     */
    public function uploadFile(Request $request)
    {
        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'type' => 'required|in:logo,favicon',
        ]);

        $type = $request->input('type');
        $folder = $type === 'logo' ? 'logos' : 'favicons';
        $path = $this->handleFileUpload($request->file('file'), $folder);
        $settingKey = $type === 'logo' ? 'site_logo' : 'site_favicon';

        CustomizationSetting::setValue($settingKey, $path, 'image', 'branding', ucfirst($type));
        CustomizationSetting::clearCache();

        return response()->json([
            'success' => true,
            'url' => storage_public_url($path),
            'message' => ucfirst($type) . ' uploaded successfully.',
        ]);
    }

    /**
     * Editable static content fields for each PDF template.
     */
    private function pdfTemplateEditors(): array
    {
        return [
            [
                'title' => 'Admin Work Updates PDF',
                'description' => 'Static copy for the admin work updates export.',
                'tokens' => ['{record_count}', '{generated_date}'],
                'fields' => [
                    ['key' => 'pdf_admin_work_updates_tag', 'label' => 'Header Tag', 'default' => 'Workspace Report', 'type' => 'text', 'max' => 120],
                    ['key' => 'pdf_admin_work_updates_title', 'label' => 'Document Title', 'default' => 'Admin Work Updates Report', 'type' => 'text', 'max' => 255],
                    ['key' => 'pdf_admin_work_updates_subtitle', 'label' => 'Document Subtitle', 'default' => 'Filtered application activity across agents and clients.', 'type' => 'textarea', 'rows' => 2, 'max' => 800],
                    ['key' => 'pdf_admin_work_updates_intro_title', 'label' => 'Intro Title', 'default' => 'Operational Overview', 'type' => 'text', 'max' => 160],
                    ['key' => 'pdf_admin_work_updates_intro_text', 'label' => 'Intro Text', 'default' => 'This export captures the filtered work updates visible to administrators, including assignment ownership, application progress, supporting links, and recorded notes.', 'type' => 'textarea', 'rows' => 4, 'max' => 1600],
                    ['key' => 'pdf_admin_work_updates_metric_total_updates_label', 'label' => 'Metric Label: Total Updates', 'default' => 'Total Updates', 'type' => 'text', 'max' => 80],
                    ['key' => 'pdf_admin_work_updates_metric_applied_label', 'label' => 'Metric Label: Applied', 'default' => 'Applied', 'type' => 'text', 'max' => 80],
                    ['key' => 'pdf_admin_work_updates_metric_interviews_label', 'label' => 'Metric Label: Interviews', 'default' => 'Interviews', 'type' => 'text', 'max' => 80],
                    ['key' => 'pdf_admin_work_updates_metric_hired_label', 'label' => 'Metric Label: Hired', 'default' => 'Hired', 'type' => 'text', 'max' => 80],
                    ['key' => 'pdf_admin_work_updates_detail_included_records_label', 'label' => 'Detail Label: Included Records', 'default' => 'Included Records', 'type' => 'text', 'max' => 120],
                    ['key' => 'pdf_admin_work_updates_detail_report_scope_label', 'label' => 'Detail Label: Report Scope', 'default' => 'Report Scope', 'type' => 'text', 'max' => 120],
                    ['key' => 'pdf_admin_work_updates_detail_report_scope_value', 'label' => 'Detail Value: Report Scope', 'default' => 'Admin workspace export', 'type' => 'text', 'max' => 180],
                    ['key' => 'pdf_admin_work_updates_detail_application_coverage_label', 'label' => 'Detail Label: Application Coverage', 'default' => 'Application Coverage', 'type' => 'text', 'max' => 120],
                    ['key' => 'pdf_admin_work_updates_detail_application_coverage_value', 'label' => 'Detail Value: Application Coverage', 'default' => 'All filtered agents and clients', 'type' => 'text', 'max' => 180],
                    ['key' => 'pdf_admin_work_updates_register_title', 'label' => 'Register Title', 'default' => 'Work Update Register', 'type' => 'text', 'max' => 160],
                    ['key' => 'pdf_admin_work_updates_table_date_label', 'label' => 'Table Header: Date', 'default' => 'Date', 'type' => 'text', 'max' => 80],
                    ['key' => 'pdf_admin_work_updates_table_assignment_label', 'label' => 'Table Header: Assignment', 'default' => 'Assignment', 'type' => 'text', 'max' => 80],
                    ['key' => 'pdf_admin_work_updates_table_position_label', 'label' => 'Table Header: Position', 'default' => 'Position', 'type' => 'text', 'max' => 80],
                    ['key' => 'pdf_admin_work_updates_table_method_label', 'label' => 'Table Header: Method', 'default' => 'Method', 'type' => 'text', 'max' => 80],
                    ['key' => 'pdf_admin_work_updates_table_status_label', 'label' => 'Table Header: Status', 'default' => 'Status', 'type' => 'text', 'max' => 80],
                    ['key' => 'pdf_admin_work_updates_table_references_label', 'label' => 'Table Header: References', 'default' => 'References', 'type' => 'text', 'max' => 80],
                    ['key' => 'pdf_admin_work_updates_prefix_submitted', 'label' => 'Row Prefix: Submitted', 'default' => 'Submitted', 'type' => 'text', 'max' => 80],
                    ['key' => 'pdf_admin_work_updates_assignment_agent_label', 'label' => 'Assignment Label: Agent', 'default' => 'Agent:', 'type' => 'text', 'max' => 80],
                    ['key' => 'pdf_admin_work_updates_assignment_client_label', 'label' => 'Assignment Label: Client', 'default' => 'Client:', 'type' => 'text', 'max' => 80],
                    ['key' => 'pdf_admin_work_updates_unknown_agent_text', 'label' => 'Fallback Text: Unknown Agent', 'default' => 'Unknown Agent', 'type' => 'text', 'max' => 120],
                    ['key' => 'pdf_admin_work_updates_unknown_client_text', 'label' => 'Fallback Text: Unknown Client', 'default' => 'Unknown Client', 'type' => 'text', 'max' => 120],
                    ['key' => 'pdf_admin_work_updates_untitled_position_text', 'label' => 'Fallback Text: Untitled Position', 'default' => 'Untitled Position', 'type' => 'text', 'max' => 120],
                    ['key' => 'pdf_admin_work_updates_company_missing_text', 'label' => 'Fallback Text: Company Not Provided', 'default' => 'Company not provided', 'type' => 'text', 'max' => 120],
                    ['key' => 'pdf_admin_work_updates_reference_job_label', 'label' => 'Reference Label: Job', 'default' => 'Job:', 'type' => 'text', 'max' => 80],
                    ['key' => 'pdf_admin_work_updates_reference_success_label', 'label' => 'Reference Label: Success', 'default' => 'Success:', 'type' => 'text', 'max' => 80],
                    ['key' => 'pdf_admin_work_updates_reference_note_label', 'label' => 'Reference Label: Note', 'default' => 'Note:', 'type' => 'text', 'max' => 80],
                    ['key' => 'pdf_admin_work_updates_no_references_text', 'label' => 'Fallback Text: No References', 'default' => 'No reference links or notes recorded.', 'type' => 'textarea', 'rows' => 2, 'max' => 300],
                    ['key' => 'pdf_admin_work_updates_empty_title', 'label' => 'Empty State Title', 'default' => 'No work updates found', 'type' => 'text', 'max' => 160],
                    ['key' => 'pdf_admin_work_updates_empty_text', 'label' => 'Empty State Text', 'default' => 'No filtered work updates were available when this PDF was generated.', 'type' => 'textarea', 'rows' => 3, 'max' => 800],
                    ['key' => 'pdf_admin_work_updates_footer_note', 'label' => 'Footer Note', 'default' => 'Admin work updates export for {record_count} records.', 'type' => 'textarea', 'rows' => 2, 'max' => 500],
                ],
            ],
            [
                'title' => 'Agent Work Updates PDF',
                'description' => 'Static copy for the agent work updates export.',
                'tokens' => ['{agent_name}', '{record_count}', '{generated_date}'],
                'fields' => [
                    ['key' => 'pdf_agent_work_updates_tag', 'label' => 'Header Tag', 'default' => 'Agent Export', 'type' => 'text', 'max' => 120],
                    ['key' => 'pdf_agent_work_updates_title', 'label' => 'Document Title', 'default' => 'Agent Work Updates Report', 'type' => 'text', 'max' => 255],
                    ['key' => 'pdf_agent_work_updates_subtitle', 'label' => 'Document Subtitle', 'default' => 'Activity report for {agent_name}.', 'type' => 'textarea', 'rows' => 2, 'max' => 800],
                    ['key' => 'pdf_agent_work_updates_intro_title', 'label' => 'Intro Title', 'default' => 'Report Summary', 'type' => 'text', 'max' => 160],
                    ['key' => 'pdf_agent_work_updates_intro_text', 'label' => 'Intro Text', 'default' => 'This report lists the filtered work updates submitted by {agent_name}, including client ownership, application outcome, and any saved links or notes.', 'type' => 'textarea', 'rows' => 4, 'max' => 1600],
                    ['key' => 'pdf_agent_work_updates_metric_total_updates_label', 'label' => 'Metric Label: Total Updates', 'default' => 'Total Updates', 'type' => 'text', 'max' => 80],
                    ['key' => 'pdf_agent_work_updates_metric_applied_label', 'label' => 'Metric Label: Applied', 'default' => 'Applied', 'type' => 'text', 'max' => 80],
                    ['key' => 'pdf_agent_work_updates_metric_interviews_label', 'label' => 'Metric Label: Interviews', 'default' => 'Interviews', 'type' => 'text', 'max' => 80],
                    ['key' => 'pdf_agent_work_updates_metric_hired_label', 'label' => 'Metric Label: Hired', 'default' => 'Hired', 'type' => 'text', 'max' => 80],
                    ['key' => 'pdf_agent_work_updates_detail_agent_label', 'label' => 'Detail Label: Agent', 'default' => 'Agent', 'type' => 'text', 'max' => 120],
                    ['key' => 'pdf_agent_work_updates_detail_included_records_label', 'label' => 'Detail Label: Included Records', 'default' => 'Included Records', 'type' => 'text', 'max' => 120],
                    ['key' => 'pdf_agent_work_updates_detail_report_scope_label', 'label' => 'Detail Label: Report Scope', 'default' => 'Report Scope', 'type' => 'text', 'max' => 120],
                    ['key' => 'pdf_agent_work_updates_detail_report_scope_value', 'label' => 'Detail Value: Report Scope', 'default' => 'Filtered agent dashboard export', 'type' => 'text', 'max' => 180],
                    ['key' => 'pdf_agent_work_updates_register_title', 'label' => 'Register Title', 'default' => 'Submission Register', 'type' => 'text', 'max' => 160],
                    ['key' => 'pdf_agent_work_updates_table_date_label', 'label' => 'Table Header: Date', 'default' => 'Date', 'type' => 'text', 'max' => 80],
                    ['key' => 'pdf_agent_work_updates_table_client_label', 'label' => 'Table Header: Client', 'default' => 'Client', 'type' => 'text', 'max' => 80],
                    ['key' => 'pdf_agent_work_updates_table_position_label', 'label' => 'Table Header: Position', 'default' => 'Position', 'type' => 'text', 'max' => 80],
                    ['key' => 'pdf_agent_work_updates_table_method_label', 'label' => 'Table Header: Method', 'default' => 'Method', 'type' => 'text', 'max' => 80],
                    ['key' => 'pdf_agent_work_updates_table_status_label', 'label' => 'Table Header: Status', 'default' => 'Status', 'type' => 'text', 'max' => 80],
                    ['key' => 'pdf_agent_work_updates_table_references_label', 'label' => 'Table Header: References', 'default' => 'References', 'type' => 'text', 'max' => 80],
                    ['key' => 'pdf_agent_work_updates_prefix_submitted', 'label' => 'Row Prefix: Submitted', 'default' => 'Submitted', 'type' => 'text', 'max' => 80],
                    ['key' => 'pdf_agent_work_updates_unknown_client_text', 'label' => 'Fallback Text: Unknown Client', 'default' => 'Unknown Client', 'type' => 'text', 'max' => 120],
                    ['key' => 'pdf_agent_work_updates_no_email_text', 'label' => 'Fallback Text: No Email On File', 'default' => 'No email on file', 'type' => 'text', 'max' => 120],
                    ['key' => 'pdf_agent_work_updates_untitled_position_text', 'label' => 'Fallback Text: Untitled Position', 'default' => 'Untitled Position', 'type' => 'text', 'max' => 120],
                    ['key' => 'pdf_agent_work_updates_company_missing_text', 'label' => 'Fallback Text: Company Not Provided', 'default' => 'Company not provided', 'type' => 'text', 'max' => 120],
                    ['key' => 'pdf_agent_work_updates_reference_job_label', 'label' => 'Reference Label: Job', 'default' => 'Job:', 'type' => 'text', 'max' => 80],
                    ['key' => 'pdf_agent_work_updates_reference_success_label', 'label' => 'Reference Label: Success', 'default' => 'Success:', 'type' => 'text', 'max' => 80],
                    ['key' => 'pdf_agent_work_updates_reference_note_label', 'label' => 'Reference Label: Note', 'default' => 'Note:', 'type' => 'text', 'max' => 80],
                    ['key' => 'pdf_agent_work_updates_no_references_text', 'label' => 'Fallback Text: No References', 'default' => 'No reference links or notes recorded.', 'type' => 'textarea', 'rows' => 2, 'max' => 300],
                    ['key' => 'pdf_agent_work_updates_empty_title', 'label' => 'Empty State Title', 'default' => 'No work updates found', 'type' => 'text', 'max' => 160],
                    ['key' => 'pdf_agent_work_updates_empty_text', 'label' => 'Empty State Text', 'default' => 'No filtered work updates were available for this agent when the PDF was generated.', 'type' => 'textarea', 'rows' => 3, 'max' => 800],
                    ['key' => 'pdf_agent_work_updates_footer_note', 'label' => 'Footer Note', 'default' => 'Agent report for {agent_name}.', 'type' => 'textarea', 'rows' => 2, 'max' => 500],
                ],
            ],
            [
                'title' => 'Client Work Updates PDF',
                'description' => 'Static copy for the client work updates export.',
                'tokens' => ['{client_name}', '{client_email}', '{record_count}', '{generated_date}'],
                'fields' => [
                    ['key' => 'pdf_client_work_updates_tag', 'label' => 'Header Tag', 'default' => 'Client Export', 'type' => 'text', 'max' => 120],
                    ['key' => 'pdf_client_work_updates_title', 'label' => 'Document Title', 'default' => 'Client Work Updates Report', 'type' => 'text', 'max' => 255],
                    ['key' => 'pdf_client_work_updates_subtitle', 'label' => 'Document Subtitle', 'default' => 'Approved work updates for {client_name}.', 'type' => 'textarea', 'rows' => 2, 'max' => 800],
                    ['key' => 'pdf_client_work_updates_intro_title', 'label' => 'Intro Title', 'default' => 'Client Summary', 'type' => 'text', 'max' => 160],
                    ['key' => 'pdf_client_work_updates_intro_text', 'label' => 'Intro Text', 'default' => 'This report contains the approved work updates currently available in the client portal, including job details, handling agent, application progress, and any saved references.', 'type' => 'textarea', 'rows' => 4, 'max' => 1600],
                    ['key' => 'pdf_client_work_updates_metric_total_updates_label', 'label' => 'Metric Label: Total Updates', 'default' => 'Total Updates', 'type' => 'text', 'max' => 80],
                    ['key' => 'pdf_client_work_updates_metric_applied_label', 'label' => 'Metric Label: Applied', 'default' => 'Applied', 'type' => 'text', 'max' => 80],
                    ['key' => 'pdf_client_work_updates_metric_interviews_label', 'label' => 'Metric Label: Interviews', 'default' => 'Interviews', 'type' => 'text', 'max' => 80],
                    ['key' => 'pdf_client_work_updates_metric_hired_label', 'label' => 'Metric Label: Hired', 'default' => 'Hired', 'type' => 'text', 'max' => 80],
                    ['key' => 'pdf_client_work_updates_detail_client_label', 'label' => 'Detail Label: Client', 'default' => 'Client', 'type' => 'text', 'max' => 120],
                    ['key' => 'pdf_client_work_updates_detail_email_label', 'label' => 'Detail Label: Email', 'default' => 'Email', 'type' => 'text', 'max' => 120],
                    ['key' => 'pdf_client_work_updates_detail_included_records_label', 'label' => 'Detail Label: Included Records', 'default' => 'Included Records', 'type' => 'text', 'max' => 120],
                    ['key' => 'pdf_client_work_updates_register_title', 'label' => 'Register Title', 'default' => 'Approved Work Updates', 'type' => 'text', 'max' => 160],
                    ['key' => 'pdf_client_work_updates_table_date_label', 'label' => 'Table Header: Date', 'default' => 'Date', 'type' => 'text', 'max' => 80],
                    ['key' => 'pdf_client_work_updates_table_submitted_by_label', 'label' => 'Table Header: Submitted By', 'default' => 'Submitted By', 'type' => 'text', 'max' => 80],
                    ['key' => 'pdf_client_work_updates_table_position_label', 'label' => 'Table Header: Position', 'default' => 'Position', 'type' => 'text', 'max' => 80],
                    ['key' => 'pdf_client_work_updates_table_method_label', 'label' => 'Table Header: Method', 'default' => 'Method', 'type' => 'text', 'max' => 80],
                    ['key' => 'pdf_client_work_updates_table_progress_label', 'label' => 'Table Header: Progress', 'default' => 'Progress', 'type' => 'text', 'max' => 80],
                    ['key' => 'pdf_client_work_updates_table_references_label', 'label' => 'Table Header: References', 'default' => 'References', 'type' => 'text', 'max' => 80],
                    ['key' => 'pdf_client_work_updates_prefix_added', 'label' => 'Row Prefix: Added', 'default' => 'Added', 'type' => 'text', 'max' => 80],
                    ['key' => 'pdf_client_work_updates_assigned_agent_text', 'label' => 'Fallback Text: Assigned Agent', 'default' => 'Assigned Agent', 'type' => 'text', 'max' => 120],
                    ['key' => 'pdf_client_work_updates_no_email_text', 'label' => 'Fallback Text: No Email On File', 'default' => 'No email on file', 'type' => 'text', 'max' => 120],
                    ['key' => 'pdf_client_work_updates_untitled_position_text', 'label' => 'Fallback Text: Untitled Position', 'default' => 'Untitled Position', 'type' => 'text', 'max' => 120],
                    ['key' => 'pdf_client_work_updates_company_missing_text', 'label' => 'Fallback Text: Company Not Provided', 'default' => 'Company not provided', 'type' => 'text', 'max' => 120],
                    ['key' => 'pdf_client_work_updates_reference_job_label', 'label' => 'Reference Label: Job', 'default' => 'Job:', 'type' => 'text', 'max' => 80],
                    ['key' => 'pdf_client_work_updates_reference_success_label', 'label' => 'Reference Label: Success', 'default' => 'Success:', 'type' => 'text', 'max' => 80],
                    ['key' => 'pdf_client_work_updates_reference_note_label', 'label' => 'Reference Label: Note', 'default' => 'Note:', 'type' => 'text', 'max' => 80],
                    ['key' => 'pdf_client_work_updates_no_references_text', 'label' => 'Fallback Text: No References', 'default' => 'No reference links or notes recorded.', 'type' => 'textarea', 'rows' => 2, 'max' => 300],
                    ['key' => 'pdf_client_work_updates_empty_title', 'label' => 'Empty State Title', 'default' => 'No work updates found', 'type' => 'text', 'max' => 160],
                    ['key' => 'pdf_client_work_updates_empty_text', 'label' => 'Empty State Text', 'default' => 'No approved work updates were available for this client when the PDF was generated.', 'type' => 'textarea', 'rows' => 3, 'max' => 800],
                    ['key' => 'pdf_client_work_updates_footer_note', 'label' => 'Footer Note', 'default' => 'Client report for {client_name}.', 'type' => 'textarea', 'rows' => 2, 'max' => 500],
                ],
            ],
            [
                'title' => 'Onboarding PDF',
                'description' => 'Static copy for the client onboarding submission PDF.',
                'tokens' => ['{client_name}', '{client_email}', '{package}', '{submitted_at}', '{generated_date}'],
                'fields' => [
                    ['key' => 'pdf_onboarding_tag', 'label' => 'Header Tag', 'default' => 'Onboarding Record', 'type' => 'text', 'max' => 120],
                    ['key' => 'pdf_onboarding_title', 'label' => 'Document Title', 'default' => 'Client Onboarding Submission', 'type' => 'text', 'max' => 255],
                    ['key' => 'pdf_onboarding_subtitle', 'label' => 'Document Subtitle', 'default' => 'Structured intake report for {client_name}.', 'type' => 'textarea', 'rows' => 2, 'max' => 800],
                    ['key' => 'pdf_onboarding_intro_title', 'label' => 'Intro Title', 'default' => 'Submission Overview', 'type' => 'text', 'max' => 160],
                    ['key' => 'pdf_onboarding_intro_text', 'label' => 'Intro Text', 'default' => 'This report captures the onboarding details submitted by the client in a structured format, matching the internal review style used across the workspace export reports.', 'type' => 'textarea', 'rows' => 4, 'max' => 1600],
                    ['key' => 'pdf_onboarding_metric_sections_label', 'label' => 'Metric Label: Sections', 'default' => 'Sections', 'type' => 'text', 'max' => 80],
                    ['key' => 'pdf_onboarding_metric_data_fields_label', 'label' => 'Metric Label: Data Fields', 'default' => 'Data Fields', 'type' => 'text', 'max' => 80],
                    ['key' => 'pdf_onboarding_metric_notes_label', 'label' => 'Metric Label: Notes', 'default' => 'Notes', 'type' => 'text', 'max' => 80],
                    ['key' => 'pdf_onboarding_metric_files_label', 'label' => 'Metric Label: Files', 'default' => 'Files', 'type' => 'text', 'max' => 80],
                    ['key' => 'pdf_onboarding_detail_client_label', 'label' => 'Detail Label: Client', 'default' => 'Client', 'type' => 'text', 'max' => 120],
                    ['key' => 'pdf_onboarding_detail_email_label', 'label' => 'Detail Label: Email', 'default' => 'Email', 'type' => 'text', 'max' => 120],
                    ['key' => 'pdf_onboarding_detail_package_label', 'label' => 'Detail Label: Package', 'default' => 'Package', 'type' => 'text', 'max' => 120],
                    ['key' => 'pdf_onboarding_detail_submitted_label', 'label' => 'Detail Label: Submitted', 'default' => 'Submitted', 'type' => 'text', 'max' => 120],
                    ['key' => 'pdf_onboarding_detail_resume_file_label', 'label' => 'Detail Label: Resume File', 'default' => 'Resume File', 'type' => 'text', 'max' => 120],
                    ['key' => 'pdf_onboarding_detail_form_file_label', 'label' => 'Detail Label: Form File', 'default' => 'Form File', 'type' => 'text', 'max' => 120],
                    ['key' => 'pdf_onboarding_status_received_text', 'label' => 'Status Text: Received', 'default' => 'Received', 'type' => 'text', 'max' => 80],
                    ['key' => 'pdf_onboarding_status_not_uploaded_text', 'label' => 'Status Text: Not Uploaded', 'default' => 'Not uploaded', 'type' => 'text', 'max' => 80],
                    ['key' => 'pdf_onboarding_register_title', 'label' => 'Register Title', 'default' => 'Onboarding Register', 'type' => 'text', 'max' => 160],
                    ['key' => 'pdf_onboarding_section_prefix', 'label' => 'Section Prefix', 'default' => 'Section', 'type' => 'text', 'max' => 80],
                    ['key' => 'pdf_onboarding_section_fields_suffix', 'label' => 'Section Count Suffix', 'default' => 'fields', 'type' => 'text', 'max' => 80],
                    ['key' => 'pdf_onboarding_section_notes_title', 'label' => 'Section Notes Title', 'default' => 'Section Notes', 'type' => 'text', 'max' => 120],
                    ['key' => 'pdf_onboarding_client_note_title', 'label' => 'Client Note Title', 'default' => 'Client Note', 'type' => 'text', 'max' => 120],
                    ['key' => 'pdf_onboarding_empty_title', 'label' => 'Empty State Title', 'default' => 'No onboarding sections found', 'type' => 'text', 'max' => 160],
                    ['key' => 'pdf_onboarding_empty_text', 'label' => 'Empty State Text', 'default' => 'This submission did not include any structured onboarding sections.', 'type' => 'textarea', 'rows' => 3, 'max' => 800],
                    ['key' => 'pdf_onboarding_footer_note', 'label' => 'Footer Note', 'default' => 'Onboarding submission for {client_name}.', 'type' => 'textarea', 'rows' => 2, 'max' => 500],
                ],
            ],
        ];
    }

    /**
     * Get CSS variables for dynamic theming.
     */
    public function getCssVariables()
    {
        $settings = CustomizationSetting::getAllActive();

        $css = ":root {\n";

        $cssVariables = [
            'primary_color' => '--primary-color',
            'secondary_color' => '--secondary-color',
            'accent_color' => '--accent-color',
            'background_color' => '--background-color',
            'text_color' => '--text-color',
            'header_color' => '--header-color',
            'sidebar_color' => '--sidebar-color',
            'button_color' => '--button-color',
            'link_color' => '--link-color',
            'border_color' => '--border-color',
            'success_color' => '--success-color',
            'warning_color' => '--warning-color',
            'error_color' => '--error-color',
            'info_color' => '--info-color',
            'danger_color' => '--danger-color',
            'primary_dark' => '--primary-dark',
            'secondary_dark' => '--secondary-dark',
            'success_dark' => '--success-dark',
            'warning_dark' => '--warning-dark',
            'error_dark' => '--error-dark',
            'info_dark' => '--info-dark',
            'danger_dark' => '--danger-dark',
            'font_family' => '--font-family',
            'display_font' => '--display-font',
            'font_size' => '--font-size',
            'border_radius' => '--border-radius',
            'box_shadow' => '--box-shadow',
            'animation_speed' => '--animation-speed',
            'sidebar_width' => '--sidebar-width',
            'header_height' => '--header-height',
        ];

        foreach ($cssVariables as $key => $cssVar) {
            $value = $settings->get($key)?->setting_value ?? CustomizationSetting::defaultValue($key);

            if ($value === null || $value === '') {
                continue;
            }

            $css .= "  {$cssVar}: {$value};\n";
        }

        $css .= "}\n";

        $customCss = $settings->get('custom_css')?->setting_value;

        if ($customCss) {
            $css .= "\n/* Custom CSS */\n{$customCss}";
        }

        return response($css)->header('Content-Type', 'text/css');
    }

    /**
     * Persist a request value or restore its default if cleared.
     */
    private function syncSettingFromRequest(Request $request, string $key, string $type, string $category, string $description, $fallbackDefault = null): void
    {
        if (!$request->has($key)) {
            return;
        }

        $value = $request->input($key);

        if (is_string($value)) {
            $value = trim($value);
        }

        $preserveBlankValues = [
            'footer_branding_prefix',
            'footer_branding_name',
        ];

        if ($value === null && in_array($key, $preserveBlankValues, true)) {
            $value = '';
        }

        if ($value === '' && !in_array($key, $preserveBlankValues, true)) {
            $value = CustomizationSetting::defaultValue($key, $fallbackDefault);
        }

        CustomizationSetting::setValue($key, $value, $type, $category, $description);
    }

    /**
     * Store uploaded files in the public customization directory.
     */
    private function handleFileUpload($file, string $folder): string
    {
        $filename = Str::random(20) . '.' . $file->getClientOriginalExtension();

        return $file->storeAs("customization/{$folder}", $filename, 'public');
    }
}
