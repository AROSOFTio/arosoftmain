<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', 'Blog Admin') | Arosoft</title>
        <meta name="robots" content="noindex,nofollow">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('head')
    </head>
    <body class="admin-shell antialiased">
        <header class="admin-nav">
            <div class="mx-auto flex w-full max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                <a href="{{ route('admin.blog.dashboard') }}" class="font-heading text-xl text-[color:rgba(17,24,39,0.95)]">
                    Blog Admin
                </a>
                <nav class="flex items-center gap-2">
                    <a href="{{ route('admin.blog.dashboard') }}" class="nav-link-sm">Dashboard</a>
                    <a href="{{ route('admin.blog.posts.index') }}" class="nav-link-sm">Posts</a>
                    <a href="{{ route('blog') }}" class="nav-link-sm">View Site</a>
                    <form action="{{ route('admin.logout') }}" method="post">
                        @csrf
                        <button type="submit" class="btn-outline !w-auto !px-3 !py-2 !text-[0.62rem]">Logout</button>
                    </form>
                </nav>
            </div>
        </header>

        <main class="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            @if(session('status'))
                <div class="alert-success mb-6">{{ session('status') }}</div>
            @endif

            @if($errors->any())
                <div class="mb-6 rounded-xl border border-[color:rgba(185,28,28,0.3)] bg-[color:rgba(185,28,28,0.07)] px-4 py-3 text-sm text-[color:rgba(127,29,29,0.95)]">
                    <p class="font-semibold">Please fix the highlighted fields.</p>
                </div>
            @endif

            @yield('content')
        </main>
    </body>
</html>

