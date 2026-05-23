<?php

if (!function_exists('setting')) {
    /**
     * Get a system setting value
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function setting(string $key, $default = null)
    {
        return \App\Models\SystemSetting::get($key, $default);
    }
}

if (!function_exists('set_setting')) {
    /**
     * Set a system setting value
     *
     * @param string $key
     * @param mixed $value
     * @param string|null $type
     * @return \App\Models\SystemSetting
     */
    function set_setting(string $key, $value, ?string $type = null): \App\Models\SystemSetting
    {
        return \App\Models\SystemSetting::set($key, $value, $type);
    }
}

if (!function_exists('public_settings')) {
    /**
     * Get all public system settings
     *
     * @return array
     */
    function public_settings(): array
    {
        return \App\Models\SystemSetting::getPublicSettings();
    }
}

if (!function_exists('site_name')) {
    /**
     * Get the site name from customization settings
     *
     * @param string $default
     * @return string
     */
    function site_name(string $default = 'W Automation'): string
    {
        static $siteName = null;

        return $siteName ??= \App\Models\CustomizationSetting::getValue('site_name', $default);
    }
}

if (!function_exists('storage_public_url')) {
    /**
     * Build a public storage URL without encoding path slashes.
     */
    function storage_public_url(?string $path, bool $absolute = true): ?string
    {
        $path = trim((string) $path);

        if ($path === '') {
            return null;
        }

        if (preg_match('~^(?:https?:)?//|^data:~i', $path) === 1) {
            return $path;
        }

        $normalizedPath = trim(str_replace('\\', '/', $path), '/');

        if ($normalizedPath === '') {
            return null;
        }

        $encodedPath = collect(explode('/', $normalizedPath))
            ->filter(static fn (string $segment): bool => $segment !== '')
            ->map(static fn (string $segment): string => rawurlencode($segment))
            ->implode('/');

        $relativePath = '/files/' . $encodedPath;

        return $absolute ? url($relativePath) : $relativePath;
    }
}

if (!function_exists('rounded_time_value')) {
    /**
     * Round a time delta or duration to a clean whole number.
     */
    function rounded_time_value($value): int
    {
        return (int) round((float) ($value ?? 0));
    }
}

if (!function_exists('site_logo')) {
    /**
     * Get the site logo URL from customization settings
     *
     * @param string|null $default
     * @return string|null
     */
    function site_logo(?string $default = null): ?string
    {
        return \App\Models\CustomizationSetting::getAssetUrl('site_logo', $default);
    }
}

if (!function_exists('site_favicon')) {
    /**
     * Get the site favicon URL from customization settings
     *
     * @param string|null $default
     * @return string|null
     */
    function site_favicon(?string $default = null): ?string
    {
        return \App\Models\CustomizationSetting::getAssetUrl('site_favicon', $default);
    }
}

if (!function_exists('email_header_logo')) {
    /**
     * Get the email header logo URL.
     *
     * Prefer the active site logo so email branding stays in sync with the
     * public site even when legacy email-specific logo settings are stale.
     *
     * @param string|null $default
     * @return string|null
     */
    function email_header_logo(?string $default = null): ?string
    {
        $siteLogo = site_logo();

        if (filled($siteLogo)) {
            return $siteLogo;
        }

        $uploadedLogo = \App\Models\CustomizationSetting::getAssetUrl('email_header_logo');

        if (filled($uploadedLogo)) {
            return $uploadedLogo;
        }

        $configuredUrl = trim((string) \App\Models\CustomizationSetting::getValue('email_header_logo_url', ''));

        if ($configuredUrl !== '') {
            if (preg_match('~^(?:https?:)?//|^data:~i', $configuredUrl) === 1) {
                return $configuredUrl;
            }

            return url('/' . ltrim($configuredUrl, '/'));
        }

        return $default;
    }
}


if (!function_exists("oauth_settings")) {
    function oauth_settings(): ?\App\Models\OAuthSetting
    {
        return \App\Models\OAuthSetting::getGoogleMeetSettings();
    }
}

if (!function_exists("google_meet_room_link")) {
    function google_meet_room_link(): string
    {
        $oauth = oauth_settings();
        return $oauth && $oauth->meet_room_link
            ? $oauth->meet_room_link
            : env("GOOGLE_MEET_ROOM_LINK", "https://meet.google.com/xnd-ndmy-uyd");
    }
}

if (!function_exists("google_admin_email")) {
    function google_admin_email(): string
    {
        $oauth = oauth_settings();
        return $oauth && $oauth->admin_email
            ? $oauth->admin_email
            : env("GOOGLE_ADMIN_EMAIL", "caliroweteam@caliwfhresumes.com");
    }
}
