@php
    $policyRaw = \App\Models\CustomizationSetting::getValue(
        'footer_policy_links',
        \App\Models\CustomizationSetting::defaultValue('footer_policy_links', '')
    );

    $socialRaw = \App\Models\CustomizationSetting::getValue(
        'footer_social_links',
        \App\Models\CustomizationSetting::defaultValue('footer_social_links', '')
    );

    $copyrightText = \App\Models\CustomizationSetting::getValue(
        'footer_text',
        \App\Models\CustomizationSetting::defaultValue('footer_text', '')
    );
    $brandingEnabled = \App\Models\CustomizationSetting::getValue(
        'footer_branding_enabled',
        \App\Models\CustomizationSetting::defaultValue('footer_branding_enabled', '1')
    ) === '1';
    $brandingShowLogo = \App\Models\CustomizationSetting::getValue(
        'footer_branding_show_logo',
        \App\Models\CustomizationSetting::defaultValue('footer_branding_show_logo', '1')
    ) === '1';
    $brandingPrefix = trim((string) (\App\Models\CustomizationSetting::getStoredValue('footer_branding_prefix', null) ?? ''));
    $brandingName = trim((string) (\App\Models\CustomizationSetting::getStoredValue('footer_branding_name', null) ?? ''));
    $brandingLink = trim((string) \App\Models\CustomizationSetting::getValue(
        'footer_branding_link',
        \App\Models\CustomizationSetting::defaultValue('footer_branding_link', '')
    ));
    $brandingUploadedLogo = \App\Models\CustomizationSetting::getAssetUrl('footer_branding_logo');
    $brandingLogoUrl = trim((string) \App\Models\CustomizationSetting::getValue(
        'footer_branding_logo_url',
        \App\Models\CustomizationSetting::defaultValue('footer_branding_logo_url', '')
    ));
    $brandingLogo = $brandingUploadedLogo ?: ($brandingLogoUrl !== '' ? storage_public_url($brandingLogoUrl) : null);

    $parseFooterLinks = function (?string $raw, bool $withIcon = false) {
        return collect(preg_split('/\r\n|\r|\n/', (string) $raw))
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->map(function (string $line) use ($withIcon) {
                $parts = array_map('trim', explode('|', $line));

                if (count($parts) < 2 || $parts[0] === '' || $parts[1] === '') {
                    return null;
                }

                $icon = $withIcon ? ($parts[2] ?? 'fas fa-link') : null;
                if ($withIcon && !preg_match('/^[a-z0-9\\-\\s]+$/i', (string) $icon)) {
                    $icon = 'fas fa-link';
                }

                return [
                    'label' => $parts[0],
                    'url' => $parts[1],
                    'icon' => $icon,
                ];
            })
            ->filter()
            ->values();
    };

    $policyLinks = $parseFooterLinks($policyRaw);
    $socialLinks = $parseFooterLinks($socialRaw, true);
    $hasBrandingText = $brandingPrefix !== '' || $brandingName !== '';
    $hasBranding = $brandingEnabled && ($hasBrandingText || ($brandingShowLogo && $brandingLogo));
@endphp

@if($policyLinks->isNotEmpty() || $socialLinks->isNotEmpty() || trim((string) $copyrightText) !== '' || $hasBranding)
    <style>
        .site-footer {
            display: flex !important;
            flex-direction: column !important;
            align-items: stretch !important;
            text-align: center !important;
            margin-top: 1.35rem !important;
        }

        .site-footer-policy-row,
        .site-footer-social-row {
            width: 100% !important;
            justify-content: center !important;
            align-items: center !important;
            text-align: center !important;
        }

        .site-footer-copy-row {
            width: 100% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            gap: 1rem !important;
            text-align: left !important;
        }

        .site-footer > * + * {
            margin-top: 0.7rem !important;
        }

        .site-footer-policy-row {
            display: flex !important;
            gap: 0.85rem 1.45rem !important;
            flex-wrap: wrap !important;
        }

        .site-footer-policy-link {
            margin: 0 0.18rem;
            padding: 0.2rem 0.4rem;
            font-size: 0.82rem !important;
            line-height: 1.25 !important;
        }

        .site-footer-social-row {
            display: flex !important;
            gap: 0.85rem !important;
            flex-wrap: wrap !important;
        }

        .site-footer-social-link i {
            font-size: 1.18rem !important;
        }

        .site-footer-copy {
            text-align: left !important;
        }

        .site-footer-copy-row.site-footer-copy-row-centered {
            justify-content: center !important;
            text-align: center !important;
        }

        .site-footer-copy-row.site-footer-copy-row-centered .site-footer-copy {
            width: 100% !important;
            text-align: center !important;
        }

        .site-footer-branding-wrap {
            display: flex !important;
            justify-content: flex-end !important;
            flex: 1 1 auto !important;
        }

        .site-footer-branding {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: flex-end !important;
            gap: 0.55rem !important;
            color: #111111 !important;
            text-decoration: none !important;
        }

        .site-footer-branding-prefix {
            font-size: 0.72rem !important;
            font-weight: 600 !important;
            color: #78716c !important;
        }

        .site-footer-branding-logo {
            max-height: 1.45rem !important;
            width: auto !important;
            object-fit: contain !important;
        }

        .site-footer-branding-name {
            font-size: 0.82rem !important;
            font-weight: 700 !important;
            color: #111111 !important;
        }

        @media (max-width: 768px) {
            .site-footer-policy-link {
                font-size: 0.74rem !important;
            }

            .site-footer-copy-row {
                flex-direction: column !important;
                justify-content: center !important;
                text-align: center !important;
                margin-bottom: 0px !important;
            }

            .site-footer-copy,
            .site-footer-branding-wrap,
            .site-footer-branding {
                width: 100% !important;
                justify-content: center !important;
                text-align: center !important;
            }
        }
    </style>

    <footer class="site-footer">
        @if($policyLinks->isNotEmpty())
            <div class="site-footer-policy-row">
                @foreach($policyLinks as $item)
                    @php
                        $isExternal = str_starts_with($item['url'], 'http://') || str_starts_with($item['url'], 'https://');
                    @endphp
                    <a
                        href="{{ $item['url'] }}"
                        class="site-footer-policy-link"
                        @if($isExternal) target="_blank" rel="noopener noreferrer" @endif
                    >
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </div>
        @endif

        @if($socialLinks->isNotEmpty())
            <div class="site-footer-social-row mt-5 mb-5    ">
                @foreach($socialLinks as $item)
                    @php
                        $isExternal = str_starts_with($item['url'], 'http://') || str_starts_with($item['url'], 'https://');
                    @endphp
                    <a
                        href="{{ $item['url'] }}"
                        class="site-footer-social-link"
                        aria-label="{{ $item['label'] }}"
                        @if($isExternal) target="_blank" rel="noopener noreferrer" @endif
                    >
                        <i class="{{ $item['icon'] }}"></i>
                    </a>
                @endforeach
            </div>
        @endif

        @if(trim((string) $copyrightText) !== '' || $hasBranding)
            <div class="site-footer-copy-row{{ $hasBranding ? '' : ' site-footer-copy-row-centered' }}">
                <div class="site-footer-copy">{{ $copyrightText }}</div>

                @if($hasBranding)
                    @php
                        $brandingIsExternal = str_starts_with($brandingLink, 'http://') || str_starts_with($brandingLink, 'https://');
                    @endphp
                    <div class="site-footer-branding-wrap">
                        @if($brandingLink !== '')
                            <a
                                href="{{ $brandingLink }}"
                                class="site-footer-branding"
                                aria-label="{{ trim(($brandingPrefix ? $brandingPrefix . ' ' : '') . $brandingName) }}"
                                @if($brandingIsExternal) target="_blank" rel="noopener noreferrer" @endif
                            >
                                @if($brandingPrefix !== '')
                                    <span class="site-footer-branding-prefix">{{ $brandingPrefix }}</span>
                                @endif
                                @if($brandingShowLogo && $brandingLogo)
                                    <img src="{{ $brandingLogo }}" alt="{{ $brandingName !== '' ? $brandingName : 'Footer branding logo' }}" class="site-footer-branding-logo">
                                @endif
                                @if($brandingName !== '')
                                    <span class="site-footer-branding-name">{{ $brandingName }}</span>
                                @endif
                            </a>
                        @else
                            <div class="site-footer-branding" aria-label="{{ trim(($brandingPrefix ? $brandingPrefix . ' ' : '') . $brandingName) }}">
                                @if($brandingPrefix !== '')
                                    <span class="site-footer-branding-prefix">{{ $brandingPrefix }}</span>
                                @endif
                                @if($brandingShowLogo && $brandingLogo)
                                    <img src="{{ $brandingLogo }}" alt="{{ $brandingName !== '' ? $brandingName : 'Footer branding logo' }}" class="site-footer-branding-logo">
                                @endif
                                @if($brandingName !== '')
                                    <span class="site-footer-branding-name">{{ $brandingName }}</span>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        @endif
    </footer>
@endif
