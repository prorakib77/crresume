@php
    $settings = \App\Models\CustomizationSetting::getAllActive();
    $siteName = site_name();
    $siteLogo = site_logo();
    $siteFavicon = site_favicon();
    $products = isset($products) ? $products : collect();
    $reviews = isset($reviews) ? $reviews : collect();
    $saleCountdown = $saleCountdown ?? null;
    $sliderProducts = collect();
    $sliderReviews = collect();

    if ($products->isNotEmpty()) {
        $baseProducts = $products->values();
        $targetCount = max(6, $baseProducts->count());

        for ($i = 0; $i < $targetCount; $i++) {
            $sliderProducts->push($baseProducts[$i % $baseProducts->count()]);
        }
    }

    if ($reviews->isNotEmpty()) {
        $baseReviews = $reviews->values();
        $targetReviewCount = max(6, $baseReviews->count());

        for ($i = 0; $i < $targetReviewCount; $i++) {
            $sliderReviews->push($baseReviews[$i % $baseReviews->count()]);
        }
    }

    $primaryEnabled = (string) ($settings->get('welcome_primary_enabled')?->setting_value ?? \App\Models\CustomizationSetting::defaultValue('welcome_primary_enabled', '1')) === '1';
    $secondaryEnabled = (string) ($settings->get('welcome_secondary_enabled')?->setting_value ?? \App\Models\CustomizationSetting::defaultValue('welcome_secondary_enabled', '1')) === '1';
    $primaryLabel = $primaryEnabled ? ($settings->get('welcome_primary_label')?->setting_value ?? 'Secure Your Spot') : '';
    $primaryLink = $settings->get('welcome_primary_link')?->setting_value ?? route('register');
    $secondaryLabel = $secondaryEnabled ? ($settings->get('welcome_secondary_label')?->setting_value ?? 'Login') : '';
    $secondaryLink = $settings->get('welcome_secondary_link')?->setting_value ?? route('login');
    $headerRegisterEnabled = (string) ($settings->get('public_header_register_enabled')?->setting_value ?? $settings->get('welcome_primary_enabled')?->setting_value ?? \App\Models\CustomizationSetting::defaultValue('public_header_register_enabled', '1')) === '1';
    $headerRegisterLabel = $headerRegisterEnabled ? ($settings->get('public_header_register_label')?->setting_value ?? $settings->get('welcome_primary_label')?->setting_value ?? 'Secure Your Spot') : '';
    $headerRegisterLink = $settings->get('public_header_register_link')?->setting_value ?? $settings->get('welcome_primary_link')?->setting_value ?? route('register');
    $welcomeBadge = trim((string) ($settings->get('welcome_badge')?->setting_value ?? \App\Models\CustomizationSetting::defaultValue('welcome_badge', '')));
    $welcomeTitle = trim((string) ($settings->get('welcome_title')?->setting_value ?? \App\Models\CustomizationSetting::defaultValue('welcome_title', '')));
    $welcomeSubtitle = trim((string) ($settings->get('welcome_subtitle')?->setting_value ?? \App\Models\CustomizationSetting::defaultValue('welcome_subtitle', '')));
    $servicesButtonLabel = $settings->get('welcome_services_button_label')?->setting_value ?? \App\Models\CustomizationSetting::defaultValue('welcome_services_button_label', 'View All Services');
    $servicesButtonLink = $settings->get('welcome_services_button_link')?->setting_value ?? \App\Models\CustomizationSetting::defaultValue('welcome_services_button_link', '#');
    $bannerEnabled = (string) ($settings->get('welcome_banner_enabled')?->setting_value ?? \App\Models\CustomizationSetting::defaultValue('welcome_banner_enabled', '1')) === '1';
    $bannerMode = (string) ($settings->get('welcome_banner_mode')?->setting_value ?? \App\Models\CustomizationSetting::defaultValue('welcome_banner_mode', 'image_text'));
    $bannerMode = in_array($bannerMode, ['image_only', 'image_text'], true) ? $bannerMode : 'image_text';
    $bannerImageUrl = trim((string) ($settings->get('welcome_banner_image_url')?->setting_value ?? \App\Models\CustomizationSetting::defaultValue('welcome_banner_image_url', '')));
    $bannerMobileImageUrl = trim((string) ($settings->get('welcome_banner_mobile_image_url')?->setting_value ?? \App\Models\CustomizationSetting::defaultValue('welcome_banner_mobile_image_url', '')));
    $bannerUploadedImage = \App\Models\CustomizationSetting::getAssetUrl('welcome_banner_image');
    $bannerUploadedMobileImage = \App\Models\CustomizationSetting::getAssetUrl('welcome_banner_mobile_image');
    $bannerImageSource = trim((string) ($bannerUploadedImage ?: $bannerImageUrl));
    $bannerMobileImageSource = trim((string) ($bannerUploadedMobileImage ?: $bannerMobileImageUrl));
    $bannerDisplayImageSource = $bannerImageSource !== '' ? $bannerImageSource : $bannerMobileImageSource;
    $bannerHasText = $welcomeBadge !== '' || $welcomeTitle !== '' || $welcomeSubtitle !== '' || trim((string) $primaryLabel) !== '' || trim((string) $secondaryLabel) !== '';
    $showTopBanner = $bannerEnabled && ($bannerDisplayImageSource !== '' || $bannerHasText);
    $bannerPrimaryOpenNewTab = \Illuminate\Support\Str::startsWith(strtolower((string) $primaryLink), ['http://', 'https://']);
    $bannerSecondaryOpenNewTab = \Illuminate\Support\Str::startsWith(strtolower((string) $secondaryLink), ['http://', 'https://']);
    $textSectionEnabled = (string) ($settings->get('welcome_text_section_enabled')?->setting_value ?? \App\Models\CustomizationSetting::defaultValue('welcome_text_section_enabled', '1')) === '1';
    $textSectionRichText = trim((string) ($settings->get('welcome_text_section_rich_text')?->setting_value ?? \App\Models\CustomizationSetting::defaultValue('welcome_text_section_rich_text', '')));
    $textSectionSubtext = trim((string) ($settings->get('welcome_text_section_subtext')?->setting_value ?? \App\Models\CustomizationSetting::defaultValue('welcome_text_section_subtext', '')));
    $textSectionButtonLabel = trim((string) ($settings->get('welcome_text_section_button_label')?->setting_value ?? \App\Models\CustomizationSetting::defaultValue('welcome_text_section_button_label', '')));
    $textSectionButtonLink = trim((string) ($settings->get('welcome_text_section_button_link')?->setting_value ?? \App\Models\CustomizationSetting::defaultValue('welcome_text_section_button_link', '#')));
    $showTextSection = $textSectionEnabled && ($textSectionRichText !== '' || $textSectionSubtext !== '' || $textSectionButtonLabel !== '');
    $textSectionButtonOpenNewTab = \Illuminate\Support\Str::startsWith(strtolower($textSectionButtonLink), ['http://', 'https://']);
    $announcementEnabled = (string) ($settings->get('welcome_announcement_enabled')?->setting_value ?? \App\Models\CustomizationSetting::defaultValue('welcome_announcement_enabled', '0')) === '1';
    $announcementText = trim((string) ($settings->get('welcome_announcement_text')?->setting_value ?? \App\Models\CustomizationSetting::defaultValue('welcome_announcement_text', '')));
    $announcementLink = trim((string) ($settings->get('welcome_announcement_link')?->setting_value ?? \App\Models\CustomizationSetting::defaultValue('welcome_announcement_link', '')));
    $announcementBgColor = (string) ($settings->get('welcome_announcement_bg_color')?->setting_value ?? \App\Models\CustomizationSetting::defaultValue('welcome_announcement_bg_color', '#111111'));
    $announcementTextColor = (string) ($settings->get('welcome_announcement_text_color')?->setting_value ?? \App\Models\CustomizationSetting::defaultValue('welcome_announcement_text_color', '#F7F2E8'));
    $announcementSpeed = (int) ($settings->get('welcome_announcement_speed')?->setting_value ?? \App\Models\CustomizationSetting::defaultValue('welcome_announcement_speed', '20'));
    $announcementSpeed = max(6, min(60, $announcementSpeed));
    $announcementOpenNewTab = \Illuminate\Support\Str::startsWith(strtolower($announcementLink), ['http://', 'https://']);
    $showAnnouncement = $announcementEnabled && $announcementText !== '';
    $popupEnabled = (string) ($settings->get('welcome_popup_enabled')?->setting_value ?? \App\Models\CustomizationSetting::defaultValue('welcome_popup_enabled', '0')) === '1';
    $popupDelay = (int) ($settings->get('welcome_popup_delay')?->setting_value ?? \App\Models\CustomizationSetting::defaultValue('welcome_popup_delay', '1'));
    $popupDelay = max(0, min(15, $popupDelay));
    $popupBadge = trim((string) ($settings->get('welcome_popup_badge')?->setting_value ?? \App\Models\CustomizationSetting::defaultValue('welcome_popup_badge', 'Limited Time Offer')));
    $popupTitle = trim((string) ($settings->get('welcome_popup_title')?->setting_value ?? \App\Models\CustomizationSetting::defaultValue('welcome_popup_title', '')));
    $popupMessage = trim((string) ($settings->get('welcome_popup_message')?->setting_value ?? \App\Models\CustomizationSetting::defaultValue('welcome_popup_message', '')));
    $popupPrice = trim((string) ($settings->get('welcome_popup_price')?->setting_value ?? \App\Models\CustomizationSetting::defaultValue('welcome_popup_price', '')));
    $popupButtonLabel = trim((string) ($settings->get('welcome_popup_button_label')?->setting_value ?? \App\Models\CustomizationSetting::defaultValue('welcome_popup_button_label', 'Book Now')));
    $popupButtonLink = trim((string) ($settings->get('welcome_popup_button_link')?->setting_value ?? \App\Models\CustomizationSetting::defaultValue('welcome_popup_button_link', '#')));
    $popupImageUrl = trim((string) ($settings->get('welcome_popup_image_url')?->setting_value ?? \App\Models\CustomizationSetting::defaultValue('welcome_popup_image_url', '')));
    $popupUploadedImage = \App\Models\CustomizationSetting::getAssetUrl('welcome_popup_image');
    $popupImageSource = $popupUploadedImage ?: $popupImageUrl;
    $popupBgColor = (string) ($settings->get('welcome_popup_bg_color')?->setting_value ?? \App\Models\CustomizationSetting::defaultValue('welcome_popup_bg_color', '#111111'));
    $popupTextColor = (string) ($settings->get('welcome_popup_text_color')?->setting_value ?? \App\Models\CustomizationSetting::defaultValue('welcome_popup_text_color', '#FFFFFF'));
    $popupAccentColor = (string) ($settings->get('welcome_popup_accent_color')?->setting_value ?? \App\Models\CustomizationSetting::defaultValue('welcome_popup_accent_color', '#C8A45D'));
    $popupOpenNewTab = \Illuminate\Support\Str::startsWith(strtolower($popupButtonLink), ['http://', 'https://']);
    $showSalesPopup = $popupEnabled && ($popupTitle !== '' || $popupMessage !== '');
    $showCountdown = $saleCountdown && optional($saleCountdown->end_at)->isFuture();
    $countdownEndIso = $showCountdown ? optional($saleCountdown->end_at)->toIso8601String() : null;
    $countdownImage = $showCountdown ? $saleCountdown->image_source_url : null;
    $countdownBg = $showCountdown ? ($saleCountdown->bg_color ?: '#111111') : '#111111';
    $countdownText = $showCountdown ? ($saleCountdown->text_color ?: '#FFFFFF') : '#FFFFFF';
    $countdownAccent = $showCountdown ? ($saleCountdown->accent_color ?: '#C8A45D') : '#C8A45D';
    $countdownOpenNewTab = $showCountdown
        ? \Illuminate\Support\Str::startsWith(strtolower((string) $saleCountdown->cta_link), ['http://', 'https://'])
        : false;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $siteName }}</title>

    @if($siteFavicon)
        <link rel="icon" type="image/x-icon" href="{{ $siteFavicon }}">
        <link rel="shortcut icon" type="image/x-icon" href="{{ $siteFavicon }}">
    @endif

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">

    @vite('resources/css/app.css')
    <x-dynamic-styles />
    <style>
        .welcome-header-wrap {
            position: sticky;
            top: 0;
            z-index: 70;
            padding: 0;
        }

        .welcome-announcement {
            width: 100%;
            overflow: hidden;
            border-bottom: 1px solid rgba(200, 164, 93, 0.32);
            background: var(--announcement-bg, #111111);
            color: var(--announcement-text, #f7f2e8);
        }

        .welcome-announcement-track {
            display: flex;
            width: max-content;
            min-width: 100%;
            animation: welcome-announcement-scroll var(--announcement-duration, 20s) linear infinite;
            will-change: transform;
        }

        .welcome-announcement-item {
            min-width: 100vw;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.55rem;
            padding: 0.5rem 1rem;
            font-size: 0.62rem;
            font-weight: 800;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .welcome-announcement-item::before {
            content: "•";
            font-size: 0.7rem;
            opacity: 0.78;
        }

        .welcome-announcement-link,
        .welcome-announcement-text {
            color: inherit;
            text-decoration: none;
        }

        .welcome-announcement-link:hover {
            color: inherit;
            opacity: 0.86;
        }

        @keyframes welcome-announcement-scroll {
            0% {
                transform: translateX(0);
            }
            100% {
                transform: translateX(-33.3333%);
            }
        }

        .welcome-sales-popup {
            position: fixed;
            inset: 0;
            z-index: 120;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            background: rgba(10, 10, 10, 0.58);
            backdrop-filter: blur(2px);
        }

        .welcome-sales-popup.is-open {
            display: flex;
        }

        .welcome-sales-popup-dialog {
            width: min(96vw, 860px);
            border-radius: 1.35rem;
            overflow: hidden;
            border: 1px solid color-mix(in srgb, var(--popup-accent, #c8a45d) 48%, transparent);
            background: linear-gradient(145deg, color-mix(in srgb, var(--popup-bg, #111111) 92%, #ffffff 8%), var(--popup-bg, #111111));
            color: var(--popup-text, #ffffff);
            box-shadow: 0 38px 95px rgba(0, 0, 0, 0.45);
            position: relative;
        }

        .welcome-sales-popup-close {
            position: absolute;
            top: 0.85rem;
            right: 0.85rem;
            width: 2.2rem;
            height: 2.2rem;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.28);
            background: rgba(0, 0, 0, 0.35);
            color: #ffffff;
            font-size: 0.78rem;
            z-index: 2;
        }

        .welcome-sales-popup-grid {
            display: grid;
            grid-template-columns: minmax(0, 0.95fr) minmax(0, 1.05fr);
            min-height: 360px;
        }

        .welcome-sales-popup-media {
            position: relative;
            background: color-mix(in srgb, var(--popup-accent, #c8a45d) 24%, #000000);
        }

        .welcome-sales-popup-media::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(170deg, rgba(0, 0, 0, 0.06) 0%, rgba(0, 0, 0, 0.52) 100%);
        }

        .welcome-sales-popup-media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .welcome-sales-popup-media-fallback {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.2rem;
            text-align: center;
            color: #fff8ea;
            font-size: 0.82rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            line-height: 1.5;
        }

        .welcome-sales-popup-content {
            display: grid;
            align-content: center;
            gap: 0.88rem;
            padding: 1.5rem 1.4rem 1.35rem;
        }

        .welcome-sales-popup-badge {
            display: inline-flex;
            width: fit-content;
            border-radius: 999px;
            border: 1px solid color-mix(in srgb, var(--popup-accent, #c8a45d) 70%, transparent);
            color: var(--popup-accent, #c8a45d);
            background: color-mix(in srgb, var(--popup-accent, #c8a45d) 16%, transparent);
            font-size: 0.58rem;
            font-weight: 800;
            letter-spacing: 0.17em;
            text-transform: uppercase;
            padding: 0.3rem 0.58rem;
        }

        .welcome-sales-popup-title {
            margin: 0;
            color: var(--popup-text, #ffffff);
            font-size: 1.3rem;
            font-weight: 800;
            line-height: 1.24;
            text-transform: capitalize;
        }

        .welcome-sales-popup-message {
            margin: 0;
            color: color-mix(in srgb, var(--popup-text, #ffffff) 88%, #000000 12%);
            font-size: 0.83rem;
            font-weight: 500;
            line-height: 1.68;
        }

        .welcome-sales-popup-price {
            display: inline-flex;
            width: fit-content;
            align-items: center;
            border-radius: 0.85rem;
            border: 1px solid color-mix(in srgb, var(--popup-accent, #c8a45d) 58%, transparent);
            background: color-mix(in srgb, var(--popup-accent, #c8a45d) 18%, transparent);
            color: var(--popup-accent, #c8a45d);
            font-size: 1.08rem;
            font-weight: 800;
            padding: 0.42rem 0.72rem;
        }

        .welcome-sales-popup-actions {
            display: flex;
            align-items: center;
            gap: 0.7rem;
            margin-top: 0.28rem;
        }

        .welcome-sales-popup-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2.5rem;
            border-radius: 999px;
            border: 1px solid var(--popup-accent, #c8a45d);
            background: var(--popup-accent, #c8a45d);
            color: #111111 !important;
            font-size: 0.66rem;
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            padding: 0.58rem 1.1rem;
            text-decoration: none;
            transition: transform 0.18s ease, filter 0.18s ease;
        }

        .welcome-sales-popup-btn:hover {
            color: #111111 !important;
            transform: scale(1.02);
            filter: brightness(0.95);
        }

        .welcome-visually-hidden {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        .welcome-header {
            margin: 0;
            width: 100%;
            padding: 0;
            background: transparent;
            border-radius: 0;
            border: 1px solid rgba(200, 164, 93, 0.28);
            border-left: 0;
            border-right: 0;
            backdrop-filter: blur(8px);
            box-shadow: 0 18px 44px rgba(17, 17, 17, 0.08);
        }

        .welcome-header-inner {
            margin: 0 auto;
            max-width: 80rem;
            display: grid;
            grid-template-columns: auto 1fr;
            align-items: center;
            gap: 1rem;
            padding: 0.62rem 0.72rem;
        }

        .welcome-header-brand {
            display: inline-flex;
            align-items: center;
            gap: 0.65rem;
            min-height: 3.3rem;
        }

        .welcome-header-logo {
            width: auto;
            max-height: 3.1rem;
        }

        .welcome-header-fallback {
            display: inline-flex;
            width: 3rem;
            height: 3rem;
            align-items: center;
            justify-content: center;
            border-radius: 0.85rem;
            border: 1px solid #d8c6a1;
            background: #fbf5e8;
            color: #b68c3a;
        }

        .welcome-header-panel {
            display: grid;
            grid-template-columns: 1fr auto;
            align-items: center;
            gap: 0.8rem;
            min-width: 0;
        }

        .welcome-header-menu {
            display: flex;
            align-items: center;
            justify-self: center;
            gap: 0.4rem;
        }

        .welcome-header-menu-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2.25rem;
            border-radius: 999px;
            padding: 0.5rem 0.9rem;
            border: 1px solid transparent;
            color: #111111;
            background: transparent;
            font-size: 0.58rem;
            font-weight: 800;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            text-decoration: none;
            transition: transform 0.18s ease, background-color 0.18s ease, color 0.18s ease, border-color 0.18s ease;
        }

        .welcome-header-menu-link:hover {
            transform: scale(1.02);
            border-color: #d8c6a1;
            background: #fffaf1;
        }

        .welcome-header-menu-link.active {
            border-color: transparent;
            background: transparent;
            color: #9b7431;
        }

        .welcome-header-actions {
            display: flex;
            justify-self: end;
            gap: 0.6rem;
        }

        .welcome-header-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2.36rem;
            border-radius: 999px;
            padding: 0.56rem 1rem;
            border: 1px solid #111111;
            color: #ffffff !important;
            background: #111111;
            font-size: 0.62rem;
            font-weight: 800;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            text-decoration: none;
            transition: transform 0.18s ease, background-color 0.18s ease, color 0.18s ease, border-color 0.18s ease;
        }

        .welcome-header-btn:hover {
            transform: scale(1.02);
            background: #000000;
        }

        .welcome-header-btn.secondary {
            border-color: #d8c6a1;
            background: #fffaf1;
            color: #1f1f1f !important;
        }

        .welcome-header-btn.secondary:hover {
            background: #f8edd8;
        }

        .welcome-header-toggle {
            display: none;
            width: 2.25rem;
            height: 2.25rem;
            align-items: center;
            justify-content: center;
            border-radius: 0.72rem;
            border: 1px solid #dfcfaf;
            background: #fffaf1;
            color: #111111;
            font-size: 0.82rem;
        }

        .welcome-main > section {
            width: 100%;
            max-width: 100%;
        }

        .welcome-main > section + section {
            margin-top: 1.4rem !important;
        }

        .welcome-main > footer.site-footer {
            margin-top: 1rem !important;
        }

        .welcome-top-banner-section {
            margin-bottom: 1.55rem;
        }

        .welcome-top-banner {
            position: relative;
            overflow: hidden;
            min-height: clamp(16rem, 42vw, 31rem);
            border-radius: 1.5rem;
            border: 1px solid rgba(200, 164, 93, 0.28);
            background: linear-gradient(135deg, #151515 0%, #2c2419 100%);
            box-shadow: 0 28px 74px rgba(17, 17, 17, 0.16);
            isolation: isolate;
        }

        .welcome-top-banner.is-image-only {
            min-height: clamp(13rem, 30vw, 24rem);
        }

        .welcome-top-banner-media {
            position: absolute;
            inset: 0;
        }

        .welcome-top-banner-media::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(17, 17, 17, 0.18) 0%, rgba(17, 17, 17, 0.42) 100%);
        }

        .welcome-top-banner.is-image-text .welcome-top-banner-media::after {
            background:
                linear-gradient(180deg, rgba(17, 17, 17, 0.18) 0%, rgba(17, 17, 17, 0.5) 100%),
                radial-gradient(circle at top, rgba(200, 164, 93, 0.16) 0%, transparent 48%);
        }

        .welcome-top-banner-media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .welcome-top-banner-fallback {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            text-align: center;
            color: #f7ead0;
            font-size: 0.86rem;
            font-weight: 800;
            letter-spacing: 0.24em;
            text-transform: uppercase;
            line-height: 1.7;
        }

        .welcome-top-banner-content {
            position: relative;
            z-index: 1;
            min-height: inherit;
            display: grid;
            place-items: center;
            padding: 1.6rem;
        }

        .welcome-top-banner-content-inner {
            width: min(100%, 44rem);
            display: grid;
            justify-items: center;
            gap: 0.88rem;
            text-align: center;
            padding: 1.35rem 1.2rem;
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 1.4rem;
            background: linear-gradient(180deg, rgba(20, 20, 20, 0.46) 0%, rgba(20, 20, 20, 0.68) 100%);
            backdrop-filter: blur(7px);
            box-shadow: 0 20px 48px rgba(0, 0, 0, 0.18);
        }

        .welcome-top-banner-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            border: 1px solid rgba(200, 164, 93, 0.58);
            background: rgba(200, 164, 93, 0.18);
            color: #f7ead0;
            padding: 0.38rem 0.74rem;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.2em;
            text-transform: uppercase;
        }

        .welcome-top-banner-title {
            margin: 0;
            color: #ffffff;
            font-size: clamp(1.5rem, 3vw, 2.85rem);
            font-weight: 800;
            line-height: 1.12;
            text-wrap: balance;
        }

        .welcome-top-banner-subtitle {
            margin: 0;
            max-width: 44rem;
            color: rgba(255, 248, 234, 0.92);
            font-size: 0.85rem;
            font-weight: 400;
            line-height: 1.8;
        }

        .welcome-top-banner-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 0.7rem;
            margin-top: 0.12rem;
        }

        .welcome-top-banner-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2.6rem;
            border-radius: 999px;
            border: 1px solid #c8a45d;
            background: #c8a45d;
            color: #111111 !important;
            text-decoration: none;
            padding: 0.7rem 1.1rem;
            font-size: 0.64rem;
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            transition: transform 0.18s ease, filter 0.18s ease, background-color 0.18s ease, color 0.18s ease;
        }

        .welcome-top-banner-btn:hover {
            color: #111111;
            transform: translateY(-1px);
            filter: brightness(0.95);
        }

        .welcome-top-banner-btn.secondary {
            border-color: rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff !important;
        }

        .welcome-top-banner-btn.secondary:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.16);
        }

        .welcome-countdown-section {
            margin-bottom: 0;
        }

        .welcome-countdown-shell {
            position: relative;
            overflow: hidden;
            border-radius: 1.35rem;
            border: 1px solid color-mix(in srgb, var(--countdown-accent, #c8a45d) 48%, transparent);
            background: linear-gradient(
                145deg,
                color-mix(in srgb, var(--countdown-bg, #111111) 93%, #ffffff 7%),
                var(--countdown-bg, #111111)
            );
            color: var(--countdown-text, #ffffff);
            box-shadow: 0 28px 70px rgba(17, 17, 17, 0.16);
        }

        .welcome-countdown-shell::after {
            content: "";
            position: absolute;
            width: 16rem;
            height: 16rem;
            right: -3.5rem;
            top: -5rem;
            border-radius: 999px;
            background: radial-gradient(circle, color-mix(in srgb, var(--countdown-accent, #c8a45d) 38%, transparent) 0%, transparent 72%);
            pointer-events: none;
        }

        .welcome-countdown-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(0, 0.9fr);
        }

        .welcome-countdown-content {
            position: relative;
            z-index: 1;
            display: grid;
            align-content: center;
            gap: 0.9rem;
            padding: 1.4rem 1.3rem;
        }

        .welcome-countdown-badge {
            display: inline-flex;
            width: fit-content;
            border-radius: 999px;
            border: 1px solid color-mix(in srgb, var(--countdown-accent, #c8a45d) 68%, transparent);
            background: color-mix(in srgb, var(--countdown-accent, #c8a45d) 18%, transparent);
            color: color-mix(in srgb, var(--countdown-text, #ffffff) 92%, #000000 8%);
            font-size: 0.58rem;
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            padding: 0.34rem 0.62rem;
        }

        .welcome-countdown-title {
            margin: 0;
            color: var(--countdown-text, #ffffff);
            font-size: 1.32rem;
            font-weight: 800;
            line-height: 1.25;
            text-wrap: balance;
        }

        .welcome-countdown-subtitle {
            margin: 0;
            color: color-mix(in srgb, var(--countdown-text, #ffffff) 90%, #000000 10%);
            font-size: 0.85rem;
            font-weight: 400;
            line-height: 1.7;
            max-width: 58ch;
        }

        .welcome-countdown-offer {
            display: inline-flex;
            width: fit-content;
            border-radius: 0.8rem;
            border: 1px solid color-mix(in srgb, var(--countdown-accent, #c8a45d) 62%, transparent);
            background: color-mix(in srgb, var(--countdown-accent, #c8a45d) 16%, transparent);
            color: color-mix(in srgb, var(--countdown-text, #ffffff) 96%, #000000 4%);
            font-size: 0.74rem;
            font-weight: 700;
            padding: 0.46rem 0.65rem;
        }

        .welcome-countdown-timer {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.52rem;
        }

        .welcome-countdown-cell {
            border-radius: 0.78rem;
            border: 1px solid color-mix(in srgb, var(--countdown-text, #ffffff) 22%, transparent);
            background: color-mix(in srgb, #ffffff 10%, transparent);
            display: grid;
            justify-items: center;
            gap: 0.2rem;
            padding: 0.5rem 0.32rem;
            min-width: 0;
        }

        .welcome-countdown-value {
            color: var(--countdown-text, #ffffff);
            font-size: 1.1rem;
            font-weight: 800;
            line-height: 1.05;
            font-variant-numeric: tabular-nums;
        }

        .welcome-countdown-label {
            color: color-mix(in srgb, var(--countdown-text, #ffffff) 80%, #000000 20%);
            font-size: 0.52rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }

        .welcome-countdown-caption {
            margin: -0.1rem 0 0;
            color: color-mix(in srgb, var(--countdown-text, #ffffff) 82%, #000000 18%);
            font-size: 0.64rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
        }

        .welcome-countdown-action {
            margin-top: 0.14rem;
        }

        .welcome-countdown-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2.45rem;
            border-radius: 999px;
            border: 1px solid var(--countdown-accent, #c8a45d);
            background: var(--countdown-accent, #c8a45d);
            color: #111111 !important;
            font-size: 0.64rem;
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            text-decoration: none;
            padding: 0.6rem 1.1rem;
            transition: transform 0.18s ease, filter 0.18s ease;
        }

        .welcome-countdown-btn:hover {
            color: #111111;
            transform: scale(1.02);
            filter: brightness(0.94);
        }

        .welcome-countdown-media {
            position: relative;
            min-height: 300px;
            background: color-mix(in srgb, var(--countdown-accent, #c8a45d) 26%, #000000);
        }

        .welcome-countdown-media::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(0, 0, 0, 0.06) 0%, rgba(0, 0, 0, 0.5) 100%);
        }

        .welcome-countdown-media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .welcome-countdown-media-fallback {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            text-align: center;
            color: #fff8ea;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            line-height: 1.45;
        }

        .welcome-countdown-shell.is-ended .welcome-countdown-cell {
            opacity: 0.72;
        }

        .welcome-swiper-shell {
            border: 0;
            border-radius: 0;
            background: transparent;
            box-shadow: none;
            padding: 0;
        }

        .welcome-text-section {
            margin-top: 0;
            margin-bottom: 0;
        }

        .welcome-text-card {
            position: relative;
            overflow: hidden;
            border-radius: 1.45rem;
            border: 1px solid rgba(200, 164, 93, 0.24);
            background:
                radial-gradient(circle at top left, rgba(200, 164, 93, 0.12) 0%, transparent 32%),
                linear-gradient(135deg, #fffdf9 0%, #f8f2e8 100%);
            box-shadow: 0 24px 62px rgba(17, 17, 17, 0.09);
            padding: 1.4rem 1.3rem;
        }

        .welcome-text-card::after {
            content: "";
            position: absolute;
            right: -3rem;
            bottom: -4rem;
            width: 12rem;
            height: 12rem;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(17, 17, 17, 0.05) 0%, transparent 72%);
            pointer-events: none;
        }

        .welcome-text-card-inner {
            position: relative;
            z-index: 1;
            display: grid;
            gap: 0.95rem;
            justify-items: center;
            text-align: center;
            max-width: 56rem;
            margin: 0 auto;
        }

        .welcome-text-rich {
            color: #111111;
            font-size: clamp(1.12rem, 2.3vw, 1.8rem);
            font-weight: 700;
            line-height: 1.45;
            text-wrap: balance;
        }

        .welcome-text-rich h1,
        .welcome-text-rich h2,
        .welcome-text-rich h3,
        .welcome-text-rich h4,
        .welcome-text-rich h5,
        .welcome-text-rich h6 {
            margin: 0;
            color: #111111;
            font-size: inherit;
            font-weight: 800;
            line-height: inherit;
        }

        .welcome-text-rich p {
            margin: 0;
        }

        .welcome-text-rich a {
            color: #9b7431;
            text-decoration: underline;
            text-underline-offset: 0.18em;
        }

        .welcome-text-rich strong {
            font-weight: 800;
        }

        .welcome-text-subtext {
            margin: 0;
            max-width: 46rem;
            color: #5f5647;
            font-size: 0.85rem;
            font-weight: 400;
            line-height: 1.8;
        }

        .welcome-text-action {
            display: flex;
            justify-content: center;
            margin-top: 0.12rem;
        }

        .welcome-text-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2.55rem;
            border-radius: 999px;
            border: 1px solid #111111;
            background: #111111;
            color: #ffffff !important;
            text-decoration: none;
            padding: 0.7rem 1.15rem;
            font-size: 0.64rem;
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            transition: transform 0.18s ease, background-color 0.18s ease;
        }

        .welcome-text-btn:hover {
            color: #ffffff;
            background: #000000;
            transform: translateY(-1px);
        }

        .welcome-swiper-head {
            display: flex;
            align-items: flex-start;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 1rem;
            text-align: center;
        }

        .welcome-swiper-head > div {
            width: 100%;
        }

        .welcome-swiper-title {
            margin: 0;
            color: #111111;
            font-size: 1rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }

        .welcome-swiper-note {
            margin: 0.35rem 0 0;
            color: #6e675a;
            font-size: 0.85rem;
            font-weight: 400;
        }

        .welcome-swiper-side-control {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.15rem;
            height: 2.15rem;
            border-radius: 999px;
            border: 1px solid rgba(200, 164, 93, 0.45);
            background: #fff8e9;
            color: #8b6728;
            font-size: 0.76rem;
            transition: transform 0.18s ease, background-color 0.18s ease, color 0.18s ease, border-color 0.18s ease;
            position: absolute;
            top: 50%;
            z-index: 4;
            transform: translateY(-50%);
        }

        .welcome-swiper-side-control:hover {
            background: #111111;
            color: #ffffff;
            border-color: #111111;
            transform: translateY(-50%) scale(1.03);
        }

        .welcome-swiper {
            width: 100%;
            overflow: hidden;
            position: relative;
            padding: 0;
        }

        .welcome-swiper-frame {
            position: relative;
            padding: 0 2.75rem;
        }

        .welcome-swiper-prev {
            left: 0;
        }

        .welcome-swiper-next {
            right: 0;
        }

        .welcome-swiper .swiper-wrapper {
            align-items: stretch;
        }

        .welcome-swiper .swiper-slide {
            height: auto !important;
            display: flex;
        }

        .welcome-swiper-card {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            border: 1px solid rgba(200, 164, 93, 0.22);
            border-radius: 1.3rem;
            background: #ffffff;
            overflow: hidden;
            box-shadow: none;
        }

        .welcome-swiper-image-wrap {
            width: 100%;
            aspect-ratio: 16 / 10;
            background: #efe2c8;
        }

        .welcome-swiper-image-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .welcome-swiper-image-fallback {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9b7431;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            background: linear-gradient(135deg, #f7ecda, #fff8ea);
        }

        .welcome-swiper-body {
            display: grid;
            gap: 0.62rem;
            padding: 0.96rem;
        }

        .welcome-swiper-badge {
            display: inline-flex;
            width: fit-content;
            align-items: center;
            border-radius: 999px;
            border: 1px solid rgba(200, 164, 93, 0.48);
            background: #fff5df;
            color: #8d6728;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            padding: 0.36rem 0.62rem;
        }

        .welcome-swiper-card-title {
            margin: 0;
            color: #171717;
            font-size: 0.95rem;
            font-weight: 700;
            line-height: 1.4;
            min-height: 2.65rem;
        }

        .welcome-swiper-prices {
            display: grid;
            gap: 0.4rem;
        }

        .welcome-swiper-price-row {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 0.75rem;
        }

        .welcome-swiper-price-label {
            color: #7f7668;
            font-size: 0.62rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }

        .welcome-swiper-price-regular {
            color: #857a6a;
            font-size: 0.88rem;
            font-weight: 700;
            text-decoration: line-through;
        }

        .welcome-swiper-price-sale {
            color: #111111;
            font-size: 1.3rem;
            font-weight: 800;
            line-height: 1.1;
        }

        .welcome-swiper-btn {
            margin-top: 0.35rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2.35rem;
            border-radius: 999px;
            border: 1px solid #111111;
            background: #111111;
            color: #ffffff !important;
            font-size: 0.66rem;
            font-weight: 800;
            letter-spacing: 0.21em;
            text-transform: uppercase;
            transition: transform 0.18s ease, background-color 0.18s ease;
        }

        .welcome-swiper-btn:hover {
            background: #000000;
            transform: scale(1.02);
        }

        .welcome-swiper-pagination {
            margin-top: 0.95rem;
            position: static !important;
            text-align: center;
        }

        .welcome-swiper-pagination .swiper-pagination-bullet {
            width: 0.5rem;
            height: 0.5rem;
            background: rgba(17, 17, 17, 0.24);
            opacity: 1;
        }

        .welcome-swiper-pagination .swiper-pagination-bullet-active {
            background: #111111;
            transform: scale(1.2);
        }

        .welcome-services-cta {
            margin-top: 1.15rem;
            display: flex;
            justify-content: center;
        }

        .welcome-services-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2.65rem;
            border-radius: 999px;
            border: 1px solid #111111;
            background: #111111;
            color: #ffffff !important;
            padding: 0.65rem 1.2rem;
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            transition: transform 0.18s ease, background-color 0.18s ease;
        }

        .welcome-services-btn:hover {
            background: #000000;
            transform: scale(1.02);
        }

        .review-showcase {
            margin-top: 0;
            padding-top: 0;
        }

        .review-showcase-head {
            text-align: center;
            margin-bottom: 1rem;
        }

        .review-showcase-title {
            margin: 0;
            color: #111111;
            font-size: 1rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }

        .review-showcase-note {
            margin: 0.35rem 0 0;
            color: #6e675a;
            font-size: 0.85rem;
            font-weight: 400;
        }

        .review-swiper-shell {
            position: relative;
            padding: 0 2.75rem;
        }

        .review-swiper {
            width: 100%;
            overflow: hidden;
        }

        .review-swiper .swiper-wrapper {
            align-items: stretch;
        }

        .review-swiper .swiper-slide {
            height: auto !important;
            display: flex;
        }

        .review-card {
            width: 100%;
            display: flex;
            flex-direction: column;
            border: 1px solid rgba(200, 164, 93, 0.24);
            border-radius: 1.25rem;
            background: #ffffff;
            overflow: hidden;
            box-shadow: 0 16px 38px rgba(17, 17, 17, 0.06);
        }

        .review-media-button {
            position: relative;
            width: 100%;
            border: 0;
            padding: 0;
            margin: 0;
            aspect-ratio: 16 / 10;
            cursor: zoom-in;
            background: #f2e8d4;
            overflow: hidden;
        }

        .review-media-button img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.26s ease;
        }

        .review-media-button:hover img {
            transform: scale(1.03);
        }

        .review-media-fallback {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9b7431;
            font-size: 0.62rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            background: linear-gradient(135deg, #f7ecda, #fff8ea);
        }

        .review-card-body {
            display: grid;
            gap: 0.85rem;
            padding: 0.9rem;
            flex: 1;
        }

        .review-card-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.65rem;
        }

        .review-customer {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            min-width: 0;
        }

        .review-country {
            width: 1.75rem;
            height: 1.75rem;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #e5d6b7;
            background: #fff6e5;
            color: #8b6728;
            font-size: 0.57rem;
            font-weight: 800;
            letter-spacing: 0.06em;
        }

        .review-name {
            color: #171717;
            font-size: 0.9rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .review-verified-pill {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            border: 1px solid #d6eadf;
            background: #eef8f2;
            color: #157347;
            font-size: 0.62rem;
            font-weight: 700;
            gap: 0.34rem;
            padding: 0.3rem 0.58rem;
            white-space: nowrap;
        }

        .review-card-headline {
            margin: 0;
            color: #111111;
            font-size: 1.02rem;
            font-weight: 700;
            line-height: 1.4;
        }

        .review-card-text {
            margin: 0;
            color: #3f3f46;
            font-size: 0.83rem;
            line-height: 1.78;
        }

        .review-product-box {
            margin-top: 0.15rem;
            border: 1px solid #ece4d5;
            background: #faf7f2;
            border-radius: 0.95rem;
            padding: 0.64rem 0.72rem;
        }

        .review-product-note {
            margin: 0;
            color: #71717a;
            font-size: 0.56rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .review-product-link {
            margin-top: 0.25rem;
            display: inline-block;
            color: #111111;
            font-size: 0.78rem;
            font-weight: 700;
            text-decoration: underline;
            text-decoration-color: #c8a45d;
            text-underline-offset: 0.18rem;
            text-decoration-thickness: 2px;
        }

        .review-swiper-control {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.15rem;
            height: 2.15rem;
            border-radius: 999px;
            border: 1px solid rgba(17, 17, 17, 0.24);
            background: #ffffff;
            color: #111111;
            font-size: 0.76rem;
            transition: transform 0.18s ease, background-color 0.18s ease, color 0.18s ease;
            position: absolute;
            top: 50%;
            z-index: 4;
            transform: translateY(-50%);
        }

        .review-swiper-control:hover {
            background: #111111;
            color: #ffffff;
            transform: translateY(-50%) scale(1.03);
        }

        .review-swiper-prev {
            left: 0;
        }

        .review-swiper-next {
            right: 0;
        }

        .review-swiper-footer {
            margin-top: 0.95rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.78rem;
        }

        .review-swiper-fraction {
            color: #111111;
            font-size: 0.94rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            min-width: 2.9rem;
            text-align: center;
        }

        .review-image-modal {
            position: fixed;
            inset: 0;
            background: rgba(12, 12, 12, 0.82);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1400;
            padding: 1rem;
        }

        .review-image-modal.is-open {
            display: flex;
        }

        .review-image-modal-dialog {
            width: min(96vw, 1080px);
            max-height: 92vh;
            border-radius: 1rem;
            background: #ffffff;
            overflow: hidden;
            box-shadow: 0 36px 80px rgba(0, 0, 0, 0.35);
            position: relative;
        }

        .review-image-modal-close {
            position: absolute;
            top: 0.65rem;
            right: 0.65rem;
            width: 2rem;
            height: 2rem;
            border: 0;
            border-radius: 999px;
            background: rgba(17, 17, 17, 0.88);
            color: #ffffff;
            font-size: 0.72rem;
            z-index: 2;
        }

        .review-image-modal-media {
            width: 100%;
            max-height: 78vh;
            display: block;
            object-fit: contain;
            background: #f8f8f8;
        }

        .review-image-modal-caption {
            border-top: 1px solid #efefef;
            padding: 0.65rem 0.9rem;
            color: #3f3f46;
            font-size: 0.82rem;
            font-weight: 600;
            text-align: center;
        }

        @media (max-width: 767px) {
            .welcome-header-wrap {
                padding: 0;
            }

            .welcome-announcement-item {
                font-size: 0.56rem;
                letter-spacing: 0.14em;
                padding: 0.44rem 0.8rem;
            }

            .welcome-sales-popup {
                padding: 0.75rem;
            }

            .welcome-sales-popup-dialog {
                width: 100%;
            }

            .welcome-sales-popup-grid {
                grid-template-columns: 1fr;
                min-height: auto;
            }

            .welcome-sales-popup-media {
                min-height: 180px;
            }

            .welcome-sales-popup-content {
                padding: 1.1rem 1rem 1rem;
                gap: 0.72rem;
            }

            .welcome-sales-popup-title {
                font-size: 1.02rem;
            }

            .welcome-sales-popup-message {
                font-size: 0.75rem;
                line-height: 1.58;
            }

            .welcome-sales-popup-price {
                font-size: 0.92rem;
            }

            .welcome-sales-popup-btn {
                width: 100%;
                min-height: 2.35rem;
                font-size: 0.6rem;
            }

            .welcome-header-inner {
                display: flex;
                flex-wrap: wrap;
                gap: 0.5rem;
                padding: 0.54rem 0.58rem;
            }

            .welcome-header-logo {
                max-height: 2.6rem;
            }

            .welcome-header-fallback {
                width: 2.55rem;
                height: 2.55rem;
            }

            .welcome-header-toggle {
                display: inline-flex;
            }

            .welcome-header-panel {
                width: 100%;
                display: none;
                grid-template-columns: 1fr;
                align-items: stretch;
                gap: 0.5rem;
                border-top: 1px solid #eee5d3;
                margin-top: 0.28rem;
                padding-top: 0.62rem;
            }

            .welcome-header-panel.is-open {
                display: grid;
            }

            .welcome-header-menu {
                width: 100%;
                justify-self: stretch;
                flex-direction: column;
                align-items: stretch;
                gap: 0.4rem;
            }

            .welcome-header-menu-link {
                width: 100%;
                min-height: 2.2rem;
                font-size: 0.56rem;
                letter-spacing: 0.18em;
            }

            .welcome-header-actions {
                width: 100%;
                justify-self: stretch;
                display: flex;
                flex-direction: column;
                gap: 0.45rem;
            }

            .welcome-header-btn {
                width: 100%;
                min-height: 2.32rem;
                font-size: 0.58rem;
                letter-spacing: 0.18em;
            }

            .welcome-main > section + section {
                margin-top: 1.2rem !important;
            }

            .welcome-top-banner-section {
                margin-bottom: 0;
            }

            .welcome-top-banner {
                min-height: 14rem;
                border-radius: 1.05rem;
            }

            .welcome-top-banner.is-image-only {
                min-height: 11.5rem;
            }

            .welcome-top-banner-content {
                padding: 0.9rem;
            }

            .welcome-top-banner-content-inner {
                width: 100%;
                gap: 0.68rem;
                padding: 0.95rem 0.85rem;
                border-radius: 1rem;
            }

            .welcome-top-banner-badge {
                font-size: 0.54rem;
                letter-spacing: 0.17em;
            }

            .welcome-top-banner-title {
                font-size: 1.18rem;
            }

            .welcome-top-banner-subtitle {
                font-size: 0.85rem;
                font-weight: 400;
                line-height: 1.62;
            }

            .welcome-top-banner-actions {
                width: 100%;
                flex-direction: column;
                align-items: stretch;
            }

            .welcome-top-banner-btn {
                width: 100%;
                min-height: 2.35rem;
                font-size: 0.58rem;
            }

            .welcome-countdown-section {
                margin-bottom: 0;
            }

            .welcome-countdown-shell {
                border-radius: 1rem;
            }

            .welcome-countdown-grid {
                grid-template-columns: 1fr;
            }

            .welcome-countdown-content {
                gap: 0.74rem;
                padding: 1rem;
            }

            .welcome-countdown-title {
                font-size: 1.02rem;
            }

            .welcome-countdown-subtitle {
                font-size: 0.85rem;
                font-weight: 400;
                line-height: 1.62;
            }

            .welcome-countdown-offer {
                font-size: 0.66rem;
            }

            .welcome-countdown-timer {
                gap: 0.4rem;
            }

            .welcome-countdown-cell {
                border-radius: 0.66rem;
                padding: 0.44rem 0.22rem;
            }

            .welcome-countdown-value {
                font-size: 0.88rem;
            }

            .welcome-countdown-label {
                font-size: 0.48rem;
            }

            .welcome-countdown-caption {
                font-size: 0.54rem;
            }

            .welcome-countdown-btn {
                width: 100%;
                min-height: 2.3rem;
                font-size: 0.58rem;
            }

            .welcome-countdown-media {
                min-height: 190px;
                order: -1;
            }

            .welcome-swiper-shell {
                padding: 0;
            }

            .welcome-text-section {
                margin-top: 0;
                margin-bottom: 0;
            }

            .welcome-text-card {
                border-radius: 1rem;
                padding: 1rem 0.9rem;
            }

            .welcome-text-card-inner {
                gap: 0.75rem;
            }

            .welcome-text-rich {
                font-size: 0.98rem;
                line-height: 1.55;
            }

            .welcome-text-subtext {
                font-size: 0.85rem;
                font-weight: 400;
                line-height: 1.65;
            }

            .welcome-text-action {
                width: 100%;
            }

            .welcome-text-btn {
                width: 100%;
                min-height: 2.35rem;
                font-size: 0.58rem;
            }

            .welcome-swiper-title {
                font-size: 1rem;
            }

            .welcome-swiper-note {
                font-size: 0.85rem;
                font-weight: 400;
            }

            .welcome-swiper-body {
                padding: 0.8rem;
            }

            .welcome-swiper {
                padding: 0;
            }

            .welcome-swiper-frame {
                padding: 0 2.15rem;
            }

            .welcome-swiper-side-control {
                width: 1.9rem;
                height: 1.9rem;
                font-size: 0.66rem;
            }

            .review-showcase {
                margin-top: 0;
                padding-top: 0;
            }

            .review-showcase-title {
                font-size: 1rem;
            }

            .review-showcase-note {
                font-size: 0.85rem;
                font-weight: 400;
            }

            .review-swiper-shell {
                padding: 0 2.15rem;
            }

            .review-swiper-control {
                width: 1.9rem;
                height: 1.9rem;
                font-size: 0.66rem;
            }

            .review-card-body {
                padding: 0.78rem;
                gap: 0.72rem;
            }

            .review-name {
                font-size: 0.82rem;
            }

            .review-card-headline {
                font-size: 0.92rem;
            }

            .review-card-text {
                font-size: 0.74rem;
                line-height: 1.64;
            }

            .review-image-modal-dialog {
                width: 100%;
            }
        }

        @media (min-width: 768px) {
            .welcome-header-panel {
                display: grid !important;
            }

        }
        @media(max-width: 768px){
            .welcome-text-rich {
        font-size: 1.5rem !important;
        line-height: 1.30;
    }
        }
    </style>
</head>
<body class="min-h-screen text-stone-950 antialiased">
    <div class="pointer-events-none fixed inset-0 overflow-hidden">
        <div class="absolute left-[-10%] top-[-10%] h-96 w-96 rounded-full bg-[#c8a45d]/20 blur-3xl"></div>
        <div class="absolute right-[-8%] top-1/4 h-80 w-80 rounded-full bg-black/8 blur-3xl"></div>
        <div class="absolute bottom-[-12%] left-1/3 h-96 w-96 rounded-full bg-[#c8a45d]/10 blur-3xl"></div>
    </div>

    <div class="relative">
        <x-public-header
            :site-name="$siteName"
            :site-logo="$siteLogo"
            active="home"
            :primary-label="$headerRegisterLabel"
            :primary-link="$headerRegisterLink"
            :secondary-label="$secondaryLabel"
            :secondary-link="$secondaryLink"
            :show-announcement="$showAnnouncement"
            :announcement-text="$announcementText"
            :announcement-link="$announcementLink"
            :announcement-bg-color="$announcementBgColor"
            :announcement-text-color="$announcementTextColor"
            :announcement-speed="$announcementSpeed"
        />

        <main class="welcome-main mx-auto max-w-7xl px-4 pb-4 pt-4 sm:px-6 lg:px-8 lg:pb-4">
            @if($showTopBanner)
                <section class="welcome-top-banner-section">
                    <div class="welcome-top-banner {{ $bannerMode === 'image_only' ? 'is-image-only' : 'is-image-text' }}">
                        <div class="welcome-top-banner-media">
                            @if($bannerDisplayImageSource !== '')
                                <picture>
                                    @if($bannerMobileImageSource !== '')
                                        <source media="(max-width: 767px)" srcset="{{ $bannerMobileImageSource }}">
                                    @endif
                                    <img src="{{ $bannerDisplayImageSource }}" alt="{{ $welcomeTitle !== '' ? $welcomeTitle : $siteName }}" loading="eager" fetchpriority="high">
                                </picture>
                            @else
                                <div class="welcome-top-banner-fallback">
                                    {{ $siteName }}<br>Hero Banner
                                </div>
                            @endif
                        </div>

                        @if($bannerMode === 'image_text' && $bannerHasText)
                            <div class="welcome-top-banner-content">
                                <div class="welcome-top-banner-content-inner">
                                    @if($welcomeBadge !== '')
                                        <span class="welcome-top-banner-badge">{{ $welcomeBadge }}</span>
                                    @endif

                                    @if($welcomeTitle !== '')
                                        <h1 class="welcome-top-banner-title">{{ $welcomeTitle }}</h1>
                                    @endif

                                    @if($welcomeSubtitle !== '')
                                        <p class="welcome-top-banner-subtitle">{!! nl2br(e($welcomeSubtitle)) !!}</p>
                                    @endif

                                    @if(trim((string) $primaryLabel) !== '' || trim((string) $secondaryLabel) !== '')
                                        <div class="welcome-top-banner-actions">
                                            @if(trim((string) $primaryLabel) !== '')
                                                <a
                                                    href="{{ $primaryLink ?: '#' }}"
                                                    class="welcome-top-banner-btn"
                                                    @if($bannerPrimaryOpenNewTab) target="_blank" rel="noopener noreferrer" @endif
                                                >
                                                    {{ $primaryLabel }}
                                                </a>
                                            @endif

                                            @if(trim((string) $secondaryLabel) !== '')
                                                <a
                                                    href="{{ $secondaryLink ?: '#' }}"
                                                    class="welcome-top-banner-btn secondary"
                                                    @if($bannerSecondaryOpenNewTab) rel="noopener noreferrer" @endif
                                                >
                                                    {{ $secondaryLabel }}
                                                </a>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </section>
            @endif

            @if($showCountdown)
                <section class="welcome-countdown-section">
                    <div
                        class="welcome-countdown-shell"
                        data-sale-countdown
                        data-countdown-end="{{ $countdownEndIso }}"
                        style="--countdown-bg: {{ $countdownBg }}; --countdown-text: {{ $countdownText }}; --countdown-accent: {{ $countdownAccent }};"
                    >
                        <div class="welcome-countdown-grid">
                            <div class="welcome-countdown-content">
                                @if($saleCountdown->badge_text)
                                    <span class="welcome-countdown-badge">{{ $saleCountdown->badge_text }}</span>
                                @endif

                                <h2 class="welcome-countdown-title">{{ $saleCountdown->title }}</h2>

                                @if($saleCountdown->subtitle)
                                    <p class="welcome-countdown-subtitle">{!! nl2br(e($saleCountdown->subtitle)) !!}</p>
                                @endif

                                @if($saleCountdown->offer_text)
                                    <div class="welcome-countdown-offer">{{ $saleCountdown->offer_text }}</div>
                                @endif

                                <div class="welcome-countdown-timer" role="timer" aria-live="polite" aria-atomic="true">
                                    <div class="welcome-countdown-cell">
                                        <span class="welcome-countdown-value" data-countdown-days>00</span>
                                        <span class="welcome-countdown-label">Days</span>
                                    </div>
                                    <div class="welcome-countdown-cell">
                                        <span class="welcome-countdown-value" data-countdown-hours>00</span>
                                        <span class="welcome-countdown-label">Hours</span>
                                    </div>
                                    <div class="welcome-countdown-cell">
                                        <span class="welcome-countdown-value" data-countdown-minutes>00</span>
                                        <span class="welcome-countdown-label">Minutes</span>
                                    </div>
                                    <div class="welcome-countdown-cell">
                                        <span class="welcome-countdown-value" data-countdown-seconds>00</span>
                                        <span class="welcome-countdown-label">Seconds</span>
                                    </div>
                                </div>

                                <p class="welcome-countdown-caption">sale ends in</p>

                                <div class="welcome-countdown-action">
                                    <a
                                        href="{{ $saleCountdown->cta_link ?: '#' }}"
                                        class="welcome-countdown-btn"
                                        @if($countdownOpenNewTab) target="_blank" rel="noopener noreferrer" @endif
                                    >
                                        {{ $saleCountdown->cta_label ?: 'Book Now' }}
                                    </a>
                                </div>
                            </div>

                            <div class="welcome-countdown-media">
                                @if($countdownImage)
                                    <img src="{{ $countdownImage }}" alt="{{ $saleCountdown->title }}" loading="lazy">
                                @else
                                    <div class="welcome-countdown-media-fallback">
                                        {{ $siteName }}<br>Sales Art
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </section>
            @endif

            @if($sliderProducts->isNotEmpty())
                <section class="welcome-swiper-shell">
                    <div class="welcome-swiper-head">
                        <div>
                            <h2 class="welcome-swiper-title">Full Service Packages</h2>
                            <p class="welcome-swiper-note">Professional packages crafted for faster hiring outcomes.</p>
                        </div>
                    </div>

                    <div class="welcome-swiper-frame">
                        <button type="button" class="welcome-swiper-side-control welcome-swiper-prev" aria-label="Previous products">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <div class="swiper welcome-swiper" data-welcome-swiper>
                            <div class="swiper-wrapper">
                                @foreach($sliderProducts as $product)
                                    @php
                                        $imageUrl = $product->image_source_url;
                                    @endphp
                                    <article class="swiper-slide">
                                        <div class="welcome-swiper-card">
                                            <div class="welcome-swiper-image-wrap">
                                                @if($imageUrl)
                                                    <img src="{{ $imageUrl }}" alt="{{ $product->title }}" loading="lazy">
                                                @else
                                                    <div class="welcome-swiper-image-fallback">No Image</div>
                                                @endif
                                            </div>

                                            <div class="welcome-swiper-body">
                                                <span class="welcome-swiper-badge">{{ $product->badge_text ?: 'ONLY ONE SPOT LEFT' }}</span>

                                                <h3 class="welcome-swiper-card-title">{{ $product->title }}</h3>

                                                <div class="welcome-swiper-prices">
                                                    @if(!is_null($product->regular_price))
                                                        <div class="welcome-swiper-price-row">
                                                            <span class="welcome-swiper-price-label">Regular Price</span>
                                                            <span class="welcome-swiper-price-regular">${{ number_format((float) $product->regular_price, 2) }}</span>
                                                        </div>
                                                    @endif
                                                    <div class="welcome-swiper-price-row">
                                                        <span class="welcome-swiper-price-label">Sale Price</span>
                                                        <span class="welcome-swiper-price-sale">${{ number_format((float) $product->sale_price, 2) }}</span>
                                                    </div>
                                                </div>

                                                <a href="{{ $product->cta_link ?: '#' }}" class="welcome-swiper-btn" target="_blank" rel="noopener noreferrer">
                                                    {{ $product->cta_label ?: 'Buy Now' }}
                                                </a>
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                            <div class="welcome-swiper-pagination"></div>
                        </div>
                        <button type="button" class="welcome-swiper-side-control welcome-swiper-next" aria-label="Next products">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>

                    <div class="welcome-services-cta">
                        <a href="{{ $servicesButtonLink ?: '#' }}" class="welcome-services-btn">
                            {{ $servicesButtonLabel ?: 'View All Services' }}
                        </a>
                    </div>
                </section>
            @else
                <section class="rounded-[1.6rem] border border-dashed border-[#d8c6a1] bg-[#fffaf1] p-8 text-center text-stone-700">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-[#9b7431]">Packages</p>
                    <p class="mt-2 text-base">No product cards are active right now.</p>
                </section>
            @endif

            @if($showTextSection)
                <section class="welcome-text-section">
                    <div class="welcome-text-card">
                        <div class="welcome-text-card-inner">
                            @if($textSectionRichText !== '')
                                <div class="welcome-text-rich">
                                    @if(str_contains($textSectionRichText, '<'))
                                        {!! $textSectionRichText !!}
                                    @else
                                        {!! nl2br(e($textSectionRichText)) !!}
                                    @endif
                                </div>
                            @endif

                            @if($textSectionSubtext !== '')
                                <p class="welcome-text-subtext">{!! nl2br(e($textSectionSubtext)) !!}</p>
                            @endif

                            @if($textSectionButtonLabel !== '')
                                <div class="welcome-text-action">
                                    <a
                                        href="{{ $textSectionButtonLink !== '' ? $textSectionButtonLink : '#' }}"
                                        class="welcome-text-btn"
                                        @if($textSectionButtonOpenNewTab) target="_blank" rel="noopener noreferrer" @endif
                                    >
                                        {{ $textSectionButtonLabel }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </section>
            @endif

            @if($sliderReviews->isNotEmpty())
                <section class="review-showcase">
                    <div class="review-showcase-head">
                        <h2 class="review-showcase-title">BE OUR NEXT WFH SUCCESS STORY</h2>
                        <p class="review-showcase-note">What our verified customers are saying.</p>
                    </div>

                    <div class="review-swiper-shell">
                        <button type="button" class="review-swiper-control review-swiper-prev" aria-label="Previous reviews">
                            <i class="fas fa-chevron-left"></i>
                        </button>

                        <div
                            class="swiper review-swiper"
                            data-review-swiper
                            data-review-count="{{ $sliderReviews->count() }}"
                        >
                            <div class="swiper-wrapper">
                                @foreach($sliderReviews as $review)
                                    @php
                                        $imageUrl = $review->image_source_url;
                                    @endphp
                                    <article class="swiper-slide">
                                        <div class="review-card">
                                            <button type="button" class="review-media-button" data-review-image="{{ $imageUrl }}" data-review-caption="{{ $review->customer_name }}">
                                                @if($imageUrl)
                                                    <img src="{{ $imageUrl }}" alt="Review image for {{ $review->customer_name }}" loading="lazy">
                                                @else
                                                    <span class="review-media-fallback">Review Image</span>
                                                @endif
                                            </button>

                                            <div class="review-card-body">
                                                <div class="review-card-meta">
                                                    <div class="review-customer">
                                                        <span class="review-country">{{ $review->country_label ?: 'US' }}</span>
                                                        <span class="review-name">{{ $review->customer_name }}</span>
                                                    </div>

                                                    @if($review->is_verified)
                                                        <span class="review-verified-pill">
                                                            <i class="fas fa-check-circle"></i>
                                                            Verified customer
                                                        </span>
                                                    @endif
                                                </div>

                                                <h3 class="review-card-headline">{{ $review->headline }}</h3>
                                                <p class="review-card-text">{{ $review->review_text }}</p>

                                                @if($review->product_name)
                                                    <div class="review-product-box">
                                                        <p class="review-product-note">{{ $review->customer_name }} uses...</p>
                                                        @if($review->product_link)
                                                            <a href="{{ $review->product_link }}" target="_blank" rel="noopener noreferrer" class="review-product-link">
                                                                {{ $review->product_name }}
                                                            </a>
                                                        @else
                                                            <span class="review-product-link">{{ $review->product_name }}</span>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </div>

                        <button type="button" class="review-swiper-control review-swiper-next" aria-label="Next reviews">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>

                    <div class="review-swiper-footer">
                        <div class="review-swiper-fraction"></div>
                    </div>

                    <div class="welcome-services-cta">
                        <a href="{{ route('reviews.page') }}" class="welcome-services-btn">View All Reviews</a>
                    </div>
                </section>
            @endif

            <x-site-footer />
        </main>
    </div>

    @if($showSalesPopup)
        <div class="welcome-sales-popup" data-sales-popup data-sales-popup-delay="{{ $popupDelay }}" aria-hidden="true">
            <div
                class="welcome-sales-popup-dialog"
                role="dialog"
                aria-modal="true"
                aria-label="Special offer"
                style="--popup-bg: {{ $popupBgColor }}; --popup-text: {{ $popupTextColor }}; --popup-accent: {{ $popupAccentColor }};"
            >
                <button type="button" class="welcome-sales-popup-close" data-sales-popup-close aria-label="Close offer popup">
                    <i class="fas fa-times"></i>
                </button>

                <div class="welcome-sales-popup-grid">
                    <div class="welcome-sales-popup-media">
                        @if($popupImageSource !== '')
                            <img src="{{ $popupImageSource }}" alt="{{ $popupTitle !== '' ? $popupTitle : 'Special offer' }}" loading="lazy">
                        @else
                            <div class="welcome-sales-popup-media-fallback">
                                {{ $siteName }}<br>Special Sales Offer
                            </div>
                        @endif
                    </div>

                    <div class="welcome-sales-popup-content">
                        @if($popupBadge !== '')
                            <span class="welcome-sales-popup-badge">{{ $popupBadge }}</span>
                        @endif

                        @if($popupTitle !== '')
                            <h2 class="welcome-sales-popup-title">{{ $popupTitle }}</h2>
                        @endif

                        @if($popupMessage !== '')
                            <p class="welcome-sales-popup-message">{{ $popupMessage }}</p>
                        @endif

                        @if($popupPrice !== '')
                            <div class="welcome-sales-popup-price">{{ $popupPrice }}</div>
                        @endif

                        <div class="welcome-sales-popup-actions">
                            <a
                                href="{{ $popupButtonLink !== '' ? $popupButtonLink : '#' }}"
                                class="welcome-sales-popup-btn"
                                @if($popupOpenNewTab) target="_blank" rel="noopener noreferrer" @endif
                            >
                                {{ $popupButtonLabel !== '' ? $popupButtonLabel : 'Book Now' }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($sliderReviews->isNotEmpty())
        <div class="review-image-modal" data-review-image-modal aria-hidden="true">
            <div class="review-image-modal-dialog" role="dialog" aria-modal="true" aria-label="Review image preview">
                <button type="button" class="review-image-modal-close" data-review-image-close aria-label="Close image preview">
                    <i class="fas fa-times"></i>
                </button>
                <img src="" alt="Review preview" class="review-image-modal-media" data-review-image-target>
                <div class="review-image-modal-caption" data-review-image-caption></div>
            </div>
        </div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const navToggle = document.querySelector('[data-welcome-nav-toggle]');
            const navMenu = document.querySelector('[data-welcome-nav]');
            const navIcon = document.querySelector('[data-welcome-nav-icon]');

            if (!navToggle || !navMenu) {
                return;
            }

            const setMenuOpen = function (isOpen) {
                navMenu.classList.toggle('is-open', isOpen);
                navToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');

                if (navIcon) {
                    navIcon.classList.toggle('fa-bars', !isOpen);
                    navIcon.classList.toggle('fa-times', isOpen);
                }
            };

            navToggle.addEventListener('click', function () {
                setMenuOpen(!navMenu.classList.contains('is-open'));
            });

            navMenu.querySelectorAll('a').forEach(function (link) {
                link.addEventListener('click', function () {
                    if (window.innerWidth < 768) {
                        setMenuOpen(false);
                    }
                });
            });

            window.addEventListener('resize', function () {
                if (window.innerWidth >= 768) {
                    setMenuOpen(false);
                }
            });
        });
    </script>

    @if($showCountdown)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const countdownRoot = document.querySelector('[data-sale-countdown]');
                if (!countdownRoot) {
                    return;
                }

                const endValue = countdownRoot.getAttribute('data-countdown-end');
                const endTimestamp = Date.parse(endValue || '');
                if (!Number.isFinite(endTimestamp)) {
                    return;
                }

                const daysNode = countdownRoot.querySelector('[data-countdown-days]');
                const hoursNode = countdownRoot.querySelector('[data-countdown-hours]');
                const minutesNode = countdownRoot.querySelector('[data-countdown-minutes]');
                const secondsNode = countdownRoot.querySelector('[data-countdown-seconds]');

                const setValue = function (node, value) {
                    if (!node) {
                        return;
                    }

                    node.textContent = String(Math.max(0, value)).padStart(2, '0');
                };

                let timerId = null;

                const updateCountdown = function () {
                    const remainingMs = Math.max(0, endTimestamp - Date.now());
                    const totalSeconds = Math.floor(remainingMs / 1000);
                    const days = Math.floor(totalSeconds / 86400);
                    const hours = Math.floor((totalSeconds % 86400) / 3600);
                    const minutes = Math.floor((totalSeconds % 3600) / 60);
                    const seconds = totalSeconds % 60;

                    setValue(daysNode, days);
                    setValue(hoursNode, hours);
                    setValue(minutesNode, minutes);
                    setValue(secondsNode, seconds);

                    if (remainingMs <= 0) {
                        countdownRoot.classList.add('is-ended');
                        if (timerId !== null) {
                            window.clearInterval(timerId);
                        }
                    }
                };

                updateCountdown();
                timerId = window.setInterval(updateCountdown, 1000);
            });
        </script>
    @endif

    @if($showSalesPopup)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const popup = document.querySelector('[data-sales-popup]');
                if (!popup) {
                    return;
                }

                const closeButton = popup.querySelector('[data-sales-popup-close]');
                const delaySeconds = Number(popup.getAttribute('data-sales-popup-delay') || 0);
                const delayMs = Number.isFinite(delaySeconds) ? Math.max(0, delaySeconds * 1000) : 0;

                const openPopup = function () {
                    popup.classList.add('is-open');
                    popup.setAttribute('aria-hidden', 'false');
                    document.body.style.overflow = 'hidden';
                };

                const closePopup = function () {
                    popup.classList.remove('is-open');
                    popup.setAttribute('aria-hidden', 'true');
                    document.body.style.overflow = '';
                };

                closeButton?.addEventListener('click', closePopup);

                popup.addEventListener('click', function (event) {
                    if (event.target === popup) {
                        closePopup();
                    }
                });

                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape' && popup.classList.contains('is-open')) {
                        closePopup();
                    }
                });

                window.addEventListener('load', function () {
                    window.setTimeout(openPopup, delayMs);
                }, { once: true });
            });
        </script>
    @endif

    @if($sliderProducts->isNotEmpty() || $sliderReviews->isNotEmpty())
        <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (typeof Swiper !== 'undefined') {
                    const productSliderRoot = document.querySelector('[data-welcome-swiper]');

                    if (productSliderRoot) {
                        new Swiper(productSliderRoot, {
                            loop: true,
                            speed: 640,
                            spaceBetween: 16,
                            roundLengths: true,
                            centeredSlides: false,
                            autoplay: {
                                delay: 3400,
                                disableOnInteraction: false,
                                pauseOnMouseEnter: true,
                            },
                            slidesPerView: 1,
                            grabCursor: true,
                            navigation: {
                                nextEl: '.welcome-swiper-next',
                                prevEl: '.welcome-swiper-prev',
                            },
                            pagination: {
                                el: '.welcome-swiper-pagination',
                                clickable: true,
                            },
                            breakpoints: {
                                768: {
                                    slidesPerView: 2,
                                    spaceBetween: 18,
                                },
                                1100: {
                                    slidesPerView: 3,
                                    spaceBetween: 20,
                                },
                            },
                        });
                    }

                    const reviewSliderRoot = document.querySelector('[data-review-swiper]');
                    if (reviewSliderRoot) {
                        const reviewCount = Number(reviewSliderRoot.getAttribute('data-review-count') || 0);

                        new Swiper(reviewSliderRoot, {
                            loop: reviewCount > 3,
                            speed: 620,
                            spaceBetween: 16,
                            roundLengths: true,
                            centeredSlides: false,
                            autoplay: {
                                delay: 3600,
                                disableOnInteraction: false,
                                pauseOnMouseEnter: true,
                            },
                            slidesPerView: 1,
                            grabCursor: true,
                            watchOverflow: true,
                            navigation: {
                                nextEl: '.review-swiper-next',
                                prevEl: '.review-swiper-prev',
                            },
                            pagination: {
                                el: '.review-swiper-fraction',
                                type: 'fraction',
                            },
                            breakpoints: {
                                768: {
                                    slidesPerView: 2,
                                    spaceBetween: 18,
                                },
                                1100: {
                                    slidesPerView: 3,
                                    spaceBetween: 20,
                                },
                            },
                        });
                    }
                }

                const reviewModal = document.querySelector('[data-review-image-modal]');
                const reviewModalImage = reviewModal?.querySelector('[data-review-image-target]');
                const reviewModalCaption = reviewModal?.querySelector('[data-review-image-caption]');
                const reviewModalClose = reviewModal?.querySelector('[data-review-image-close]');

                const closeReviewModal = function () {
                    if (!reviewModal || !reviewModalImage) {
                        return;
                    }

                    reviewModal.classList.remove('is-open');
                    reviewModal.setAttribute('aria-hidden', 'true');
                    document.body.style.overflow = '';
                    reviewModalImage.src = '';

                    if (reviewModalCaption) {
                        reviewModalCaption.textContent = '';
                    }
                };

                if (reviewModal && reviewModalImage) {
                    document.addEventListener('click', function (event) {
                        const trigger = event.target.closest('[data-review-image]');
                        if (!trigger) {
                            return;
                        }

                        const src = trigger.getAttribute('data-review-image');
                        if (!src) {
                            return;
                        }

                        const caption = trigger.getAttribute('data-review-caption') || '';

                        reviewModalImage.src = src;
                        reviewModalImage.alt = caption || 'Review preview';
                        if (reviewModalCaption) {
                            reviewModalCaption.textContent = caption;
                        }

                        reviewModal.classList.add('is-open');
                        reviewModal.setAttribute('aria-hidden', 'false');
                        document.body.style.overflow = 'hidden';
                    });

                    reviewModalClose?.addEventListener('click', closeReviewModal);

                    reviewModal.addEventListener('click', function (event) {
                        if (event.target === reviewModal) {
                            closeReviewModal();
                        }
                    });

                    document.addEventListener('keydown', function (event) {
                        if (event.key === 'Escape') {
                            closeReviewModal();
                        }
                    });
                }
            });
        </script>
    @endif
</body>
</html>
