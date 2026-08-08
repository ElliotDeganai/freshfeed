<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        @php
            $siteLogo = \App\Models\AppSetting::get('logo_path');
        @endphp
        @if ($siteLogo)
            <link rel="icon" href="{{ Storage::url($siteLogo) }}">
        @else
            <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        @endif

        @php
            $ogSiteName = \App\Models\AppSetting::get('app_name', config('app.name', 'SoRecette'));
            $ogDescription = \App\Models\AppSetting::get(
                'homepage_hero_subtitle',
                "SoRecette est l'endroit où de vraies personnes partagent ce qu'elles cuisinent vraiment — rapide, healthy ou gourmand."
            );
        @endphp

        <meta property="og:type" content="website">
        <meta property="og:site_name" content="{{ $ogSiteName }}">
        <meta property="og:title" content="{{ $ogSiteName }}">
        <meta property="og:description" content="{{ $ogDescription }}">
        <meta property="og:image" content="{{ asset('images/og-banner.jpg') }}">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        <meta property="og:url" content="{{ url()->current() }}">

        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $ogSiteName }}">
        <meta name="twitter:description" content="{{ $ogDescription }}">
        <meta name="twitter:image" content="{{ asset('images/og-banner.jpg') }}">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
