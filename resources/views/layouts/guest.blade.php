<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @php
            $siteName = site_name();
            $siteLogo = site_logo();
            $siteFavicon = site_favicon();
        @endphp
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $siteName }}</title>

        @if($siteFavicon)
            <link rel="icon" type="image/x-icon" href="{{ $siteFavicon }}">
            <link rel="shortcut icon" type="image/x-icon" href="{{ $siteFavicon }}">
        @endif

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <x-dynamic-styles />
        <style>
            body.auth-page {
                min-height: 100vh;
                background: #ffffff;
            }

            .auth-page-bg {
                position: fixed;
                inset: 0;
                pointer-events: none;
                background:
                    radial-gradient(35% 35% at 30% 30%, rgba(102, 126, 234, 0.45), transparent 65%),
                    radial-gradient(40% 40% at 70% 40%, rgba(45, 212, 191, 0.42), transparent 60%),
                    radial-gradient(35% 35% at 50% 75%, rgba(92, 84, 255, 0.35), transparent 65%);
            }
        </style>
    </head>
    <body class="auth-page antialiased">
        <div class="auth-page-bg"></div>

        <div class="relative flex min-h-screen items-center justify-center px-4 py-8 sm:px-6 lg:px-8">
            <div class="w-full max-w-md rounded-[2rem] border border-[#d8c6a1] bg-white/95 p-6 shadow-[0_28px_80px_rgba(17,17,17,0.12)] backdrop-blur sm:p-8">
                <div class="mb-6 text-center">
                    <a href="/" class="inline-flex items-center justify-center no-underline">
                        @if($siteLogo)
                            <img src="{{ $siteLogo }}" alt="{{ $siteName }}" class="max-h-16 w-auto">
                        @else
                            <span class="inline-flex h-16 w-16 items-center justify-center rounded-full border border-[#d8c6a1] bg-[#fbf5e8] text-[#b68c3a]">
                                <x-application-logo class="h-9 w-9 fill-current" />
                            </span>
                        @endif
                    </a>
                </div>

                {{ $slot }}
            </div>
        </div>
    </body>
</html>
