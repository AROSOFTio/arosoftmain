<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Forgot Password | Arosoft Admin</title>
        <meta name="robots" content="noindex,nofollow">
        @include('layouts.partials.favicons')
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css'])
    </head>
    <body class="admin-shell antialiased">
        <main class="mx-auto flex min-h-screen w-full max-w-7xl items-center px-4 py-10 sm:px-6 lg:px-8">
            <section class="admin-card mx-auto w-full max-w-md p-7">
                <a href="{{ route('home') }}" class="inline-flex brand-logo-wrap admin-login-logo-wrap">
                    <img src="{{ asset('brand/logo-full.svg') }}" alt="Arosoft Innovations Ltd" class="brand-logo-full admin-login-logo">
                </a>
                <p class="page-kicker">AROSOFT Innovations Ltd.</p>
                <h1 class="mt-2 font-heading text-3xl">Forgot password</h1>
                <p class="mt-2 text-sm muted-copy">Enter your admin email to receive a reset link.</p>

                @if(session('status'))
                    <div class="mt-5 rounded-xl border border-[color:rgba(22,163,74,0.3)] bg-[color:rgba(22,163,74,0.08)] px-4 py-3 text-sm text-[color:rgba(21,128,61,0.95)]">
                        {{ session('status') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="mt-5 rounded-xl border border-[color:rgba(185,28,28,0.3)] bg-[color:rgba(185,28,28,0.07)] px-4 py-3 text-sm text-[color:rgba(127,29,29,0.95)]">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="post" action="{{ route('admin.password.email') }}" class="mt-6 space-y-4">
                    @csrf

                    <div>
                        <label for="email" class="form-label">Email</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            class="form-field"
                        >
                    </div>

                    <button type="submit" class="btn-solid !w-full !text-[0.68rem]">Send reset link</button>
                </form>

                <div class="mt-5">
                    <a href="{{ route('admin.login') }}" class="nav-link-sm">Back to login</a>
                </div>
            </section>
        </main>
    </body>
</html>
