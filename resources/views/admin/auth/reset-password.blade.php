<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Reset Password | Arosoft Admin</title>
        <meta name="robots" content="noindex,nofollow">
        @include('layouts.partials.favicons')
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css'])
    </head>
    <body class="admin-shell antialiased">
        <main class="mx-auto flex min-h-screen w-full max-w-7xl items-center px-4 py-10 sm:px-6 lg:px-8">
            <section class="admin-card mx-auto w-full max-w-md p-7">
                <a href="{{ route('home') }}" class="inline-flex brand-logo-wrap admin-login-logo-wrap">
                    <img src="{{ asset('brand/logo-full.svg') }}" alt="Arosoft Innovations Ltd" class="brand-logo-full admin-login-logo">
                </a>
                <p class="page-kicker">AROSOFT Innovations Ltd.</p>
                <h1 class="mt-2 font-heading text-3xl">Reset password</h1>
                <p class="mt-2 text-sm muted-copy">Set a new password for your admin account.</p>

                @if($errors->any())
                    <div class="mt-5 rounded-xl border border-[color:rgba(185,28,28,0.3)] bg-[color:rgba(185,28,28,0.07)] px-4 py-3 text-sm text-[color:rgba(127,29,29,0.95)]">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="post" action="{{ route('admin.password.update') }}" class="mt-6 space-y-4">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div>
                        <label for="email" class="form-label">Email</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email', $email) }}"
                            required
                            autofocus
                            class="form-field"
                        >
                    </div>

                    <div>
                        <label for="password" class="form-label">New password</label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            class="form-field"
                        >
                    </div>

                    <div>
                        <label for="password_confirmation" class="form-label">Confirm new password</label>
                        <input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            required
                            class="form-field"
                        >
                    </div>

                    <button type="submit" class="btn-solid !w-full !text-[0.68rem]">Reset password</button>
                </form>

                <div class="mt-5">
                    <a href="{{ route('admin.login') }}" class="nav-link-sm">Back to login</a>
                </div>
            </section>
        </main>
    </body>
</html>
