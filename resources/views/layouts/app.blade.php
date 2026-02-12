<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', 'AROSOFT Innovations')</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
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
