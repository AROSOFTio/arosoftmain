<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    @php
        $defaultTitle = 'Arosoft Innovations Ltd | IT Services, Printing, Training, Web Development';
        $defaultDescription = 'Arosoft Innovations Ltd provides IT services, printing services, graphics design, website design and development, system development, and IT training/internship in Kampala.';
        $pageTitle = trim($__env->yieldContent('meta_title', $__env->yieldContent('title', $defaultTitle)));
        $pageDescription = trim($__env->yieldContent('meta_description', $defaultDescription));
        $pageKeywords = trim($__env->yieldContent('meta_keywords', 'Arosoft Innovations Ltd, IT services, web development, Kampala'));
        $canonicalUrl = trim($__env->yieldContent('canonical', url()->current()));
        $robots = trim($__env->yieldContent('meta_robots', 'index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1'));
        $ogType = trim($__env->yieldContent('og_type', 'website'));
        $ogTitle = trim($__env->yieldContent('og_title', $pageTitle));
        $ogDescription = trim($__env->yieldContent('og_description', $pageDescription));
        $ogImage = trim($__env->yieldContent('og_image', url('/og-image.jpg')));
        $twitterCard = trim($__env->yieldContent('twitter_card', 'summary_large_image'));
        $twitterTitle = trim($__env->yieldContent('twitter_title', $ogTitle));
        $twitterDescription = trim($__env->yieldContent('twitter_description', $ogDescription));
        $twitterImage = trim($__env->yieldContent('twitter_image', $ogImage));

        $localBusinessSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => 'Arosoft Innovations Ltd',
            'url' => 'https://arosoft.io',
            'email' => 'info@arosoft.io',
            'telephone' => '+256787726388',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => 'Kitintale Road, Opposite St. Johns C.O.U, next to Sir Appolo Kaggwa St. School',
                'addressLocality' => 'Kampala',
                'addressCountry' => 'UG',
            ],
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'contactType' => 'customer support',
                'url' => 'https://arosoft.io/contact',
                'telephone' => '+256787726388',
                'email' => 'info@arosoft.io',
            ],
        ];
    @endphp
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $pageTitle }}</title>
        <meta name="description" content="{{ $pageDescription }}">
        <meta name="keywords" content="{{ $pageKeywords }}">
        <meta name="robots" content="{{ $robots }}">
        <link rel="canonical" href="{{ $canonicalUrl }}">

        <meta property="og:type" content="{{ $ogType }}">
        <meta property="og:title" content="{{ $ogTitle }}">
        <meta property="og:description" content="{{ $ogDescription }}">
        <meta property="og:url" content="{{ $canonicalUrl }}">
        <meta property="og:image" content="{{ $ogImage }}">
        <meta property="og:site_name" content="Arosoft Innovations Ltd">

        <meta name="twitter:card" content="{{ $twitterCard }}">
        <meta name="twitter:title" content="{{ $twitterTitle }}">
        <meta name="twitter:description" content="{{ $twitterDescription }}">
        <meta name="twitter:image" content="{{ $twitterImage }}">
        <meta name="theme-color" content="#009D31">
        @include('layouts.partials.favicons')

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-6208436737131241" crossorigin="anonymous"></script>

        <script>
            window.siteTutorialVideos = @json($tutorialVideos ?? []);
            window.siteTutorialPlaylists = @json($tutorialPlaylists ?? []);
        </script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script type="application/ld+json">
            {!! json_encode($localBusinessSchema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) !!}
        </script>
        @stack('head')
        @yield('schema')
        @yield('faq_schema')
    </head>
    <body class="antialiased">
        <div class="line-texture min-h-screen overflow-x-clip" x-data="siteShell()" x-init="init()" @keydown.escape.window="handleEscape()">
            @include('layouts.partials.topbar')
            @include('layouts.partials.header')
            @include('layouts.partials.offcanvas')

            <main class="mx-auto w-full max-w-7xl px-4 pb-24 pt-12 sm:px-6 lg:px-8">
                @yield('content')
            </main>

            @include('layouts.partials.footer')
        </div>
    </body>
</html>
