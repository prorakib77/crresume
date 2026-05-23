@php
    $resolvedTitle = trim((string) ($title ?? ''));
    $resolvedSubtitle = trim((string) ($subtitle ?? ''));
    $resolvedLogo = email_header_logo();
    $resolvedSiteName = site_name();
@endphp

<div class="head">
    @if($resolvedLogo)
        <div style="margin:0 0 14px; text-align:center;">
            <img
                src="{{ $resolvedLogo }}"
                alt="{{ $resolvedSiteName }}"
                style="display:block; width:auto; max-width:220px; max-height:68px; height:auto; margin:0 auto;"
            >
        </div>
    @else
        <p class="brand">{{ $resolvedSiteName }}</p>
    @endif

    @if($resolvedTitle !== '')
        <h1 class="title">{{ $resolvedTitle }}</h1>
    @endif

    @if($resolvedSubtitle !== '')
        <p class="sub">{{ $resolvedSubtitle }}</p>
    @endif
</div>
